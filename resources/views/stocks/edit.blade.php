@extends('layouts.app')

@section('content')
<div class="container" style="padding: 20px; max-width: 600px; margin: 0 auto;">
    <h2>銘柄編集</h2>

    <form action="{{ route('stocks.update', $stock['id']) }}" method="POST" style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        @csrf {{-- ← これ超重要！Laravelのセキュリティ対策です --}}
        @method('PUT')        
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">銘柄名</label>
            <input type="text" name="name" id="name" value="{{ old('name', $stock['name'] ) }}"
                class="w-full rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 p-3 border {{ $errors->has('name') ? 'border-red-500 bg-red-50' : 'border-gray-300' }}"
                placeholder="例: トヨタ">
            
            {{-- エラーメッセージの表示 --}}
            @error('name')
                <p class="mt-1 text-sm text-red-600 font-bold">{{ $message }}</p>
            @enderror
        </div>

        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px;">現在値 (¥)</label>
            <input type="number" step="0.1" name="price" value="{{ old('price', $stock['price'] )}}" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;" required>
            @error('price')
                <p class="mt-1 text-sm text-red-600 font-bold">{{ $message }}</p>
            @enderror
        </div>

        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 5px;">評価損益 (円)</label>
            <input type="number" name="diff" value="{{ old('diff', $stock['diff'] )}}" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;" required>
        </div>

        <button type="submit" style="background: #38c172; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; width: 100%;">更新する</button>
        <a href="/" style="display: block; text-align: center; margin-top: 15px; color: #666;">戻る</a>
    </form>
</div>
@endsection