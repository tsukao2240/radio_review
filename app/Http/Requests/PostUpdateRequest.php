<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PostUpdateRequest extends FormRequest
{
    public function authorize()
    {
        $post = $this->route('post');
        return $post && $this->user()->id === $post->user_id;
    }

    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:5000',
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'タイトルは必須項目です',
            'title.max' => 'タイトルは255文字以内で入力してください',
            'body.required' => '本文は必須項目です',
            'body.max' => '本文は5000文字以内で入力してください',
        ];
    }
}
