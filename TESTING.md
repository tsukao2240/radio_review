# テスト環境セットアップガイド

## 環境別の設定

このプロジェクトは以下の2つの環境でテストを実行できます：

### 1. Docker開発環境（推奨）

Docker Composeを使用した開発環境でテストを実行する場合。

**セットアップ:**
```bash
# テスト用データベースを作成（初回のみ）
docker-compose exec db mysql -uroot -proot -e "CREATE DATABASE IF NOT EXISTS radio_review_test; GRANT ALL PRIVILEGES ON radio_review_test.* TO 'laravel'@'%'; FLUSH PRIVILEGES;"

# テスト実行
docker-compose exec app php artisan test
```

**設定内容（.env.testing）:**
- `DB_HOST=db` （Docker Composeのサービス名）
- `DB_USERNAME=laravel`
- `DB_PASSWORD=password`
- `REDIS_HOST=redis`

### 2. GitHub Actions CI

GitHub Actionsではワークフローで環境変数を上書きします。

**設定方法:**
`.github/workflows/tests.yml.example` を参考にしてください。

主なポイント：
- `.env.testing` をコピー
- 環境変数で `DB_HOST=127.0.0.1`、`DB_USERNAME=root`、`DB_PASSWORD=root` を上書き
- MySQL/Redisをservice containerとして起動

**GitHub Actions設定例:**
```yaml
steps:
  - name: Override environment variables for GitHub Actions
    run: |
      echo "DB_HOST=127.0.0.1" >> .env
      echo "DB_USERNAME=root" >> .env
      echo "DB_PASSWORD=root" >> .env
      echo "REDIS_HOST=127.0.0.1" >> .env

  - name: Run tests
    run: php artisan test
    env:
      DB_HOST: 127.0.0.1
      DB_USERNAME: root
      DB_PASSWORD: root
```

## ファイル構成

- **`.env.testing`** - デフォルト設定（GitHub Actions向け）。Gitにコミットされる。
- **`.env.testing.local`** - 環境固有の設定（Docker/ローカル向け）。`.gitignore`に含まれる。
- **`.env.testing.local.example`** - Docker環境用のサンプル設定。

## 優先順位

Laravel 11は以下の優先順位で環境変数を読み込みます：

1. `.env.testing.local`（存在する場合）
2. `.env.testing`
3. `.env`

これにより、各開発者が自分の環境に合わせて設定をカスタマイズできます。

## よくある問題

### データベース接続エラー

```
SQLSTATE[HY000] [2002] Connection refused
```

**原因:** `DB_HOST`の設定が環境と一致していない。

**解決策:**
- Docker環境の場合: `.env.testing.local`で`DB_HOST=db`に設定
- ローカル環境の場合: `.env.testing.local`で`DB_HOST=127.0.0.1`に設定

### 権限エラー

```
Access denied for user 'laravel'@'%' to database 'radio_review_test'
```

**解決策:**
```bash
docker-compose exec db mysql -uroot -proot -e "GRANT ALL PRIVILEGES ON radio_review_test.* TO 'laravel'@'%'; FLUSH PRIVILEGES;"
```

## テストコマンド

```bash
# 全テスト実行
docker-compose exec app php artisan test

# 特定のテストスイート
docker-compose exec app php artisan test --testsuite=Feature
docker-compose exec app php artisan test --testsuite=Unit

# 特定のテストファイル
docker-compose exec app php artisan test --filter=PostRatingTest

# 特定のテストメソッド
docker-compose exec app php artisan test --filter=PostRatingTest::user_can_create_review_with_rating

# 並列実行（高速化）
docker-compose exec app php artisan test --parallel

# カバレッジ（要Xdebug）
docker-compose exec app php artisan test --coverage
```

## 新しい機能のテスト

Phase 7で以下のテストが追加されました：

1. **PostRatingTest** - 評価機能（1-5つ星）
2. **PostTagTest** - タグ機能（複数選択可）
3. **PostInteractionTest** - いいね・コメント機能
4. **RecommendationTest** - 推薦システム

```bash
# 新機能のテストのみ実行
docker-compose exec app php artisan test tests/Feature/PostRatingTest.php
docker-compose exec app php artisan test tests/Feature/PostTagTest.php
docker-compose exec app php artisan test tests/Feature/PostInteractionTest.php
docker-compose exec app php artisan test tests/Feature/RecommendationTest.php
```
