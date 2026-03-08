@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">
    <div class="max-w-4xl mx-auto py-10 px-4">
    
        {{-- 💡 合計損益の表示エリア --}}
        <!-- <div class="mb-8 p-6 rounded-2xl shadow-sm border {{ $totalDiff >= 0 ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200' }}">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium {{ $totalDiff >= 0 ? 'text-green-700' : 'text-red-700' }}">トータル評価損益</p>
                    <h1 class="text-4xl font-black {{ $totalDiff >= 0 ? 'text-green-800' : 'text-red-800' }}">
                        {{ $totalDiff >= 0 ? '+' : '' }}{{ number_format($totalDiff) }} <span class="text-xl">円</span>
                    </h1>
                </div>
                <div class="text-5xl">
                    {{ $totalDiff >= 0 ? '📈' : '📉' }}
                </div>
            </div>
        </div> -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            {{-- 合計損益カード (1/3幅) --}}
            <div class="md:col-span-1 p-6 rounded-2xl shadow-sm border {{ $totalDiff >= 0 ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200' }}">
                <p class="text-sm font-medium {{ $totalDiff >= 0 ? 'text-green-700' : 'text-red-700' }}">トータル評価損益</p>
                <h1 class="text-3xl font-black {{ $totalDiff >= 0 ? 'text-green-800' : 'text-red-800' }} mt-2">
                    {{ $totalDiff >= 0 ? '+' : '' }}{{ number_format($totalDiff) }} 円
                </h1>
                <div class="text-4xl mt-4">
                    {{ $totalDiff >= 0 ? '📈' : '📉' }}
                </div>
            </div>

            {{-- 資産構成グラフ (2/3幅) --}}
            {{-- 資産構成グラフ (2/3幅) --}}
            <div class="md:col-span-2 p-8 rounded-2xl shadow-sm border border-gray-200 bg-white">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="text-lg font-bold text-gray-800">資産構成比率</h3>
                    <p class="text-xs text-gray-500">現在値に基づく比率</p>
                </div>
                
                <div class="flex flex-col sm:flex-row items-center gap-8">
                    {{-- グラフ本体 (左側) --}}
                    <div class="relative w-full sm:w-2/5 h-48 flex items-center justify-center">
                        <canvas id="portfolioChart"></canvas>
                        <div class="absolute text-center">
                            <p class="text-[10px] uppercase tracking-widest text-gray-400 font-bold">Total</p>
                            <p class="text-xl font-black text-gray-900 leading-none">¥{{ number_format($totalPrice) }}</p>
                        </div>
                    </div>
                    
                    {{-- 💡 自作の凡例エリア (右側：縦並び + スクロール) --}}
                    <div class="w-full sm:w-3/5">
                        <div id="chartLegend" class="flex flex-col gap-1 max-h-48 overflow-y-auto pr-2 custom-scrollbar">
                            {{-- ここにJSで凡例が縦に挿入されます --}}
                        </div>
                    </div>
                </div>
            </div>

            <style>
                /* スクロールバーをシュッとさせる */
                .custom-scrollbar::-webkit-scrollbar { width: 4px; }
                .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
                .custom-scrollbar::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 10px; }
                .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #9ca3af; }
            </style>
        </div>
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800">My Dashboard</h1>
            <a href="/stocks/create" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg shadow-md transition duration-300">
                + 新規銘柄を登録
            </a>
        </div>

        {{-- 保存完了メッセージ --}}
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        @foreach($stocks as $stock)
            <div class="relative group bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition">
                {{-- 銘柄カード内の右上のボタンエリア --}}
                <div class="absolute top-4 right-4 flex items-center gap-2">
                    <a href="{{ route('stocks.edit', $stock['id']) }}" class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-full transition-all" title="編集">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </a>

                    <form action="{{ route('stocks.destroy', $stock['id']) }}" method="POST" onsubmit="return confirm('本当に削除しますか？');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-full transition-all" title="削除">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </form>
                </div>
                {{-- 銘柄名 --}}
                <h3 class="text-xl font-bold text-gray-700 mb-2 pr-8">{{ $stock['name'] }}</h3>
                
                {{-- 価格情報 --}}
                <p class="text-gray-500 mb-4">
                    現在値: <span class="text-lg font-semibold text-gray-900">¥{{ number_format($stock['price'], 1) }}</span>
                </p>
                
                {{-- 損益バッジ --}}
                <div class="inline-block px-3 py-1 rounded-full text-sm font-bold {{ $stock['diff'] > 0 ? 'bg-red-50 text-red-600' : 'bg-green-50 text-green-600' }}">
                    評価損益: {{ $stock['diff'] > 0 ? '+' : '' }}{{ number_format($stock['diff']) }}円
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
<script>
document.addEventListener('DOMContentLoaded', function () {
    const stocks = @json($stocks);
    
    if (stocks.length === 0) return;

    const ctx = document.getElementById('portfolioChart').getContext('2d');
    
    // 🎨 モダンな配色パレット (Tailwind v4のカラーを参考)
    const chartColors = [
        '#60a5fa', // blue-400
        '#4ade80', // green-400
        '#fb923c', // orange-400
        '#f87171', // red-400
        '#a78bfa', // violet-400
        '#f472b6', // pink-400
        '#22d3ee'  // cyan-400
    ];

    // グラフデータの準備
    const labels = stocks.map(s => s.name);
    const data = stocks.map(s => s.price); 
    
    // 凡例エリアを取得
    const legendContainer = document.getElementById('chartLegend');
    
    // 凡例のHTMLを生成
    // 凡例のHTMLを生成
    legendContainer.innerHTML = ''; // 初期化
    stocks.forEach((stock, index) => {
        const color = chartColors[index % chartColors.length];
        const percentage = ((stock.price / @json($totalPrice)) * 100).toFixed(1);
        
        legendContainer.innerHTML += `
            <div class="flex items-center justify-between p-2 rounded-xl hover:bg-gray-50 transition-all group">
                <div class="flex items-center gap-3 min-w-0">
                    <span class="w-2 h-6 rounded-full flex-shrink-0" style="background-color: ${color};"></span>
                    <div class="flex flex-col min-w-0">
                        <span class="font-bold text-gray-800 truncate text-sm uppercase">${stock.name}</span>
                        <span class="text-[10px] text-gray-400 font-medium">${percentage}%</span>
                    </div>
                </div>
                <div class="text-right flex-shrink-0">
                    <span class="font-mono font-bold text-gray-600 text-sm">¥${stock.price.toLocaleString()}</span>
                </div>
            </div>
        `;
    });

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: chartColors,
                borderWidth: 2, // 💡 少し枠線を入れてメリハリを
                borderColor: '#ffffff', // 枠線を白に
                hoverOffset: 10 // 💡 ホバー時に少し飛び出す演出
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false // 標準の凡例はオフ
                },
                // 💡 ツールチップのカスタマイズ（ホバー時の表示）
                tooltip: {
                    backgroundColor: 'rgba(17, 24, 39, 0.9)', // gray-900
                    titleFont: { size: 14, weight: 'bold' },
                    bodyFont: { size: 13 },
                    padding: 12,
                    cornerRadius: 8,
                    displayColors: true,
                    boxWidth: 8,
                    boxHeight: 8,
                    boxPadding: 4,
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            if (label) { label += ': '; }
                            if (context.parsed !== null) {
                                label += '¥' + context.parsed.toLocaleString();
                            }
                            return label;
                        }
                    }
                }
            },
            cutout: '80%', // 真ん中の穴を大きくして、より洗練された印象に
            animation: {
                animateScale: true, // 💡 拡大アニメーション
                animateRotate: true // 💡 回転アニメーション
            }
        }
    });
});
</script>