<?php

namespace App\Exceptions;

use Exception;

/**
 * 外部API（radiko等）関連の例外
 */
class ExternalApiException extends Exception
{
    /**
     * ユーザーフレンドリーなエラーメッセージを取得
     *
     * @return string
     */
    public function getUserMessage(): string
    {
        return '外部サービスとの通信に失敗しました。ネットワーク接続を確認の上、再度お試しください。';
    }
}
