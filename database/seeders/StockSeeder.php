<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Stock; // これを忘れずに追加

class StockSeeder extends Seeder
{
    public function run(): void
    {
        // 以前Controllerに書いていたデータを、今度はデータベースに保存します
        Stock::create(['name' => 'トヨタ', 'price' => 53500.5, 'diff' => 30050]);
        Stock::create(['name' => 'ソニーG', 'price' => 512500.0, 'diff' => -25000]);
        Stock::create(['name' => 'ソフトバンクG', 'price' => 58200.0, 'diff' => -30000]);
        Stock::create(['name' => '任天堂', 'price' => 7800.0, 'diff' => 1200]);
    }
}