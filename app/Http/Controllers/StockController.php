<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use Illuminate\Http\Request;
use App\Http\Requests\StoreStockRequest;

class StockController extends Controller
{
    // public function index()
    // {
    //     $stocks = Stock::all();

    //     return view('welcome', compact('stocks'));
    // }
    public function index(Request $request)
    {
        // 1. CookieからJSON文字列を取得
        $cookieData = $request->cookie('my_stocks');
        
        // 2. JSONを連想配列に変換（データがなければ空配列）
        $stocks = $cookieData ? json_decode($cookieData, true) : [];
        
        // 💡 合計損益を計算（配列の 'diff' カラムだけを抽出して合計）
        $totalDiff = array_sum(array_column($stocks, 'diff'));

        // Viewに合計値を渡す
        return view('welcome', compact('stocks', 'totalDiff'));
}

    public function create()
    {
        return view('stocks.create');
    }

    // public function store(Request $request)
    // {
    //     // ここでバリデーション！
    //     $request->validate([
    //         'name' => 'required|max:20', // 必須、最大20文字
    //         'price' => 'required|numeric|min:0', // 必須、数値、0以上
    //         'diff' => 'required|integer', // 必須、整数
    //     ], [
    //         // カスタムメッセージ（日本語化）
    //         'name.required' => '銘柄名は必ず入力してください。',
    //         'name.max' => '銘柄名は20文字以内で入力してください。',
    //         'price.required' => '価格を入力してください。',
    //         'price.numeric' => '価格は数値で入力してください。',
    //     ]);

    //     // バリデーションを通ったときだけ、下の処理が進む
    //     Stock::create($request->all());

    //     return redirect('/')->with('success', '銘柄を登録しました！');
    // }
    public function store(StoreStockRequest $request)
    {
        // 1. 今のデータを取得
        $cookieData = $request->cookie('my_stocks');
        $stocks = $cookieData ? json_decode($cookieData, true) : [];

        // 2. 新しい銘柄を作成（IDを自作する）
        $newStock = [
            'id' => uniqid(), // ブラウザ側で管理するためのユニークなID
            'name' => $request->name,
            'price' => (float)$request->price,
            'diff' => (int)$request->diff,
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
}