<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class StockApiController extends Controller
{
    /**
     * 株価を一括取得するエンドポイント
     * クエリ文字列 ?symbols=AAPL,MSFT で受け取る
     */
    public function getPrices(Request $request)
    {
        $symbols = $request->query('symbols');
        if (!$symbols) {
            return response()->json(['error' => 'No symbols provided'], 400);
        }

        // FMP APIキーを.envから取得
        $apiKey = env('FMP_API_KEY', config('services.fmp.key'));

        if (!$apiKey) {
            return response()->json(['error' => 'API Key not configured'], 500);
        }

        $symbolsArray = array_filter(array_map('trim', explode(',', $symbols)));
        $results = [];

        foreach ($symbolsArray as $symbol) {
            $cacheKey = 'fmp_price_' . $symbol;

            try {
                // 1銘柄ごとに2時間キャッシュ (120分)
                $data = Cache::remember($cacheKey, now()->addHours(2), function () use ($symbol, $apiKey) {
                    $response = Http::get("https://financialmodelingprep.com/stable/quote/", [
                        'symbol' => $symbol,
                        'apikey' => $apiKey,
                    ]);

                    if ($response->successful()) {
                        $json = $response->json();
                        // 1件分の配列が返ってくるはずなので、それを返す
                        if (is_array($json) && count($json) > 0) {
                            return $json[0];
                        }
                    }

                    Log::error("FMP API Error for {$symbol}", [
                        'status' => $response->status(),
                        'body' => $response->body()
                    ]);

                    throw new \Exception("Failed to fetch price for {$symbol}");
                });
                
                if ($data) {
                    $results[] = $data;
                }
            } catch (\Exception $e) {
                Log::error("FMP Connection Error for {$symbol}", ['exception' => $e->getMessage()]);
                // エラーの場合はその銘柄はスキップして次へ
            }
        }

        return response()->json($results);
    }

    /**
     * 為替レート (USD/JPY) を取得するエンドポイント
     */
    public function getExchangeRate()
    {
        $apiKey = env('FMP_API_KEY', config('services.fmp.key'));

        if (!$apiKey) {
            return response()->json(['error' => 'API Key not configured'], 500);
        }

        try {
            // 12時間キャッシュ
            $rate = Cache::remember('fmp_exchange_rate_USDJPY', now()->addHours(12), function () use ($apiKey) {
                $response = Http::get('https://financialmodelingprep.com/stable/quote/', [
                    'symbol' => 'USDJPY',
                    'apikey' => $apiKey,
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    // FMPのレスポンスは配列で返る： [{"symbol":"USDJPY", "price":150.5...}]
                    if (is_array($data) && count($data) > 0 && isset($data[0]['price'])) {
                        return (float) $data[0]['price'];
                    }
                }

                throw new \Exception('Failed to fetch exchange rate');
            });

            return response()->json(['rate' => $rate]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Connection error'], 500);
        }
    }
}
