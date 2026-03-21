<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class StockPriceService
{
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.alphavantage.key');
    }

    // public function getLatestPrice(string $symbol)
    // {
    //     $response = Http::get("https://www.alphavantage.co/query", [
    //         'function' => 'GLOBAL_QUOTE',
    //         'symbol' => $symbol,
    //         'apikey' => $this->apiKey,
    //     ]);

    //     if ($response->successful()) {
    //         $data = $response->json();

    //         // API制限にかかった場合
    //         if (isset($data['Note'])) {
    //             Log::warning("Alpha Vantage API Limit reached: " . $data['Note']);
    //             return null;
    //         }

    //         // 価格情報の抽出（キーに "05. price" という特殊な名前が使われているため）
    //         return $data['Global Quote']['05. price'] ?? null;
    //     }

    //     return null;
    // }
    public function getLatestPrice(string $symbol)
    {
        // LaravelのLogクラスを通さず、PHPの標準エラー出力に直接叩き込む
        error_log("--- DEBUG START: fetching {$symbol} ---");
        $response = Http::get("https://www.alphavantage.co/query", [
            'function' => 'GLOBAL_QUOTE',
            'symbol' => $symbol,
            'apikey' => $this->apiKey,
        ]);
        dd($response);
        error_log("--- DEBUG RESPONSE: " . $response->status() . " ---");
        if ($response->successful()) {
            $data = $response->json();

            // 1. API制限の警告が出ているか確認
            if (isset($data['Note'])) {
                Log::warning("Alpha Vantage API Limit reached for {$symbol}: " . $data['Note']);
                return null;
            }

            // 2. そもそもデータが空（銘柄コード間違いなど）ではないか確認
            if (!isset($data['Global Quote']) || empty($data['Global Quote'])) {
                Log::error("Alpha Vantage returned empty data for {$symbol}: " . json_encode($data));
                return null;
            }

            $price = $data['Global Quote']['05. price'] ?? null;
            Log::info("Alpha Vantage Success: {$symbol} = {$price}");
            return $price;
        }

        // 2. 通信失敗（404, 500, タイムアウト等）の場合
        Log::error("Alpha Vantage Connection FAILED for {$symbol}. Status: " . $response->status() . " Body: " . $response->body());
        return null;
    }
}