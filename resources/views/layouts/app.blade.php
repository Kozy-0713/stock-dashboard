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
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>Stock Dashboard</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    @include('partials.navigation')
    <main>
        @yield('content')
    </main>
</body>
</html>