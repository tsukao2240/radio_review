<?php

namespace App\Exceptions;

use Exception;

/**
 * 録音関連の例外
 */
class RecordingException extends Exception
{
    /**
     * ユーザーフレンドリーなエラーメッセージを取得
     *
     * @return string
     */
    public function getUserMessage(): string
    {
        return '録音処理中にエラーが発生しました。設定を確認の上、再度お試しください。';
    }
}
