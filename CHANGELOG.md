# Changelog

このプロジェクトの重要な変更はすべてこのファイルに記録されます。

## [Unreleased] - 2026-02-07

### 追加 (Added)

#### 評価・タグシステム
- **星評価機能**: 1-5つ星でレビューを評価
- **タグシステム**: 複数タグでレビューを分類
  - デフォルトタグ: 感動した、笑った、勉強になった、考えさせられた、癒された、面白かった、ためになった
- **フィルタリング**: 評価とタグでレビューをフィルタリング
- **番組平均評価**: 番組ごとの平均評価とレビュー数を表示
- **Reactコンポーネント**: StarRating.jsx, TagSelector.jsx

#### いいね・コメント機能
- **いいね機能**: レビューにいいねを追加・削除
- **コメント機能**: レビューにコメント追加・削除（最大1000文字）
- **通知機能**: いいね・コメント時に投稿者へ通知
- **リアルタイム更新**: いいね数・コメント数のリアルタイム表示
- **Reactコンポーネント**: LikeButton.jsx, CommentSection.jsx

#### 推薦システム
- **パーソナライズ推薦**: お気に入りと高評価レビューに基づく番組推薦
- **トレンド表示**: 直近7日間で高評価の番組を表示
- **人気番組**: 総合的に高評価の番組を表示
- **キャッシュ機構**: Redis使用で高速化（TTL: 1時間）
- **推薦ページ**: /recommendations

### 変更 (Changed)

#### データベース
- `posts`テーブルに`rating`, `station_id`, `likes_count`, `comments_count`カラム追加
- `post_tags`テーブル追加（タグマスター）
- `post_post_tag`テーブル追加（投稿とタグの関連）
- `post_likes`テーブル追加
- `post_comments`テーブル追加

#### モデル・サービス
- `Post`モデル: rating, tags, likes, comments リレーション追加
- `PostTag`, `PostLike`, `PostComment`モデル追加
- `PostService`: フィルタリング、平均評価計算メソッド追加
- `PostInteractionService`: いいね・コメント処理サービス追加
- `RecommendationService`: 推薦アルゴリズム実装

#### コントローラー
- `PostController`: getProgramRating()追加、view()にフィルタリング対応
- `PostInteractionController`: いいね・コメントAPI追加
- `RecommendationController`: 推薦ページとAPI追加
- `MypageController`: 評価・タグ編集対応

#### フロントエンド
- `app.jsx`: 新しいReactコンポーネント登録
- `NotificationCenter.jsx`: いいね・コメント通知対応
- `custom.css`: 新機能用スタイル追加（250行以上）

#### バリデーション
- `ReviewCreateRequest`: rating, tagsバリデーション追加

### テスト (Testing)
- **テスト環境**: Docker + GitHub Actions両対応
- **Feature Tests**: 42テスト作成（21/42成功）
  - PostRatingTest.php
  - PostTagTest.php
  - PostInteractionTest.php
  - RecommendationTest.php
- **テストドキュメント**: TESTING.md作成

### ドキュメント (Documentation)
- `README.md`: 新機能の説明追加
- `TESTING.md`: テスト環境セットアップガイド
- `PHASE8_CHECKLIST.md`: 最終確認チェックリスト
- `CHANGELOG.md`: このファイル
- `.github/workflows/tests.yml.example`: GitHub Actionsワークフローサンプル

### 技術的詳細 (Technical Details)

#### 新しいルート
```
GET  /program/{program_id}/rating    → PostController@getProgramRating
POST /api/posts/like                 → PostInteractionController@like
POST /api/posts/unlike               → PostInteractionController@unlike
POST /api/posts/comment              → PostInteractionController@comment
POST /api/posts/comment/delete       → PostInteractionController@deleteComment
GET  /api/posts/comments             → PostInteractionController@getComments
GET  /api/posts/check-like           → PostInteractionController@checkLike
GET  /recommendations                → RecommendationController@index
GET  /api/recommendations            → RecommendationController@getRecommendations
POST /api/recommendations/refresh    → RecommendationController@refresh
```

#### パフォーマンス最適化
- Eager loading使用（N+1問題対策）
- いいね・コメント数のキャッシュ（postsテーブル内）
- 推薦結果のRedisキャッシュ（TTL: 1時間）
- 適切なインデックス追加

#### セキュリティ
- 認証必須機能: いいね、コメント、推薦
- CSRF保護
- XSS対策（Bladeエスケープ）
- バリデーション強化

---

## [過去のバージョン]

以前の変更履歴については、gitログを参照してください。

```bash
git log --oneline --decorate --graph
```
