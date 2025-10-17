<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RecordingScheduleRequest extends FormRequest
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
            'scheduled_start_time' => 'required|string|size:14|regex:/^\d{14}$/',
            'scheduled_end_time' => 'required|string|size:14|regex:/^\d{14}$/',
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
            'scheduled_start_time.required' => '開始時刻は必須です',
            'scheduled_start_time.string' => '開始時刻は文字列である必要があります',
            'scheduled_start_time.size' => '開始時刻はYYYYMMDDHHMMSS形式（14桁）で入力してください',
            'scheduled_start_time.regex' => '開始時刻は数字のみで入力してください',
            'scheduled_end_time.required' => '終了時刻は必須です',
            'scheduled_end_time.string' => '終了時刻は文字列である必要があります',
            'scheduled_end_time.size' => '終了時刻はYYYYMMDDHHMMSS形式（14桁）で入力してください',
            'scheduled_end_time.regex' => '終了時刻は数字のみで入力してください',
        ];
    }
}
