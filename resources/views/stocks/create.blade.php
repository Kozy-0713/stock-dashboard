@extends('layouts.app')

@section('content')
<div class="container" style="padding: 20px; max-width: 600px; margin: 0 auto;">
    <h2>新規銘柄登録</h2>

    <form action="/stocks" method="POST" style="background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        @csrf {{-- ← これ超重要！Laravelのセキュリティ対策です --}}
        
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px;">銘柄名</label>
            <input type="text" name="name" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;" required>
        </div>

        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px;">現在値 (¥)</label>
            <input type="number" step="0.1" name="price" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;" required>
        </div>

        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 5px;">評価損益 (円)</label>
            <input type="number" name="diff" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;" required>
        </div>

        <button type="submit" style="background: #38c172; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; width: 100%;">保存する</button>
        <a href="/" style="display: block; text-align: center; margin-top: 15px; color: #666;">戻る</a>
    </form>
</div>
@endsection