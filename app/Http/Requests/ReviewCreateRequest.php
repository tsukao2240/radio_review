<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReviewCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:5000',
            'program_id' => 'required|integer|exists:radio_programs,id',
            'user_id' => 'required|integer|exists:users,id',
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'タイトルは必須です',
            'title.string' => 'タイトルは文字列である必要があります',
            'title.max' => 'タイトルは255文字以内で入力してください',
            'body.required' => '本文は必須です',
            'body.string' => '本文は文字列である必要があります',
            'body.max' => '本文は5000文字以内で入力してください',
            'program_id.required' => '番組IDは必須です',
            'program_id.integer' => '番組IDは整数である必要があります',
            'program_id.exists' => '指定された番組が存在しません',
            'user_id.required' => 'ユーザーIDは必須です',
            'user_id.integer' => 'ユーザーIDは整数である必要があります',
            'user_id.exists' => '指定されたユーザーが存在しません',
        ];
    }
}
