<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
        // FMP_API_KEY=xxx を設定してください
        $apiKey = env('FMP_API_KEY', config('services.fmp.key'));

        if (!$apiKey) {
            return response()->json(['error' => 'API Key not configured'], 500);
        }

        try {
            // FMP APIを呼び出し（複数銘柄一括）
            $response = Http::get("https://financialmodelingprep.com/stable/quote/", [
                'symbol' => $symbols,
                'apikey' => $apiKey,
            ]);

            if ($response->successful()) {
                return response()->json($response->json());
            }

            Log::error('FMP API Error', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return response()->json(['error' => 'Failed to fetch prices from API'], 502);

        } catch (\Exception $e) {
            Log::error('FMP Connection Error', ['exception' => $e->getMessage()]);
            return response()->json(['error' => 'Connection error'], 500);
        }
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
            $response = Http::get('https://financialmodelingprep.com/stable/quote/', [
                'symbol' => 'USDJPY',
                'apikey' => $apiKey,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                // FMPのレスポンスは配列で返る： [{"symbol":"USDJPY", "price":150.5...}]
                if (is_array($data) && count($data) > 0 && isset($data[0]['price'])) {
                    return response()->json(['rate' => (float) $data[0]['price']]);
                }
            }

            return response()->json(['error' => 'Failed to fetch exchange rate'], 502);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Connection error'], 500);
        }
    }
}
