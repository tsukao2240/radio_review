# デプロイメントガイド

## ffmpeg セットアップ

このアプリケーションはラジオ録音機能にffmpegが必要です。Windows環境とLinux環境の両方に対応しています。

### 自動セットアップ

アプリケーションには両プラットフォーム用の事前コンパイル済みffmpegバイナリが含まれています：

- **Windows**: `ffmpeg/ffmpeg-master-latest-win64-gpl/bin/ffmpeg.exe`
- **Linux**: `ffmpeg/ffmpeg`

これらのバイナリはアプリケーションによって自動的に検出・使用されます。

### 手動でのffmpegインストール

含まれているバイナリが環境で動作しない場合は、手動でffmpegをインストールできます：

#### Linux (Ubuntu/Debian)
```bash
sudo apt update
sudo apt install ffmpeg
```

#### Linux (CentOS/RHEL)
```bash
sudo yum install epel-release
sudo yum install ffmpeg
```

#### Windows
1. https://ffmpeg.org/download.html からダウンロード
2. `C:\ffmpeg\bin\` に展開
3. PATH環境変数に追加

### Dockerデプロイメント

Dockerデプロイの場合、Dockerfileに以下を追加：

```dockerfile
# ffmpegをインストール
RUN apt-get update && \
    apt-get install -y ffmpeg && \
    rm -rf /var/lib/apt/lists/*
```

### 確認方法

Laravelログを確認することでffmpegのインストールを確認できます。録音開始時に検出されたffmpegパスがログに記録されます。

## 環境設定

以下のディレクトリが書き込み可能であることを確認してください：
- `storage/app/recordings/` - 録音ファイルの保存用
- `storage/logs/` - アプリケーションログ用

## 本番デプロイメントチェックリスト

1. ✅ ffmpegが利用可能（自動または手動インストール）
2. ✅ ストレージディレクトリが書き込み可能
3. ✅ キャッシュとセッション設定が完了
4. ✅ 環境変数が設定済み
5. ✅ データベースマイグレーションが実行済み
6. ✅ アセットがコンパイル済み（`npm run production`）

## トラブルシューティング

### 録音の問題

1. Laravelログを確認：`storage/logs/laravel.log`
2. ログでffmpegパスを確認
3. ストレージディレクトリの権限を確認
4. radiko.jpへのネットワーク接続を確認

### クロスプラットフォーム互換性

アプリケーションは自動的にオペレーティングシステムを検出し、WindowsとLinux環境の両方に適したコマンドとパスを使用します。