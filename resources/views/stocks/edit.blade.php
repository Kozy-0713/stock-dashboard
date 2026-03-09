@extends('layouts.app')

@section('content')
<div class="container" style="padding: 20px; max-width: 600px; margin: 0 auto;">
    <h2>銘柄編集</h2>

    <form action="{{ route('stocks.update', $stock['id']) }}" method="POST" style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        @csrf {{-- ← これ超重要！Laravelのセキュリティ対策です --}}
        @method('PUT')        
        <x-stock-form :stock="$stock" />

        <button type="submit" style="background: #38c172; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; width: 100%;">更新する</button>
        <a href="/" style="display: block; text-align: center; margin-top: 15px; color: #666;">戻る</a>
    </form>
</div>
@endsection