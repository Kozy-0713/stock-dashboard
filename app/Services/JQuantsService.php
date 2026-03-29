<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class JQuantsService
{
    private $baseUrl = 'https://api.jquants.com/v1';

    /**
     * IDトークンを取得（キャッシュ利用）
     */
    public function getIdToken()
    {
        // 24時間の実行用トークンをキャッシュ（余裕を見て23時間で設定）
        return Cache::remember('jquants_id_token', now()->addHours(23), function () {
            $response = Http::post("{$this->baseUrl}/token/auth_refresh", [
                'refreshtoken' => config('services.jquants.api_key')
            ]);

            if ($response->failed()) {
                \Log::error('J-Quants認証失敗: ' . $response->body());
                throw new \Exception('IDトークンの取得に失敗しました。');
            }

            return $response->json()['idToken'];
        });
    }

    /**
     * 全銘柄の終値一覧を取得
     */
    public function getDailyQuotes($date)
    {
        $idToken = $this->getIdToken();

        $response = Http::withToken($idToken)
            ->get("{$this->baseUrl}/daily/quotes", [
                'date' => $date, // YYYYMMDD 形式
            ]);

        return $response->json();
    }

    /**
     * 指定日の株価一覧を取得して整形
     * @param string $date YYYYMMDD
     */
    public function fetchDailyQuotes(string $date): array
    {
        $idToken = $this->getIdToken();

        $response = Http::withToken($idToken)
            ->get("{$this->baseUrl}/daily/quotes", [
                'date' => $date,
            ]);

        if ($response->failed()) {
            throw new \Exception("J-Quants APIエラー: {$response->status()}");
        }

        $data = $response->json();
        $quotes = $data['daily_quotes'] ?? [];

        // 整形処理へ
        return $this->formatQuotes($quotes, $date);
    }

    /**
     * APIレスポンスをコード主体の連想配列に整形
     */
    private function formatQuotes(array $quotes, string $date): array
    {
        $stocks = [];

        foreach ($quotes as $quote) {
            // 銘柄コード（J-Quantsは5桁で返る場合があるため、上4桁を抽出）
            $code = substr($quote['Code'], 0, 4);
            
            $stocks[$code] = [
                'price' => $quote['Close'],      // 終値
                'volume' => $quote['Volume'],    // 出来高
                // 'change' => $quote['UpperLimit'] // 必要に応じて項目追加
            ];
        }

        return [
            'data_date' => $date,
            'updated_at' => now()->toDateTimeString(),
            'stocks' => $stocks
        ];
    }

    /**
     * 整形済みデータをJSONファイルとして保存する
     */
    public function saveQuotes(array $data): bool
    {
        // JSON_PRETTY_PRINT: GitHubでの差分を見やすくするため
        // JSON_UNESCAPED_UNICODE: 日本語（銘柄名など）が含まれても読めるようにするため
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return Storage::disk('local')->put('quotes.json', $json);
    }
}