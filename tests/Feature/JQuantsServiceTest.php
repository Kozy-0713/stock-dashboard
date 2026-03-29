<?php
namespace Tests\Feature;

use App\Services\JQuantsService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test; // Attributeを使用

class JQuantsServiceTest extends TestCase
{
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new JQuantsService();
        Cache::flush();
    }

    #[Test] // 警告が出ないモダンな書き方
    public function IDトークンが正しく取得できキャッシュされること()
    {
        // 1. HTTPレスポンスをモック
        Http::fake([
            'api.jquants.com/v1/token/auth_refresh' => Http::response([
                'idToken' => 'fake_id_token_123'
            ], 200),
        ]);

        // 2. サービスを実行（インスタンスメソッドとして呼び出し）
        $token = $this->service->getIdToken();

        // 3. 検証
        $this->assertEquals('fake_id_token_123', $token);
        $this->assertTrue(Cache::has('jquants_id_token'));

        // 4. 2回目の呼び出しでキャッシュが使われ、Httpリクエストが増えないこと
        $this->service->getIdToken();
        Http::assertSentCount(1); 
    }

    #[Test]
    public function APIから取得したデータを期待する形式に整形できること()
    {
        Http::fake([
            'api.jquants.com/v1/token/auth_refresh' => Http::response(['idToken' => 'fake_token'], 200),
            'api.jquants.com/v1/daily/quotes*' => Http::response([
                'daily_quotes' => [
                    [
                        'Code' => '72030', 
                        'Close' => 2500.5, 
                        'Volume' => 1000.0 // J-Quantsは数値もfloatで返ることが多いです
                    ],
                    [
                        'Code' => '99840', 
                        'Close' => 8500.0, 
                        'Volume' => 500.0
                    ],
                ]
            ], 200),
        ]);

        $result = $this->service->fetchDailyQuotes('20260323');

        $this->assertEquals('20260323', $result['data_date']);
        $this->assertArrayHasKey('7203', $result['stocks']);
        $this->assertEquals(2500.5, $result['stocks']['7203']['price']);
        $this->assertCount(2, $result['stocks']);
    }

    #[Test]
    public function 整形済みデータをJSONファイルとして保存できること()
    {
        // Storageを擬似化
        Storage::fake('local');

        $sampleData = [
            'data_date' => '20260323',
            'updated_at' => '2026-03-24 15:00:00',
            'stocks' => [
                '7203' => ['price' => 2500.5, 'volume' => 1000]
            ]
        ];

        // 実行
        $this->service->saveQuotes($sampleData);

        // 検証：ファイルが存在するか
        Storage::disk('local')->assertExists('quotes.json');

        // 検証：保存された内容が正しいか
        $savedData = json_decode(Storage::disk('local')->get('quotes.json'), true);
        $this->assertEquals('20260323', $savedData['data_date']);
        $this->assertEquals(2500.5, $savedData['stocks']['7203']['price']);
    }
}