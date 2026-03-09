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

    public function getLatestPrice(string $symbol)
    {
        $response = Http::get("https://www.alphavantage.co/query", [
            'function' => 'GLOBAL_QUOTE',
            'symbol' => $symbol,
            'apikey' => $this->apiKey,
        ]);

        if ($response->successful()) {
            $data = $response->json();

            // API制限にかかった場合
            if (isset($data['Note'])) {
                Log::warning("Alpha Vantage API Limit reached: " . $data['Note']);
                return null;
            }

            // 価格情報の抽出（キーに "05. price" という特殊な名前が使われているため）
            return $data['Global Quote']['05. price'] ?? null;
        }

        return null;
    }
}