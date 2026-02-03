# PWA（Progressive Web App）実装ガイド

## 概要
このアプリケーションはPWA（Progressive Web App）に対応しており、以下の機能を提供します：

- **オフライン対応**: ネットワーク接続がなくてもアプリが動作
- **ホーム画面へのインストール**: ネイティブアプリのようにインストール可能
- **プッシュ通知**: リアルタイム通知機能（将来の拡張用）
- **高速読み込み**: キャッシュによる高速なページ表示
- **アプリライクな体験**: フルスクリーン表示とネイティブアプリのようなUI

## 実装内容

### 1. ファイル構成

```
public/
├── manifest.json          # PWAマニフェストファイル
├── sw.js                  # Service Worker
├── offline.html           # オフライン時の表示ページ
└── images/
    └── icons/            # PWAアイコン（各サイズ）
        ├── icon-72x72.png
        ├── icon-96x96.png
        ├── icon-128x128.png
        ├── icon-144x144.png
        ├── icon-152x152.png
        ├── icon-192x192.png
        ├── icon-384x384.png
        └── icon-512x512.png

resources/
├── js/
│   ├── pwa.js            # PWA登録・管理スクリプト
│   └── generate-icons.js # アイコン生成スクリプト
└── views/
    └── layouts/
        └── header.blade.php  # PWAメタタグ追加済み
```

### 2. 主要機能

#### manifest.json
- アプリ名、説明、アイコン、表示モードなどを定義
- ホーム画面へのインストールに必要な情報を提供
- ショートカット機能（放送中の番組、録音履歴、レビュー投稿）

#### Service Worker (sw.js)
- **キャッシング戦略**: ネットワーク優先、フォールバックでキャッシュ使用
- **オフライン対応**: ネットワーク切断時は専用ページを表示
- **自動更新**: 古いキャッシュを自動削除
- **プッシュ通知対応**: 将来の通知機能拡張用

#### PWA登録スクリプト (pwa.js)
- Service Workerの自動登録
- アプリインストールプロンプト
- 更新通知の表示
- オンライン/オフライン状態の監視

## セットアップ手順

### 1. アイコン生成

PWAアイコンを生成するには、以下のコマンドを実行します：

```bash
# Dockerコンテナ内で実行
docker exec -it radio_review_app bash

# 依存パッケージをインストール
npm install

# アイコンを生成
npm run generate-icons
```

このスクリプトは `public/favicon.ico` から各サイズのPNGアイコンを自動生成します。

### 2. ビルド

フロントエンドをビルドしてPWA機能を有効化します：

```bash
# 開発モード
npm run dev

# 本番ビルド
npm run build
```

### 3. HTTPS設定

PWAを正しく動作させるには、HTTPS接続が必要です（localhostを除く）。

**本番環境では必ずHTTPSを使用してください。**

## 動作確認

### 1. Service Workerの登録確認

ブラウザの開発者ツールを開いて確認：

1. Chrome DevTools を開く（F12）
2. **Application** タブを選択
3. 左メニューから **Service Workers** を選択
4. `/sw.js` が登録されていることを確認

### 2. マニフェストの確認

1. Chrome DevTools の **Application** タブ
2. 左メニューから **Manifest** を選択
3. アプリ名、アイコン、表示モードなどが正しく表示されることを確認

### 3. インストール可能性の確認

1. Chrome DevTools の **Application** タブ
2. 左メニューから **Manifest** を選択
3. **"Add to Home Screen"** ボタンが表示されることを確認
4. またはアドレスバーにインストールアイコンが表示される

### 4. オフライン動作の確認

1. Chrome DevTools の **Network** タブ
2. **Offline** にチェックを入れる
3. ページを再読み込み
4. オフラインページが表示されることを確認

### 5. キャッシュの確認

1. Chrome DevTools の **Application** タブ
2. 左メニューから **Cache Storage** を選択
3. `radio-review-v1.0.0` キャッシュが存在することを確認

## ユーザー向け機能

### ホーム画面へのインストール

#### Android / Windows
1. 右下に「アプリをインストール」ボタンが表示されます
2. ボタンをクリックしてインストール
3. または、ブラウザメニューから「ホーム画面に追加」を選択

