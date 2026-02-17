<?php
$my_stocks = [
    // 銘柄名, 現在値, 前日比, 保有数, 取得単価
    ['name' => 'トヨタ', 'price' => 3500.5, 'change' => 45.2, 'count' => 100, 'buy_price' => 3200.0 , 'code' => '7203'],
    ['name' => 'ソニーG', 'price' => 12500.0, 'change' => 200.0, 'count' => 50, 'buy_price' => 13000.0, 'code' => '6758'],
    ['name' => 'ソフトバンクG', 'price' => 8200.0, 'change' => -110.5, 'count' => 100, 'buy_price' => 8500.0, 'code' => '9984'],
];

// 本来はAPI等から取得しますが、まずはダミーデータで！
// $my_stocks = [
//     ['code' => '7203', 'name' => 'トヨタ', 'price' => 3500.5, 'change' => 45.2],
//     ['code' => '9984', 'name' => 'ソフトバンクG', 'price' => 8200.0, 'change' => -110.5],
//     ['code' => '6758', 'name' => 'ソニーG', 'price' => 12500.0, 'change' => 200.0],
// ]; -->
?>
@extends('layouts.app')
@section('content')
    <div class="container">
        <h1>My Dashboard</h1>
        <div class="grid">
    <?php
        // 1. 合計計算用の変数を初期化
        $total_investment = 0;
        $total_current_value = 0;

        // 2. ループを回す前に計算だけしてしまう（後で表示するため）
        foreach ($my_stocks as $stock) {
            $total_investment += $stock['buy_price'] * $stock['count'];
            $total_current_value += $stock['price'] * $stock['count'];
        }
        $total_profit = $total_current_value - $total_investment;
        $total_profit_rate = ($total_profit / $total_investment) * 100;
    ?>
    <div class="summary-header">
    <div class="summary-item">
        <span class="label">総評価損益</span>
        <span class="value <?php echo $total_profit >= 0 ? 'plus' : 'minus'; ?>">
            <?php echo ($total_profit > 0 ? '+' : '') . number_format($total_profit); ?>円
            (<?php echo number_format($total_profit_rate, 2); ?>%)
        </span>
    </div>
</div>
    <?php foreach ($my_stocks as $stock): 
        // 損益計算
        $profit = ($stock['price'] - $stock['buy_price']) * $stock['count'];
        $profit_class = $profit >= 0 ? 'plus' : 'minus';
    ?>
        <div class="card <?php echo $profit_class; ?>">
            <div class="name"><a href="https://finance.yahoo.co.jp/quote/<?php echo $stock['code']; ?>.T" target="_blank">
    <?php echo $stock['name']; ?>
</a></div>
            <div class="price">現在値: ¥{{ number_format($stock['price'], 1) }}</div>
            <div class="profit">
                評価損益: <?php echo ($profit > 0 ? '+' : '') . number_format($profit); ?>円
            </div>
        </div>
    <?php endforeach; ?>
</div>
    </div>
@endsection