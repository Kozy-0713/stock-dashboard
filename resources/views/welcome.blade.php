@extends('layouts.app')

@section('content')
<div x-data="portfolioApp()" x-init="initApp()" class="max-w-7xl mx-auto animate-fade-in relative space-y-8">
    
    {{-- Header Section --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h2 class="text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-purple-600">
                My Dashboard
            </h2>
            <p class="text-gray-500 text-sm mt-1">
                最新為替レート: 1 USD = <span x-text="exchangeRate.toFixed(2)"></span> JPY
            </p>
        </div>
        <div class="flex flex-col md:items-end gap-1.5">
            <div class="flex flex-wrap md:justify-end gap-3">
                {{-- 通貨切替トグル --}}
                <button @click="toggleCurrency()" class="flex items-center gap-2 bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 focus:ring-4 focus:ring-gray-100 font-bold py-2.5 px-4 rounded-full shadow-sm transition-all text-sm">
                    <span x-show="currency === 'JPY'">🇯🇵 JPY表示中</span>
                    <span x-show="currency === 'USD'">🇺🇸 USD表示中</span>
                    <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>
                </button>
                
                <button @click="fetchData()" :disabled="isRefreshing || stocks.length === 0" class="flex items-center gap-2 bg-white border border-gray-200 text-indigo-600 hover:bg-indigo-50 focus:ring-4 focus:ring-indigo-100 font-bold py-2.5 px-4 rounded-full shadow-sm transition-all disabled:opacity-50 disabled:cursor-not-allowed text-sm">
                    <svg :class="{'animate-spin': isRefreshing}" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span x-text="isRefreshing ? '取得中...' : '最新価格を再取得'"></span>
                </button>
                
                <button @click="openModal()" class="flex items-center gap-2 bg-gradient-to-r from-indigo-500 to-indigo-600 hover:from-indigo-600 hover:to-indigo-700 text-white font-bold py-2.5 px-5 rounded-full shadow-lg shadow-indigo-200 transition-all transform hover:-translate-y-0.5 text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    新規銘柄
                </button>
            </div>
            <p class="text-[10px] text-gray-400 opacity-90 pl-1 md:pr-1">※データはキャッシュされるためリアルタイムではありません（更新目安: 株価 2時間 / 為替 12時間）</p>
        </div>
    </div>

    {{-- Top Cards (Summary & Charts) --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        
        {{-- Total P&L Card --}}
        <div class="rounded-3xl p-8 shadow-sm border transition-all duration-300 relative overflow-hidden flex flex-col" 
             :class="totalDiff >= 0 ? 'bg-gradient-to-br from-green-50 to-emerald-100 border-green-200' : 'bg-gradient-to-br from-red-50 to-rose-100 border-red-200'">
            <div class="absolute -right-10 -top-10 opacity-10 text-9xl">
                <span x-text="totalDiff >= 0 ? '📈' : '📉'"></span>
            </div>
            <p class="text-sm font-semibold tracking-wide uppercase mb-2" :class="totalDiff >= 0 ? 'text-green-700' : 'text-red-700'">
                トータル評価損益
            </p>
            <h1 class="text-4xl font-black tracking-tighter" :class="totalDiff >= 0 ? 'text-green-800' : 'text-red-800'">
                <span x-text="totalDiff > 0 ? '+' : ''"></span><span x-text="formatMoney(totalDiff)"></span>
            </h1>
            <p class="text-sm font-bold mt-1 opacity-70" :class="totalDiff >= 0 ? 'text-green-800' : 'text-red-800'">
                <span x-text="totalDiff > 0 ? '+' : ''"></span><span x-text="totalPrice > 0 ? ((totalDiff / (totalPrice - totalDiff)) * 100).toFixed(2) + '%' : '0.00%'"></span>
            </p>
            
            <div class="mt-auto pt-6 border-t" :class="totalDiff >= 0 ? 'border-green-200/50' : 'border-red-200/50'">
                <p class="text-xs font-semibold uppercase opacity-70 mb-1 flex justify-between items-center">
                    <span>総資産額</span>
                    <button @click="saveSnapshot()" class="bg-white/50 hover:bg-white rounded px-2 py-0.5 text-xs font-bold transition">履歴保存</button>
                </p>
                <p class="text-2xl font-bold" :class="totalDiff >= 0 ? 'text-green-900' : 'text-red-900'" x-text="formatMoney(totalPrice)"></p>
            </div>
        </div>

        {{-- Portfolio Allocation Chart --}}
        <div class="lg:col-span-2 xl:col-span-2 bg-white rounded-3xl shadow-sm border border-gray-100 p-6 flex flex-col sm:flex-row items-center gap-6">
            <div class="w-full sm:w-1/2 flex flex-col justify-center h-48 relative">
                <canvas id="portfolioChart"></canvas>
                <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none" x-show="stocks.length > 0">
                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest leading-none">Total</p>
                </div>
                <div x-show="stocks.length === 0" class="absolute inset-0 flex items-center justify-center">
                    <p class="text-gray-400 text-xs font-medium">No Data</p>
                </div>
            </div>
            
            <div class="w-full sm:w-1/2">
                <h3 class="text-sm font-bold text-gray-800 mb-3 border-b border-gray-100 pb-2">資産比率</h3>
                <div class="h-40 overflow-y-auto pr-2 space-y-1.5 scrollbar-thin">
                    <template x-for="(stock, index) in stocks" :key="stock.id">
                        <div class="flex items-center justify-between p-1.5 rounded-lg hover:bg-gray-50 border border-transparent hover:border-gray-100 transition">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full shadow-sm" :style="`background-color: ${getChartColor(index)}`"></span>
                                <p class="font-bold text-gray-700 text-xs truncate max-w-[80px]" x-text="stock.name"></p>
                            </div>
                            <div class="text-right flex items-center gap-3">
                                <p class="font-bold text-gray-600 text-[11px] font-mono" x-text="formatMoney(stock.price * stock.quantity)"></p>
                                <p class="text-[10px] font-black w-8 text-indigo-500" x-text="totalPrice > 0 ? ((stock.price * stock.quantity / totalPrice) * 100).toFixed(1) + '%' : '0%'"></p>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        {{-- History Line Chart --}}
        <div class="lg:col-span-3 xl:col-span-1 bg-white rounded-3xl shadow-sm border border-gray-100 p-6 flex flex-col">
            <h3 class="text-sm font-bold text-gray-800 mb-4 border-b border-gray-100 pb-2 flex justify-between items-center">
                <span>総資産推移</span>
                <button @click="clearHistory()" class="text-xs text-rose-500 hover:underline">リセット</button>
            </h3>
            <div class="flex-grow w-full relative min-h-[150px]">
                 <canvas id="historyChart"></canvas>
                 <div x-show="history.length < 2" class="absolute inset-0 flex items-center justify-center bg-white/80">
                    <p class="text-gray-400 text-[10px] font-medium text-center px-4">履歴データが不足しています。<br>「履歴保存」で最新額を記録しましょう。</p>
                </div>
            </div>
        </div>

    </div>

    {{-- Stock List --}}
    <h3 class="text-xl font-bold text-gray-800 -mb-2 border-b border-gray-200 pb-2">保有銘柄一覧</h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <template x-for="stock in stocks" :key="stock.id">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition-all duration-300 relative group flex flex-col">
                <div class="absolute top-3 right-3 flex opacity-0 group-hover:opacity-100 transition-opacity gap-0.5">
                    <button @click="openAlertsModal(stock.code)" class="p-1.5 text-gray-400 bg-white shadow-sm border border-gray-100 rounded-full transition" :class="alerts.some(a => a.symbol === stock.code && a.enabled) ? 'text-amber-500 bg-amber-50 border-amber-200' : 'hover:text-amber-500 hover:bg-amber-50'" title="アラート設定">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                    </button>
                    <button @click="openNotesModal(stock.code)" class="p-1.5 text-gray-400 bg-white shadow-sm border border-gray-100 rounded-full transition" :class="tradeNotes[stock.code] && (tradeNotes[stock.code].buy_thesis || tradeNotes[stock.code].exit_rule || tradeNotes[stock.code].risk_note) ? 'text-blue-500 bg-blue-50 border-blue-200' : 'hover:text-blue-500 hover:bg-blue-50'" title="売買メモ">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                    </button>
                    <button @click="openModal(stock)" class="p-1.5 text-gray-400 hover:text-indigo-600 bg-white shadow-sm hover:bg-indigo-50 border border-gray-100 rounded-full transition" title="編集">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                    </button>
                    <button @click="deleteStock(stock.id)" class="p-1.5 text-gray-400 hover:text-rose-600 bg-white shadow-sm hover:bg-rose-50 border border-gray-100 rounded-full transition" title="削除">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                    </button>
                </div>
                
                <div class="flex items-center gap-3 mb-4 pr-14">
                    <div class="w-10 h-10 shrink-0 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600 font-bold text-xs uppercase tracking-wider">
                        <span x-text="stock.code.substring(0,2)"></span>
                    </div>
                    <div class="min-w-0">
                        <h3 class="font-bold text-gray-800 text-[15px] leading-tight truncate" x-text="stock.name" :title="stock.name"></h3>
                        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider" x-text="stock.code"></p>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-2 mb-4 flex-grow">
                    <div class="bg-gray-50 rounded-lg p-2.5 flex flex-col justify-center">
                        <p class="text-[9px] text-gray-400 font-bold uppercase tracking-wider mb-0.5">現在値</p>
                        <p class="text-[13px] font-bold text-gray-700 font-mono" x-text="formatMoney(stock.price, false)"></p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-2.5 flex flex-col justify-center">
                        <p class="text-[9px] text-gray-400 font-bold uppercase tracking-wider mb-0.5">保有数</p>
                        <p class="text-[13px] font-bold text-gray-700 font-mono" x-text="stock.quantity"></p>
                    </div>
                </div>

                <div class="border-t border-gray-100 pt-3 flex flex-col gap-1.5">
                    <div class="flex justify-between items-center text-[10px]">
                        <span class="font-semibold text-gray-400 uppercase">取得単価</span>
                        <span class="font-bold text-gray-500 font-mono" x-text="formatMoney(stock.buy_price, false)"></span>
                    </div>
                    <div class="flex justify-between items-center bg-gray-50 rounded pl-2">
                        <span class="text-[10px] font-semibold text-gray-500 uppercase">評価損益</span>
                        <span class="text-[13px] font-black px-2 py-1 rounded" 
                              :class="getDiff(stock) > 0 ? 'bg-green-100 text-green-700' : (getDiff(stock) < 0 ? 'bg-rose-100 text-rose-700' : 'text-gray-500')">
                            <span x-text="getDiff(stock) > 0 ? '+' : ''"></span><span x-text="formatMoney(getDiff(stock))"></span>
                        </span>
                    </div>
                </div>
            </div>
        </template>
        
        <div x-show="stocks.length === 0" class="col-span-full bg-white rounded-3xl border border-dashed border-gray-300 p-12 text-center flex flex-col items-center justify-center text-gray-400">
            <svg class="h-12 w-12 mb-3 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
            <p class="font-bold text-sm text-gray-500">銘柄が登録されていません</p>
            <p class="text-[11px] mt-1">「新規銘柄を追加」ボタンから登録してください。</p>
        </div>
    </div>

    {{-- Create/Edit Modal --}}
    <div x-show="modalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0" style="display: none;">
        <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" @click="closeModal()"></div>
        
        <div class="bg-white rounded-3xl shadow-2xl max-w-sm w-full relative z-10 transform transition-all p-6" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100">
            <button @click="closeModal()" class="absolute top-3 right-3 text-gray-400 hover:text-gray-600 p-1.5 bg-gray-50 rounded-full hover:bg-gray-100 transition">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
            
            <h3 class="text-xl font-bold text-gray-800 mb-5" x-text="editingId ? '銘柄情報の編集' : '新規銘柄の追加'"></h3>
            
            <form @submit.prevent="saveStock" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">銘柄名</label>
                    <input type="text" x-model="form.name" required class="w-full rounded-xl border-gray-200 bg-gray-50 focus:bg-white focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 text-sm shadow-sm transition" placeholder="Apple Inc.">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">ティッカー/コード (USDベース想定)</label>
                    <select x-model="form.code" required class="w-full rounded-xl border-gray-200 bg-gray-50 focus:bg-white focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 text-sm shadow-sm transition uppercase">
                        <option value="" disabled>選択してください</option>
                        @foreach(config('constants.enable_symbols', []) as $symbol)
                            <option value="{{ $symbol }}">{{ $symbol }}</option>
                        @endforeach
                    </select>
                    <p class="text-[10.5px] text-amber-600 mt-1.5 font-medium">※試験運用中のため、登録可能な銘柄は限定されています</p>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">取得単価 <span class="text-indigo-500 font-normal ml-1">USD</span></label>
                        <input type="number" step="0.0001" x-model.number="form.buy_price" required class="w-full rounded-xl border-gray-200 bg-gray-50 focus:bg-white focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 text-sm shadow-sm transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">保有数</label>
                        <input type="number" step="0.0001" x-model.number="form.quantity" required class="w-full rounded-xl border-gray-200 bg-gray-50 focus:bg-white focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 text-sm shadow-sm transition">
                    </div>
                </div>
                <p class="text-[10px] text-gray-400">※米国株を想定しており、取得単価はUSDで入力してください。</p>
                
                <div class="pt-2">
                    <button type="submit" class="w-full bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white font-bold py-3 px-4 rounded-xl shadow-md transform transition-all active:scale-95 text-sm">
                        <span x-text="editingId ? '保存する' : '追加する'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    {{-- Alerts Modal --}}
    <div x-show="alertsModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0" style="display: none;">
        <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" @click="closeAlertsModal()"></div>
        <div class="bg-white rounded-3xl shadow-2xl max-w-sm w-full relative z-10 p-6">
            <button @click="closeAlertsModal()" class="absolute top-3 right-3 text-gray-400 hover:text-gray-600 p-1.5 bg-gray-50 rounded-full hover:bg-gray-100"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
            <h3 class="text-xl font-bold text-gray-800 mb-5">アラート設定 <span class="text-sm text-gray-500 ml-1" x-text="alertsEditingSymbol"></span></h3>
            <form @submit.prevent="saveAlert" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">通知条件</label>
                    <select x-model="alertForm.condition_type" class="w-full rounded-xl border-gray-200 bg-gray-50 focus:bg-white focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 text-sm">
                        <option value="above">現在値が設定価格【以上】</option>
                        <option value="below">現在値が設定価格【以下】</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">目標価格 (USD)</label>
                    <input type="number" step="0.0001" x-model.number="alertForm.threshold" required class="w-full rounded-xl border-gray-200 bg-gray-50 focus:bg-white focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 text-sm shadow-sm transition">
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="alertEnabled" x-model="alertForm.enabled" class="rounded text-indigo-600 focus:ring-indigo-500">
                    <label for="alertEnabled" class="text-xs font-bold text-gray-700">アラートを有効にする</label>
                </div>
                <div class="pt-2 flex gap-2">
                    <button type="submit" class="flex-1 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white font-bold py-3 px-4 rounded-xl shadow-md text-sm">保存</button>
                    <button type="button" @click="deleteAlert(alertsEditingSymbol)" x-show="alerts.some(a => a.symbol === alertsEditingSymbol)" class="bg-gray-100 hover:bg-red-50 text-red-500 font-bold py-3 px-4 rounded-xl text-sm">削除</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Notes Modal --}}
    <div x-show="notesModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0" style="display: none;">
        <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" @click="closeNotesModal()"></div>
        <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full relative z-10 p-6">
            <button @click="closeNotesModal()" class="absolute top-3 right-3 text-gray-400 hover:text-gray-600 p-1.5 bg-gray-50 rounded-full hover:bg-gray-100"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
            <h3 class="text-xl font-bold text-gray-800 mb-5">売買ルールメモ <span class="text-sm text-gray-500 ml-1" x-text="notesEditingSymbol"></span></h3>
            <form @submit.prevent="saveNotes" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">買い理由 (Buy Thesis)</label>
                    <textarea x-model="noteForm.buy_thesis" rows="2" class="w-full rounded-xl border-gray-200 bg-gray-50 focus:bg-white focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 text-sm" placeholder="AI分野での成長期待など"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">売却条件 (Exit Rule)</label>
                    <textarea x-model="noteForm.exit_rule" rows="2" class="w-full rounded-xl border-gray-200 bg-gray-50 focus:bg-white focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 text-sm" placeholder="成長率が10%を割ったら売却など"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">リスク・その他 (Risk Note)</label>
                    <textarea x-model="noteForm.risk_note" rows="2" class="w-full rounded-xl border-gray-200 bg-gray-50 focus:bg-white focus:border-indigo-500 focus:ring-indigo-500 px-3 py-2 text-sm" placeholder="為替リスクありなど"></textarea>
                </div>
                <div class="pt-2">
                    <button type="submit" class="w-full bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-bold py-3 px-4 rounded-xl shadow-md text-sm">保存する</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Toasts Container --}}
    <div class="fixed bottom-4 right-4 z-50 flex flex-col gap-2 pointer-events-none">
        <template x-for="toast in toasts" :key="toast.id">
            <div class="min-w-[250px] bg-white rounded-xl shadow-xl border-l-4 p-4 flex items-start gap-3 pointer-events-auto transition-all animate-fade-in"
                 :class="toast.type === 'warning' ? 'border-amber-500' : 'border-indigo-500'">
                <div class="flex-grow">
                    <p class="text-sm font-bold text-gray-800" x-text="toast.message"></p>
                </div>
                <button @click="removeToast(toast.id)" class="text-gray-400 hover:text-gray-600 shrink-0">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
        </template>
    </div>
