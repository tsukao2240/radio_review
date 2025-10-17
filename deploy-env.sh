#!/bin/bash
# 環境に応じて.envファイルを切り替えるスクリプト

# 使用方法: ./deploy-env.sh [local|production|docker]

ENVIRONMENT=${1:-local}

if [ "$ENVIRONMENT" != "local" ] && [ "$ENVIRONMENT" != "production" ] && [ "$ENVIRONMENT" != "docker" ]; then
    echo "エラー: 無効な環境指定です"
    echo "使用方法: ./deploy-env.sh [local|production|docker]"
    exit 1
fi

SOURCE_FILE=".env.$ENVIRONMENT"
TARGET_FILE=".env"

if [ ! -f "$SOURCE_FILE" ]; then
    echo "エラー: $SOURCE_FILE が見つかりません"
    exit 1
fi

echo "環境: $ENVIRONMENT"
echo "$SOURCE_FILE -> $TARGET_FILE にコピーします..."

cp "$SOURCE_FILE" "$TARGET_FILE"

if [ $? -eq 0 ]; then
    echo "✓ 環境ファイルのコピーが完了しました"

    # APP_KEYが空の場合は生成を促す
    if ! grep -q "APP_KEY=base64:" "$TARGET_FILE"; then
        echo ""
        echo "⚠ APP_KEYが設定されていません"
        echo "以下のコマンドで生成してください:"
        echo "  php artisan key:generate"
    fi

    # 本番環境の場合は追加のチェック
    if [ "$ENVIRONMENT" = "production" ]; then
        echo ""
        echo "本番環境のデプロイチェックリスト:"
        echo "  [ ] APP_KEYが設定されている"
        echo "  [ ] APP_DEBUG=falseになっている"
        echo "  [ ] データベースパスワードが設定されている"
        echo "  [ ] Redisパスワードが設定されている"
        echo "  [ ] メール設定が正しい"
        echo "  [ ] APP_URLが正しいドメインになっている"
    fi

    exit 0
else
    echo "✗ ファイルのコピーに失敗しました"
    exit 1
fi
