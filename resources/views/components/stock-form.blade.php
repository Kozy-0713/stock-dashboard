@props(['stock' => null]) {{-- 新規の時は空、編集の時はデータが入る --}}

<div class="space-y-6 mb-3">
    {{-- 銘柄名 --}}
    <div>
        <label for="name" class="block text-sm font-bold text-gray-700 mb-2">銘柄名</label>
        <input type="text" name="name" id="name" 
               value="{{ old('name', $stock['name'] ?? '') }}"
               class="w-full rounded-xl shadow-sm p-4 border {{ $errors->has('name') ? 'border-red-500 bg-red-50' : 'border-gray-300' }}"
               placeholder="例: Apple">
        @error('name') <p class="mt-2 text-sm text-red-600 font-bold">{{ $message }}</p> @enderror
    </div>

    {{-- 銘柄コード --}}
    <div>
        <label for="code" class="block text-sm font-bold text-gray-700 mb-2">銘柄コード / シンボル</label>
        <input type="text" name="code" id="code" 
               value="{{ old('code', $stock['code'] ?? '') }}"
               class="w-full rounded-xl shadow-sm p-4 border {{ $errors->has('code') ? 'border-red-500 bg-red-50' : 'border-gray-300' }}"
               placeholder="例: AAPL">
        @error('code') <p class="mt-2 text-sm text-red-600 font-bold">{{ $message }}</p> @enderror
    </div>

    <div class="grid grid-cols-2 gap-4">
        {{-- 取得単価 --}}
        <div>
            <label for="buy_price" class="block text-sm font-bold text-gray-700 mb-2">取得単価</label>
            <input type="number" step="0.1" name="buy_price" id="buy_price" 
                   value="{{ old('buy_price', $stock['buy_price'] ?? '') }}"
                   class="w-full rounded-xl shadow-sm p-4 border {{ $errors->has('buy_price') ? 'border-red-500 bg-red-50' : 'border-gray-300' }}">
            @error('buy_price') <p class="mt-2 text-sm text-red-600 font-bold">{{ $message }}</p> @enderror
        </div>

        {{-- 株数 --}}
        <div>
            <label for="quantity" class="block text-sm font-bold text-gray-700 mb-2">保有株数</label>
            <input type="number" name="quantity" id="quantity" 
                   value="{{ old('quantity', $stock['quantity'] ?? '') }}"
                   class="w-full rounded-xl shadow-sm p-4 border {{ $errors->has('quantity') ? 'border-red-500 bg-red-50' : 'border-gray-300' }}">
            @error('quantity') <p class="mt-2 text-sm text-red-600 font-bold">{{ $message }}</p> @enderror
        </div>
    </div>
</div>