<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'keyword' => 'required|string|min:1|max:100',
        ];
    }

    public function messages()
    {
        return [
            'keyword.required' => '検索キーワードを入力してください',
            'keyword.min' => '検索キーワードは1文字以上で入力してください',
            'keyword.max' => '検索キーワードは100文字以内で入力してください',
        ];
    }
}