#### iOS (Safari)
1. 共有ボタンをタップ
2. 「ホーム画面に追加」を選択
3. 追加ボタンをタップ

### ショートカット機能

ホーム画面のアプリアイコンを長押しすると、以下のショートカットが利用可能：
- 放送中の番組
- 録音履歴
- レビュー投稿

### オフライン利用

- 一度訪問したページはオフラインでも閲覧可能
- ネットワーク切断時は専用のオフラインページが表示
- オンラインに復帰すると自動的に通常表示に戻る

## カスタマイズ

### アプリ名や色の変更

`public/manifest.json` を編集：

```json
{
  "name": "あなたのアプリ名",
  "short_name": "短縮名",
  "theme_color": "#あなたの色",
  "background_color": "#あなたの色"
}
```

### キャッシュバージョンの更新

`public/sw.js` の `CACHE_VERSION` を変更：

```javascript
const CACHE_VERSION = 'v1.0.1'; // バージョンを上げる
```

変更後、ユーザーには更新通知が表示されます。

### キャッシュする静的リソースの追加

`public/sw.js` の `STATIC_CACHE_URLS` に追加：

```javascript
const STATIC_CACHE_URLS = [
  '/',
  '/css/app.css',
  '/js/app.js',
  '/your-new-resource.js' // 追加
];
```

## トラブルシューティング

### Service Workerが登録されない

1. HTTPSを使用しているか確認（localhost以外）
2. ブラウザのコンソールでエラーを確認
3. Service Workerを手動で登録解除して再登録：
   - Chrome DevTools > Application > Service Workers
   - Unregister をクリック
   - ページをリロード

### キャッシュが更新されない

1. Service Workerのバージョンを更新（`CACHE_VERSION`を変更）
2. または、開発者ツールから手動でキャッシュをクリア：
   - Chrome DevTools > Application > Cache Storage
   - 右クリック > Delete

### インストールボタンが表示されない

以下の条件を満たしているか確認：
- HTTPS接続（localhost以外）
- manifest.jsonが正しく読み込まれている
- Service Workerが登録されている
- すでにインストール済みではない

### アイコンが表示されない

1. `public/images/icons/` ディレクトリにアイコンが存在するか確認
2. `npm run generate-icons` でアイコンを生成
3. ブラウザのキャッシュをクリアして再読み込み

## パフォーマンス最適化

### キャッシュ戦略の選択

現在は「ネットワーク優先」戦略を使用していますが、用途に応じて変更可能：

- **ネットワーク優先**: 常に最新データを取得（現在の設定）
- **キャッシュ優先**: 高速表示を優先
- **Stale-While-Revalidate**: 表示はキャッシュ、バックグラウンドで更新

### 静的リソースの事前キャッシュ

重要な静的リソースは `STATIC_CACHE_URLS` に追加して事前キャッシュします。

## セキュリティ

### HTTPS必須

本番環境では必ずHTTPSを使用してください。HTTPでは：
- Service Workerが動作しない
- インストールができない
- プッシュ通知が使えない

### コンテンツセキュリティポリシー

必要に応じて、`manifest.json` にCSPを設定できます。

## 今後の拡張

### プッシュ通知

現在、Service Workerにはプッシュ通知の基本実装が含まれています。
将来的に以下の機能を追加できます：

- 新しいレビューの通知
- お気に入り番組の放送開始通知
- 録音完了通知

### バックグラウンド同期

オフライン時に作成したデータを、オンラインに復帰した際に自動同期する機能。

### 定期的バックグラウンド同期

定期的にコンテンツを更新する機能（Periodic Background Sync API）。

## 参考リンク

- [MDN - Progressive web apps](https://developer.mozilla.org/ja/docs/Web/Progressive_web_apps)
- [Google - Progressive Web Apps](https://web.dev/progressive-web-apps/)
- [Service Worker API](https://developer.mozilla.org/ja/docs/Web/API/Service_Worker_API)
- [Web App Manifest](https://developer.mozilla.org/ja/docs/Web/Manifest)

## ライセンス

このPWA実装は、アプリケーション本体と同じライセンスに従います。