</div>

<style>
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    .animate-fade-in { animation: fadeIn 0.4s ease-out forwards; }
    .scrollbar-thin::-webkit-scrollbar { width: 3px; }
    .scrollbar-thin::-webkit-scrollbar-track { background: transparent; }
    .scrollbar-thin::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
</style>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('portfolioApp', () => ({
        stocks: JSON.parse(localStorage.getItem('my_stocks')) || [],
        history: JSON.parse(localStorage.getItem('my_history')) || [],
        modalOpen: false,
        editingId: null,
        isRefreshing: false,
        currency: localStorage.getItem('my_currency') || 'JPY',
        exchangeRate: parseFloat(localStorage.getItem('my_exchange_rate')) || 150.0,
        form: { name: '', code: '', buy_price: '', quantity: '' },
        
        alerts: JSON.parse(localStorage.getItem('stock_alerts')) || [],
        notifiedAlertIds: [],
        alertsModalOpen: false,
        alertsEditingSymbol: null,
        alertForm: { condition_type: 'above', threshold: '', enabled: true },
        
        tradeNotes: JSON.parse(localStorage.getItem('stock_trade_notes')) || {},
        notesModalOpen: false,
        notesEditingSymbol: null,
        noteForm: { buy_thesis: '', exit_rule: '', risk_note: '' },
        
        earnings: JSON.parse(sessionStorage.getItem('stock_earnings')) || {},
        
        toasts: [],
        pieChart: null,
        lineChart: null,
        
        colors: ['#6366f1', '#10b981', '#f59e0b', '#f43f5e', '#8b5cf6', '#0ea5e9', '#ec4899', '#14b8a6'],

        get totalDiff() {
            return this.stocks.reduce((sum, stock) => sum + this.getDiff(stock), 0);
        },
        
        get totalPrice() {
            return this.stocks.reduce((sum, stock) => sum + (stock.price * stock.quantity), 0);
        },

        getDiff(stock) {
            return (stock.price - stock.buy_price) * stock.quantity;
        },

        getChartColor(index) {
            return this.colors[index % this.colors.length];
        },

        // 為替を考慮したフォーマット表示
        formatMoney(val, showSymbol = true) {
            let num = Number(val);
            if (this.currency === 'JPY') {
                num = num * this.exchangeRate;
                return (showSymbol ? '¥' : '') + Math.round(num).toLocaleString('ja-JP');
            } else {
                return (showSymbol ? '$' : '') + num.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            }
        },

        toggleCurrency() {
            this.currency = this.currency === 'JPY' ? 'USD' : 'JPY';
            localStorage.setItem('my_currency', this.currency);
            this.updateCharts();
        },

        initApp() {
            this.$watch('stocks', val => {
                localStorage.setItem('my_stocks', JSON.stringify(val));
                this.updateCharts();
            });
            this.$watch('history', val => {
                localStorage.setItem('my_history', JSON.stringify(val));
                this.updateCharts();
            });
            this.$watch('alerts', val => {
                localStorage.setItem('stock_alerts', JSON.stringify(val));
            });
            this.$watch('tradeNotes', val => {
                localStorage.setItem('stock_trade_notes', JSON.stringify(val));
            });
            setTimeout(() => {
                this.initPieChart();
                this.initLineChart();
                // 初回のみ為替レートを自動取得
                if (!localStorage.getItem('my_exchange_rate')) {
                    this.fetchExchangeRate();
                }
            }, 50);
        },

        async fetchData() {
            this.isRefreshing = true;
            try {
                // APIを並列実行
                await Promise.all([this.fetchPrices(), this.fetchExchangeRate()]);
                alert('データを最新化しました。');
            } finally {
                this.isRefreshing = false;
            }
        },

        async fetchPrices() {
            if (this.stocks.length === 0) return;
            try {
                const symbols = this.stocks.map(s => s.code).join(',');
                const res = await fetch(`/api/prices?symbols=${symbols}`);
                const data = await res.json();
                
                if (Array.isArray(data)) {
                    this.stocks = this.stocks.map(stock => {
                        let newPrice = stock.price;
                        // FMP APIの配列データから当該銘柄を探す
                        const quote = data.find(q => q.symbol === stock.code);
                        if (quote && quote.price !== undefined) {
                            newPrice = parseFloat(quote.price);
                        }
                        return { ...stock, price: newPrice };
                    });
                    // アラートのチェックを実行
                    this.checkAlerts();
                } else if (data.error) {
                    console.error('API Error:', data.error);
                }
            } catch (error) {
                console.error('Failed to fetch prices', error);
            }
        },

        async fetchExchangeRate() {
            try {
                const res = await fetch('/api/exchange');
                const data = await res.json();
                if (data.rate) {
                    this.exchangeRate = parseFloat(data.rate);
                    localStorage.setItem('my_exchange_rate', this.exchangeRate.toString());
                }
            } catch (error) {
                console.error('Failed to fetch exchange rate', error);
            }
        },

        showToast(message, type = 'info') {
            const id = Date.now().toString() + Math.random().toString(36).substr(2, 5);
            this.toasts.push({ id, message, type });
            setTimeout(() => {
                this.toasts = this.toasts.filter(t => t.id !== id);
            }, 5000);
        },
        
        removeToast(id) {
            this.toasts = this.toasts.filter(t => t.id !== id);
        },

        checkAlerts() {
            this.alerts.forEach(alert => {
                if (!alert.enabled) return;
                if (this.notifiedAlertIds.includes(alert.id)) return;
                
                const stock = this.stocks.find(s => s.code === alert.symbol);
                if (!stock) return;
                
                const currentPrice = stock.price;
                const threshold = parseFloat(alert.threshold);
                let triggered = false;
                
                if (alert.condition_type === 'above' && currentPrice >= threshold) {
                    triggered = true;
                } else if (alert.condition_type === 'below' && currentPrice <= threshold) {
                    triggered = true;
                }
                
                if (triggered) {
                    this.notifiedAlertIds.push(alert.id);
                    alert.last_triggered_at = new Date().toISOString();
                    const message = `${alert.symbol}が目標価格(${alert.condition_type === 'above' ? '以上' : '以下'}: $${threshold})に到達しました！`;
                    this.showToast(message, 'warning');
                }
            });
            this.alerts = [...this.alerts];
        },

        openAlertsModal(symbol) {
            this.alertsEditingSymbol = symbol;
            const existing = this.alerts.find(a => a.symbol === symbol) || { condition_type: 'above', threshold: '', enabled: true };
            this.alertForm = { ...existing };
            this.alertsModalOpen = true;
        },

        closeAlertsModal() {
            this.alertsModalOpen = false;
        },

        saveAlert() {
            if (!this.alertForm.threshold) return;
            const idx = this.alerts.findIndex(a => a.symbol === this.alertsEditingSymbol);
            const alertData = {
                id: this.alertForm.id || Date.now().toString(),
                symbol: this.alertsEditingSymbol,
                condition_type: this.alertForm.condition_type,
                threshold: parseFloat(this.alertForm.threshold),
                enabled: this.alertForm.enabled !== undefined ? this.alertForm.enabled : true,
                last_triggered_at: this.alertForm.last_triggered_at || null
            };
            
            if (idx !== -1) {
                this.alerts[idx] = alertData;
            } else {
                this.alerts.push(alertData);
            }
            
            this.notifiedAlertIds = this.notifiedAlertIds.filter(id => id !== alertData.id);
            this.alerts = [...this.alerts];
            this.alertsModalOpen = false;
        },
        
        deleteAlert(symbol) {
            if(confirm('アラート設定を削除しますか？')) {
                this.alerts = this.alerts.filter(a => a.symbol !== symbol);
                this.alertsModalOpen = false;
            }
        },

        openNotesModal(symbol) {
            this.notesEditingSymbol = symbol;
            const existing = this.tradeNotes[symbol] || { buy_thesis: '', exit_rule: '', risk_note: '' };
            this.noteForm = { ...existing };
            this.notesModalOpen = true;
        },

        closeNotesModal() {
            this.notesModalOpen = false;
        },

        saveNotes() {
            this.tradeNotes[this.notesEditingSymbol] = {
                ...this.noteForm,
                updated_at: new Date().toISOString()
            };
            this.tradeNotes = { ...this.tradeNotes };
            this.notesModalOpen = false;
        },

        openModal(stock = null) {
            if (stock) {
                this.editingId = stock.id;
                this.form = { ...stock };
            } else {
                this.editingId = null;
                this.form = { name: '', code: '', buy_price: '', quantity: '' };
            }
            this.modalOpen = true;
        },

        closeModal() {
            this.modalOpen = false;
        },

        saveStock() {
            const formData = {
                ...this.form,
                code: this.form.code.toUpperCase(),
                buy_price: parseFloat(this.form.buy_price),
                quantity: parseFloat(this.form.quantity),
            };

            if (this.editingId) {
                const idx = this.stocks.findIndex(s => s.id === this.editingId);
                if (idx !== -1) {
                    const currentPrice = this.stocks[idx].price || formData.buy_price; 
                    this.stocks[idx] = { ...this.stocks[idx], ...formData, price: currentPrice };
                }
            } else {
                this.stocks.push({
                    id: Date.now().toString(),
                    ...formData,
                    price: formData.buy_price
                });
            }
            this.stocks = [...this.stocks]; // trigger reactivity
            this.closeModal();
        },

        deleteStock(id) {
            if(confirm('削除しますか？')) {
                this.stocks = this.stocks.filter(s => s.id !== id);
            }
        },

        saveSnapshot() {
            if (this.totalPrice <= 0) return;
            const now = new Date();
            const dateStr = `${now.getMonth()+1}/${now.getDate()} ${now.getHours()}:${now.getMinutes()}`;
            this.history.push({
                date: dateStr,
                totalUSD: this.totalPrice
            });
            // trigger watch
            this.history = [...this.history];
        },

        clearHistory() {
            if(confirm('履歴データをリセットしますか？')) {
                this.history = [];
            }
        },

        updateCharts() {
            if (this.pieChart) {
                this.pieChart.data = this.getPieData();
                this.pieChart.update();
            }
            if (this.lineChart) {
                this.lineChart.data = this.getLineData();
                this.lineChart.update();
            }
        },

        initPieChart() {
            const ctx = document.getElementById('portfolioChart');
            if(!ctx) return;
            this.pieChart = new Chart(ctx, {
                type: 'doughnut',
                data: this.getPieData(),
                options: {
                    responsive: true, maintainAspectRatio: false, cutout: '70%',
                    plugins: { legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: (context) => ' ' + this.formatMoney(context.raw / (this.currency==='JPY'?this.exchangeRate:1))
                            }
                        }
                    }
                }
            });
        },

        getPieData() {
            // Circle Chartは常に同じ比率なので、描画値自体はUSDのままで良い（Tooltipのみ変換表示）
            return {
                labels: this.stocks.map(s => s.name),
                datasets: [{
                    data: this.stocks.map(s => s.price * s.quantity * (this.currency === 'JPY' ? this.exchangeRate : 1)),
                    backgroundColor: this.stocks.map((_, i) => this.getChartColor(i)),
                    borderWidth: 0, hoverOffset: 3
                }]
            };
        },

        initLineChart() {
            const ctx = document.getElementById('historyChart');
            if(!ctx) return;
            this.lineChart = new Chart(ctx, {
                type: 'line',
                data: this.getLineData(),
                options: {
                    responsive: true, maintainAspectRatio: false,
                    scales: {
                        x: { display: false },
                        y: { display: true, beginAtZero: false, ticks: { font: {size: 10} } }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: (context) => ' ' + this.formatMoney(context.raw / (this.currency==='JPY'?this.exchangeRate:1))
                            }
                        }
                    },
                    elements: {
                        line: { tension: 0.4 },
                        point: { radius: 3, hoverRadius: 5 }
                    }
                }
            });
        },

        getLineData() {
            return {
                labels: this.history.map(h => h.date),
                datasets: [{
                    label: '総資産額',
                    data: this.history.map(h => h.totalUSD * (this.currency === 'JPY' ? this.exchangeRate : 1)),
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    fill: true,
                    borderWidth: 2
                }]
            };
        }
    }));
});
</script>
@endsection