@extends('layouts.app')

@section('content')
<div class="container" style="padding: 20px;">
    <h1>My Dashboard</h1>

    <div class="dashboard-grid" style="display: flex; gap: 20px; flex-wrap: wrap;">
        @foreach($stocks as $stock)
            <div class="card" style="border: 1px solid #ddd; padding: 20px; border-radius: 12px; min-width: 280px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); background: white;">
                <h3 style="margin-top: 0;">{{ $stock['name'] }}</h3>
                
                <div class="price" style="font-size: 1.2em; margin-bottom: 10px;">
                    現在値: <strong>¥{{ number_format($stock['price'], 1) }}</strong>
                </div>

                {{-- 損益がプラスなら赤、マイナスなら緑のクラスを当てる --}}
                <div class="diff" style="padding: 5px 10px; border-radius: 4px; display: inline-block; font-weight: bold; {{ $stock['diff'] > 0 ? 'background: #ffebee; color: #d32f2f;' : 'background: #e8f5e9; color: #2e7d32;' }}">
                    評価損益: {{ $stock['diff'] > 0 ? '+' : '' }}{{ number_format($stock['diff']) }}円
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection