<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Dashboard</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body style="background-color: #f4f7f6; font-family: sans-serif; margin: 0;">
    
    {{-- ナビゲーションなどをここに置くことも可能 --}}
    <header style="background: #333; color: white; padding: 1rem;">
        <div class="container">Stock Manager</div>
    </header>

    <main>
        @yield('content')
    </main>

</body>
</html>