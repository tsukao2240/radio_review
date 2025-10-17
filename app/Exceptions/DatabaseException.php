<?php

namespace App\Exceptions;

use Exception;

/**
 * データベース関連の例外
 */
class DatabaseException extends Exception
{
    /**
     * ユーザーフレンドリーなエラーメッセージを取得
     *
     * @return string
     */
    public function getUserMessage(): string
    {
        return 'データベースへの接続に問題が発生しました。しばらく待ってから再度お試しください。';
    }
}
