<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStockRequest extends FormRequest
{
    // 認証チェック（今回は誰でもOKなのでtrueに）
    public function authorize(): bool
    {
        return true;
    }

    // ここにバリデーションルールを移植
    public function rules(): array
    {
        return [
            'name' => 'required|max:20',
            'code' => 'required|string|max:10',
            'buy_price' => 'required|numeric|min:0', // 取得単価
            'quantity' => 'required|numeric|min:1',  // 株数
        ];
    }

    // エラーメッセージもここにまとめられる
    public function messages(): array
    {
        return [
            'name.required' => '銘柄名は必ず入力してください。',
            'name.max' => '銘柄名は20文字以内で入力してください。',
            'price.required' => '価格を入力してください。',
            'price.numeric' => '価格は数値で入力してください。',
        ];
    }
}