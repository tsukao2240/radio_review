## このアプリについて
ラジオ番組の番組表の表示や感想を投稿するアプリです。<br>
URL:http://radio-review.com/<br>
![キャプチャ](https://user-images.githubusercontent.com/59298479/82464028-87da0280-9af8-11ea-908c-1d1bda0c0905.PNG)

## 機能
<ラジオ番組>
・検索機能<br>
・表示機能<br>
・番組表表示機能<br>
・番組詳細表示機能<br>
・現在放送中の番組一覧を表示<br>
<感想>
・投稿機能<br>
・編集機能<br>
・削除機能<br>
<マイページ>
・投稿した感想の表示機能<br>
・投稿した感想の編集機能<br>
・投稿した感想の削除機能<br>
・会員登録機能<br>
・ログイン機能<br>


## 使用技術
・PHP 8.2+<br>
・Laravel 11<br>
・HTML<br>
・CSS<br>
・JavaScript<br>
・React<br>
・Vite<br>
・MySQL<br>
・Redis<br>
・Docker<br>

## Docker開発環境のセットアップ

### 前提条件
- Docker Desktop がインストールされていること
- Git がインストールされていること

### セットアップ手順

1. **リポジトリのクローン**
   ```bash
   git clone [repository-url]
   cd radio_review
   ```

2. **環境設定ファイルの準備**
   ```bash
   # Docker用の環境設定をコピー
   copy .env.docker .env
   ```

3. **Dockerコンテナの起動**
   ```bash
   docker-compose up -d
   ```

4. **Laravelのセットアップ**
   ```bash
   # Composerの依存関係をインストール
   docker-compose exec app composer install

   # アプリケーションキーの生成
   docker-compose exec app php artisan key:generate

   # データベースのマイグレーション
   docker-compose exec app php artisan migrate

   # ストレージリンクの作成
   docker-compose exec app php artisan storage:link
   ```

5. **Node.jsの依存関係をインストールしてViteを起動**
   ```bash
   # NPMの依存関係をインストール
   docker-compose exec vite npm install

   # Vite開発サーバーを起動（既に起動している場合は不要）
   docker-compose exec vite npm run dev
   ```

### アクセス方法
- **Webアプリケーション**: http://localhost:8000
- **Vite開発サーバー**: http://localhost:5173
- **MySQL**: localhost:3306
- **Redis**: localhost:6379

### よく使うDockerコマンド

```bash
# コンテナの状態確認
docker-compose ps

# コンテナのログを確認
docker-compose logs app

# Artisanコマンドの実行
docker-compose exec app php artisan [command]

# コンテナの停止
docker-compose down

# コンテナの再起動
docker-compose restart

# データベースの初期化（注意：データが削除されます）
docker-compose down -v
docker-compose up -d
```

### トラブルシューティング

1. **ポートの競合エラー**
   - 他のアプリケーションがポートを使用している場合は、`docker-compose.yml`でポート番号を変更してください

2. **権限エラー**
   - Windowsの場合、Dockerのファイル共有設定を確認してください

3. **データベース接続エラー**
   - `.env`ファイルでデータベース設定が正しいか確認してください
