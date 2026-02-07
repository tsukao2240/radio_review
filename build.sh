#!/bin/bash
# Viteビルドスクリプト
# 使用方法: ./build.sh

set -e

echo "========================================"
echo "Viteビルドを開始します..."
echo "========================================"

# Dockerコンテナ内でビルド実行
docker exec -u root radio_review_app sh -c "cd /var/www && npm install && npm run build"

echo ""
echo "========================================"
echo "ビルド完了！"
echo "========================================"
echo ""
echo "ビルドファイル:"
ls -lh /home/tsukao/project/radio_review/public/build/assets/*.js 2>/dev/null || echo "  JSファイルが見つかりません"
echo ""
echo "manifest.json:"
cat /home/tsukao/project/radio_review/public/build/manifest.json 2>/dev/null | grep "resources/js/app.jsx" || echo "  manifest.jsonが見つかりません"
echo ""
