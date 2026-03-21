<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use Illuminate\Http\Request;
use App\Http\Requests\StoreStockRequest;
use App\Services\StockPriceService;

class StockController extends Controller
{
    public function index(Request $request)
    {
        // 1. CookieからJSON文字列を取得
        $cookieData = $request->cookie('my_stocks');
        
        // 2. JSONを連想配列に変換（データがなければ空配列）
        $stocks = $cookieData ? json_decode($cookieData, true) : [];
        
        // 💡 合計損益を計算（配列の 'diff' カラムだけを抽出して合計）
        $totalDiff = array_sum(array_column($stocks, 'diff'));

        // 💡 合計評価額（現在値の合計）を追加
        $totalPrice = array_sum(array_column($stocks, 'price'));

        // Viewに合計値を渡す
        return view('welcome', compact('stocks', 'totalDiff', 'totalPrice'));
    }

    public function create()
    {
        return view('stocks.create');
    }

    public function store(StoreStockRequest $request)
    {
        // 1. 今のデータを取得
        $cookieData = $request->cookie('my_stocks');
        $stocks = $cookieData ? json_decode($cookieData, true) : [];

        // 2. 新しい銘柄を作成（IDを自作する）
        $newStock = [
            'id' => uniqid(),
            'name' => $request->name,
            'code' => strtoupper($request->code),
            'buy_price' => (float)$request->buy_price,
            'quantity' => (float)$request->quantity,
            'price' => (float)$request->buy_price, // 初回は取得時と同じ価格
            'diff' => 0, // 損益はまだ 0
        ];

        // 3. 配列に追加
        $stocks[] = $newStock;

        // 4. Cookieを焼いてトップへ戻る（有効期限はとりあえず1年：525600分）
        return redirect('/')->cookie('my_stocks', json_encode($stocks), 525600);
    }

    public function destroy(Request $request, $id) // 引数を $stock から $id に変更
    {
        // 1. 今のデータを取得
        $cookieData = $request->cookie('my_stocks');
        $stocks = $cookieData ? json_decode($cookieData, true) : [];

        // 2. 指定されたID以外のものを残す
        $filteredStocks = array_filter($stocks, function($stock) use ($id) {
            return $stock['id'] !== $id;
        });

        // 3. インデックスを振り直して（念のため）Cookieを更新
        return redirect('/')->cookie('my_stocks', json_encode(array_values($filteredStocks)), 525600);
    }

    // 編集画面を表示
    public function edit(Request $request, $id)
    {
        $cookieData = $request->cookie('my_stocks');
        $stocks = $cookieData ? json_decode($cookieData, true) : [];

        // IDが一致するデータを探す
        $stock = collect($stocks)->firstWhere('id', $id);

        if (!$stock) {
            return redirect('/')->with('error', '銘柄が見つかりませんでした。');
        }

        return view('stocks.edit', compact('stock'));
    }

    // データを更新
    public function update(StoreStockRequest $request, $id) // バリデーションは登録時と同じものを使える！
    {
        $cookieData = $request->cookie('my_stocks');
        $stocks = $cookieData ? json_decode($cookieData, true) : [];

        // 対象のデータを書き換える
        foreach ($stocks as &$stock) {
            if ($stock['id'] === $id) {
                $stock['name'] = $request->name;
                $stock['code'] = strtoupper($request->code);
                $stock['buy_price'] = (float)$request->buy_price;
                $stock['quantity'] = (float)$request->quantity;
                
                // 💡 現在の価格(price)と、新しい取得単価・株数で損益を再計算
                $stock['diff'] = ($stock['price'] - $stock['buy_price']) * $stock['quantity'];
            }
        }

        return redirect('/')->cookie('my_stocks', json_encode($stocks), 525600);
    }

    public function refreshPrices(StockPriceService $service, Request $request)
    {
        // 💡 前回の更新から60秒経過しているかチェック
        $lastRefresh = session('last_stock_refresh');
        if ($lastRefresh && now()->timestamp - $lastRefresh < 60) {
            return redirect('/')->with('status', '更新は1分間に1回までです。少し待ってから再度お試しください。');
        }

        $cookieData = $request->cookie('my_stocks');
        $stocks = $cookieData ? json_decode($cookieData, true) : [];

        if (empty($stocks)) return redirect('/');

        $updatedCount = 0;
        foreach ($stocks as &$stock) {
            $newPrice = $service->getLatestPrice($stock['code']);
            
            if ($newPrice !== null) {
                $stock['price'] = (float)$newPrice;
                $stock['diff'] = ($stock['price'] - ($stock['buy_price'] ?? 0)) * ($stock['quantity'] ?? 0);
                $updatedCount++;
            }
            
            // 無料枠対策：1秒待機（銘柄数が多い場合はこれでも足りない可能性がある）
            usleep(500000); 
        }

        // 💡 更新成功時に現在のタイムスタンプをセッションに保存
        session(['last_stock_refresh' => now()->timestamp]);

        return redirect('/')
            ->with('status', "{$updatedCount} 件の銘柄を更新しました。")
            ->cookie('my_stocks', json_encode($stocks), 525600);
    }
}