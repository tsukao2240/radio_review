# E2Eテスト（Playwright MCP）

Playwright MCPを使ったフロントエンドE2Eテスト。
Claude CodeがPlaywright MCPツールを使ってブラウザを操作し、シナリオファイルに従ってテストを実行します。

## 前提条件

- Dockerコンテナが起動済み（`docker-compose up -d`）
- アプリが `http://localhost:8000` でアクセス可能

## 実行方法

Claude Codeに以下のように依頼する：

```
tests/e2e/ のシナリオを実行してください
```

または個別に：

```
tests/e2e/01_home.yaml のシナリオを実行してください
```

## シナリオ一覧

| ファイル | 対象画面 | 認証 |
|---------|---------|------|
| 01_home.yaml | ホーム画面 | 不要 |
| 02_auth.yaml | ログイン・ログアウト | - |
| 03_schedule.yaml | 放送中番組一覧 | 不要 |
| 04_search.yaml | 番組検索 | 不要 |
| 05_timefree.yaml | タイムフリー番組表 | 不要 |
| 06_favorites.yaml | お気に入り管理 | 必要 |
| 07_recording_history.yaml | 録音履歴 | 不要 |
| 08_review.yaml | 感想投稿・一覧 | 必要 |

## シナリオ形式

各YAMLファイルは以下の形式で記述：

```yaml
name: テスト名
base_url: http://localhost:8000
steps:
  - action: navigate
    url: /path
  - action: assert_visible
    selector: "セレクタ or テキスト"
  - action: click
    selector: "セレクタ"
  - action: fill
    selector: "セレクタ"
    value: "入力値"
  - action: assert_text
    selector: "セレクタ"
    expected: "期待するテキスト"
  - action: screenshot
    name: "スクリーンショット名"
```

## テスト用アカウント

`.env` の `TEST_USER_EMAIL` / `TEST_USER_PASSWORD` を参照。
未設定の場合はテスト実行前に手動でユーザーを作成してください：

```bash
docker-compose exec app php artisan tinker
>>> App\Models\User::factory()->create(['email' => 'test@example.com', 'password' => bcrypt('password'), 'email_verified_at' => now()])
```
