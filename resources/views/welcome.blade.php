@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">
    <div class="max-w-4xl mx-auto py-10 px-4">
    
        {{-- 💡 合計損益の表示エリア --}}
        <div class="mb-8 p-6 rounded-2xl shadow-sm border {{ $totalDiff >= 0 ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200' }}">
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
                
                {{-- 1. 削除ボタン：右上に浮かせる --}}
                <form action="{{ route('stocks.destroy', $stock['id']) }}" method="POST" 
                    onsubmit="return confirm('本当に「{{ $stock['name'] }}」を削除しますか？');"
                    class="absolute top-3 right-3 opacity-0 group-hover:opacity-100 transition-opacity">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-full transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </form>

                {{-- 2. 銘柄名 --}}
                <h3 class="text-xl font-bold text-gray-700 mb-2 pr-8">{{ $stock['name'] }}</h3>
                
                {{-- 3. 価格情報 --}}
                <p class="text-gray-500 mb-4">
                    現在値: <span class="text-lg font-semibold text-gray-900">¥{{ number_format($stock['price'], 1) }}</span>
                </p>
                
                {{-- 4. 損益バッジ --}}
                <div class="inline-block px-3 py-1 rounded-full text-sm font-bold {{ $stock['diff'] > 0 ? 'bg-red-50 text-red-600' : 'bg-green-50 text-green-600' }}">
                    評価損益: {{ $stock['diff'] > 0 ? '+' : '' }}{{ number_format($stock['diff']) }}円
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection