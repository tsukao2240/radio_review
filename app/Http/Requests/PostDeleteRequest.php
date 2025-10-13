<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PostDeleteRequest extends FormRequest
{
    public function authorize()
    {
        $post = $this->route('post');
        return $post && $this->user()->id === $post->user_id;
    }

    public function rules()
    {
        return [];
    }
}
