# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## 動作指針 (Filesystem First)
回答を生成する前に、必ず以下のステップを遵守してください。これにより、不要な推測を排除し、トークン消費を最小限に抑えます。

1. **ファイル構造の確認**: プロジェクトの構造が不明な場合は、まず `ls -R` や `find` を使用してディレクトリ構成を確認してください。
2. **情報の直接取得**: コードの内容や関数定義について推測で答えず、`grep` や `read_file` を使用して、必ず実際のソースコードを確認してください。
3. **最小限のコンテキスト抽出**: ファイル全体を読み込むのではなく、必要な範囲のみを特定して読み込むように努めてください。
4. **推測の排除**: 実装の詳細が不明な段階でコードを提案しないでください。まずfilesystemから事実を確認し、確証を得てから1回で正確な修正案を提示してください。

## コミット前のルール

**バックエンド（PHP/Laravel）のコードを修正した場合、コミットする前に必ず全テストを実行してパスすることを確認してください。**

```bash
docker-compose exec app php artisan test
```

テストが全件パスしてからコミットすること。フロントエンドのみの変更（Blade/JS/CSS）はPHPテスト不要。

## フロントエンドE2Eテスト（Playwright MCP）

`tests/e2e/` にシナリオファイル（YAML）を配置。Claude CodeがPlaywright MCPツールを使ってブラウザを操作して実行する。

- シナリオ一覧・実行方法は `tests/e2e/README.md` を参照
- フロントエンド（Blade/JS/CSS）を修正した場合は、該当シナリオを実行して動作確認してからコミットすること
- 実行前提: `docker-compose up -d` でコンテナが起動済み、`http://localhost:8000` でアクセス可能

## プロジェクト概要

ラジオ番組の番組表表示・感想投稿・タイムフリー録音機能を持つWebアプリケーション。Radiko APIと連携し、番組スケジュール取得や過去放送の録音が可能。

**URL:** http://radio-review.com/

## 技術スタック

- **バックエンド:** PHP 8.2+ / Laravel 11
- **フロントエンド:** React 18 + Bladeテンプレート、Bootstrap 5、Vite 6
- **データベース:** MySQL 8.0
- **キャッシュ/セッション:** Redis
- **インフラ:** Docker Compose（Nginx, PHP-FPM, MySQL, Redis, Vite開発サーバー）
- **音声処理:** FFmpeg（`ffmpeg/`にバイナリ同梱）

## 開発コマンド

### Docker環境

```bash
docker-compose up -d                          # 全コンテナ起動
docker-compose exec app php artisan migrate   # マイグレーション実行
docker-compose exec app composer install      # PHP依存関係インストール
docker-compose exec vite npm install          # JS依存関係インストール
docker-compose down                           # コンテナ停止
```

### ビルド・開発サーバー

```bash
npm run dev       # Vite開発サーバー（HMR: localhost:5173）
npm run build     # 本番ビルド
```

### テスト

```bash
# 全テスト実行
docker-compose exec app php artisan test

# テストスイート指定
docker-compose exec app php artisan test --testsuite=Unit
docker-compose exec app php artisan test --testsuite=Feature

# 単一テストファイル実行
docker-compose exec app php artisan test --filter=RadioRecordingTest

# PHPUnit直接実行
docker-compose exec app ./vendor/bin/phpunit tests/Feature/RadioRecordingTest.php
```

テストは`radio_review_test`データベースを使用し、キャッシュ・セッションは`array`ドライバー（`phpunit.xml`で設定）。

### コード品質

```bash
docker-compose exec app ./vendor/bin/pint   # Laravel Pint（コードスタイル修正）
```

## アーキテクチャ

### バックエンド（Laravel）

Laravel規約に従い、サービスレイヤーを採用。

- **コントローラー** (`app/Http/Controllers/`)
  - `RadioRecordingController` — タイムフリー録音（最大のコントローラー、約960行）：並列HLSダウンロード、進捗追跡、ファイル管理
  - `RadioBroadcastController` — 週間/2週間番組表（Radiko API経由）
  - `RadioProgramController` — 放送中番組一覧
  - `FavoriteProgramController` — お気に入り番組CRUD（認証必須）
  - `RecordingScheduleController` — 録音予約（認証必須）
  - `NotificationController` — 通知管理（認証必須）
  - `PostController` / `MypageController` — 感想投稿・管理

- **サービス** (`app/Services/`)
  - `RadikoApiService` — Radiko API連携、XML解析（DOMDocument/XPath）、スケジュール30分キャッシュ
  - `RadioProgramSearchService` — 番組検索クエリ
  - `PostService` — 感想管理
  - `NotificationService` — 通知処理
  - `RecordingCleanupService` — 録音ストレージクリーンアップ

- **ミドルウェア** (`app/Http/Middleware/`) — ログインレート制限(`RateLimitLogin`)、レスポンス圧縮(`CompressResponse`)、セキュリティヘッダー(`SecurityHeaders`)、パフォーマンス監視(`PerformanceMonitoring`)等のカスタムミドルウェアあり

- **ルーティング** (`routes/web.php`) — 全ルートが1ファイル。録音ルートは公開、お気に入り・予約・通知・マイページは認証必須、感想投稿はメール認証必須

### フロントエンド

Bladeテンプレートによるサーバーサイドレンダリングに、Reactコンポーネントを部分的にマウントするハイブリッド構成。

- **Bladeテンプレート** (`resources/views/`) — 機能別ディレクトリ（home, auth, recording, favorite, radioprogram, mypage, notifications, post）
- **Reactコンポーネント** (`resources/js/components/`) — ToastComponent、NotificationCenter等のインタラクティブUI
- **Viteエントリポイント:** `resources/css/app.css`, `resources/css/custom.css`, `resources/js/app.jsx`
- **PWA:** Service Worker (`public/sw.js`)、マニフェスト (`public/manifest.json`)、オフラインフォールバック

### データベース

主要モデル: `User`, `Post`（感想）, `FavoriteProgram`, `RecordingSchedule`, `Notification`, `RadioProgram`（APIキャッシュデータ）。マイグレーションは`database/migrations/`に配置。

### 外部API連携

`RadikoApiService`でRadiko APIと連携:
- 放送局ごとの週間XML番組表を取得
- Base64エンコードキーによる独自認証
- Radiko互換性のためSSL検証無効
- Redisキャッシュ（スケジュール: 30分TTL、録音情報: 2時間TTL）

### 録音システム

タイムフリー録音はHLSセグメントを並列ダウンロード（デフォルト10並列）し、FFmpegでエンコード。主要環境変数: `RECORDING_STORAGE_PATH`, `RECORDING_MAX_PARALLEL`, `RECORDING_CHUNK_DELAY`。録音ファイルは`storage/app/recordings/`に保存。

## アクセスポイント（Docker）

| サービス | URL |
|---------|-----|
| Webアプリ | http://localhost:8000 |
| Vite HMR | http://localhost:5173 |
| MySQL | localhost:3306 |
| Redis | localhost:6379 |

## 関連ドキュメント

- `API_SPEC.md` — APIエンドポイント仕様（リクエスト/レスポンス例付き）
- `DEPLOYMENT.md` — 本番デプロイガイド・FFmpegセットアップ
- `HANDOVER_RECORDING_FEATURE.md` — 録音機能の実装詳細
- `PWA_README.md` — PWA実装ガイド
