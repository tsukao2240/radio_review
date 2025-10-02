<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FavoriteProgramRequest extends FormRequest
{
    /**
     * ユーザーがこのリクエストを行う権限があるか判定
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * バリデーションルール
     *
     * @return array
     */
    public function rules()
    {
        return [
            'station_id' => 'required|string|max:50',
            'program_title' => 'required|string|max:255',
        ];
    }

    /**
     * バリデーションエラーメッセージ
     *
     * @return array
     */
    public function messages()
    {
        return [
            'station_id.required' => '放送局IDは必須です',
            'station_id.string' => '放送局IDは文字列である必要があります',
            'station_id.max' => '放送局IDは50文字以内で入力してください',
            'program_title.required' => '番組タイトルは必須です',
            'program_title.string' => '番組タイトルは文字列である必要があります',
            'program_title.max' => '番組タイトルは255文字以内で入力してください',
        ];
    }
}
