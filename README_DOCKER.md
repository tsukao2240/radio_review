# Docker環境 運用ガイド

## 概要

このプロジェクトはDocker ComposeでLaravel + React + Vite環境を構築しています。

## コンテナ構成

- **app**: PHP 8.2 + Laravel 11 (PHP-FPM)
- **webserver**: Nginx (Webサーバー)
- **db**: MySQL 8.0
- **redis**: Redis (キャッシュ/セッション)

## 基本コマンド

### コンテナの起動・停止

```bash
# 全コンテナ起動
docker-compose up -d

# 全コンテナ停止
docker-compose down

# コンテナ再起動
docker-compose restart

# ログ確認
docker-compose logs -f app
```

### ビルド

```bash
# Viteビルド（簡単な方法）
./build.sh

# または手動で実行
docker exec -u root radio_review_app sh -c "cd /var/www && npm install && npm run build"

# Dockerイメージの再ビルド
docker-compose build --no-cache
docker-compose up -d
```

### マイグレーション

```bash
# マイグレーション実行
docker exec radio_review_app php artisan migrate

# マイグレーション状態確認
docker exec radio_review_app php artisan migrate:status
```

### 番組データ登録

```bash
# 全放送局の週間番組データをDBに一括登録
docker exec radio_review_app php artisan radio:insert-programs
```

## ファイル構成

### ビルド成果物の配置

- **ホスト側**: `public/build/` - Viteビルド成果物（Gitで管理）
- **コンテナ側**: `/var/www/public/build/` - ホストと同期

### 重要な設定ファイル

- `docker-compose.yml` - Docker構成
- `Dockerfile` - appコンテナイメージ定義
- `docker/nginx/default.conf` - Nginx設定
- `.gitignore` - Git除外設定

## トラブルシューティング

### ビルドファイルが反映されない

```bash
# ビルドスクリプトを実行
./build.sh

# またはコンテナを再起動
docker-compose restart app webserver
```

### 権限エラーが発生する

```bash
# storageディレクトリの権限修正
docker exec -u root radio_review_app chmod -R 775 /var/www/storage /var/www/bootstrap/cache
docker exec -u root radio_review_app chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# ffmpegの実行権限
docker exec -u root radio_review_app chmod +x /var/www/ffmpeg/ffmpeg-7.0.2-amd64-static/ffmpeg
```

### データベースをリセットしたい

```bash
# コンテナとボリュームを削除
docker-compose down -v

# 再起動してマイグレーション
docker-compose up -d
sleep 10
docker exec radio_review_app php artisan migrate --force
docker exec radio_review_app php artisan radio:insert-programs
```

## 開発ワークフロー

1. **コードを編集** (resources/js/, resources/css/, app/)
2. **ビルドを実行** `./build.sh`
3. **ブラウザで確認** http://localhost:8000
4. **変更をコミット** `git add .` → `git commit` → `git push`

## 本番デプロイ

本番環境へのデプロイ手順は `DEPLOYMENT.md` を参照してください。

## 注意事項

- `node_modules/` はDockerコンテナ内で管理され、ホスト側には同期されません
- `public/build/` のビルド成果物はGitで管理されます
- 環境変数は `.env` ファイルで管理します（Gitには含めない）
