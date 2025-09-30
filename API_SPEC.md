# Radio Review API仕様書

## タイムフリー録音API

### 1. 録音開始

**エンドポイント:** `POST /recording/timefree/start`

**説明:** radikoのタイムフリー録音を開始します。

**リクエスト:**
```json
{
  "station_id": "TBS",
  "title": "番組タイトル",
  "start_time": "202509301200",
  "end_time": "202509301230"
}
```

**パラメータ:**
| 名前 | 型 | 必須 | 説明 |
|-----|-----|------|-----|
| station_id | string | ✓ | 放送局ID (例: TBS, YFM) |
| title | string | ✓ | 番組タイトル |
| start_time | string | ✓ | 開始時刻 (YYYYMMDDHHmm形式) |
| end_time | string | ✓ | 終了時刻 (YYYYMMDDHHmm形式) |

**レスポンス:**
```json
{
  "success": true,
  "message": "タイムフリー録音を開始しました",
  "recording_id": "TBS_202509301200_20250930163000",
  "filename": "TBS_202509301200_202509301230_20250930163000.m4a"
}
```

**エラーレスポンス:**
```json
{
  "success": false,
  "message": "エラーメッセージ"
}
```

---

### 2. 録音状態確認

**エンドポイント:** `GET /recording/status`

**説明:** 録音の進捗状況を取得します。

**クエリパラメータ:**
| 名前 | 型 | 必須 | 説明 |
|-----|-----|------|-----|
| recording_id | string | ✓ | 録音ID |

**レスポンス:**
```json
{
  "success": true,
  "status": "recording",
  "file_exists": true,
  "file_size": 1048576,
  "file_size_formatted": "1.0 MB",
  "elapsed_seconds": 30,
  "elapsed_time_formatted": "0:30",
  "planned_duration_minutes": 30,
  "progress_percentage": 50,
  "is_recording": true,
  "recording_info": {
    "station_id": "TBS",
    "title": "番組タイトル",
    "filename": "TBS_202509301200_202509301230_20250930163000.m4a",
    "filepath": "/path/to/file.m4a",
    "start_time": "202509301200",
    "end_time": "202509301230",
    "created_at": "2025-09-30T16:30:00.000000Z",
    "status": "recording"
  }
}
```

**ステータス:**
- `recording`: 録音中
- `completed`: 録音完了

---

### 3. 録音停止

**エンドポイント:** `POST /recording/stop`

**説明:** 進行中の録音を停止します。

**リクエスト:**
```json
{
  "recording_id": "TBS_202509301200_20250930163000"
}
```

**レスポンス:**
```json
{
  "success": true,
  "message": "録音を停止しました"
}
```

---

### 4. 録音一覧取得

**エンドポイント:** `GET /recording/list`

**説明:** すべての録音情報を取得します（JSON形式）。

**レスポンス:**
```json
{
  "success": true,
  "recordings": [
    {
      "station_id": "TBS",
      "title": "番組タイトル",
      "filename": "TBS_202509301200_202509301230_20250930163000.m4a",
      "filepath": "/path/to/file.m4a",
      "start_time": "202509301200",
      "end_time": "202509301230",
      "created_at": "2025-09-30T16:30:00.000000Z",
      "status": "completed"
    }
  ]
}
```

---

### 5. 録音履歴画面表示

**エンドポイント:** `GET /recording/history`

**説明:** 録音履歴をブラウザで表示します（HTML形式）。

**レスポンス:** HTML画面

**表示内容:**
- 録音ファイル一覧（放送局、番組名、録音日時、ファイルサイズ、状態）
- ダウンロードボタン
- 削除ボタン
- ディスク使用状況

---

### 6. ファイルダウンロード

**エンドポイント:** `GET /recording/download`

**説明:** 録音済みファイルをダウンロードします。

**クエリパラメータ:**
| 名前 | 型 | 必須 | 説明 |
|-----|-----|------|-----|
| recording_id | string | ✓ | 録音ID |

**レスポンス:** ファイルストリーム（audio/mp4）

---

### 7. 録音ファイル削除

**エンドポイント:** `POST /recording/delete`

**説明:** 録音ファイルとキャッシュ情報を削除します。

**リクエスト:**
```json
{
  "recording_id": "TBS_202509301200_20250930163000"
}
```

**レスポンス:**
```json
{
  "success": true,
  "message": "録音ファイルを削除しました"
}
```

---

## 技術仕様

### 認証
現在のバージョンでは認証なしで利用可能です。将来的にはユーザー認証を実装予定です。

### レート制限
radikoサーバーへの負荷を考慮し、以下の制限があります：
- 並列ダウンロード数: 10
- チャンク間待機時間: 0.2秒
- 接続タイムアウト: 30秒

### エラーコード
| コード | 説明 |
|-------|-----|
| 200 | 成功 |
| 400 | パラメータ不正 |
| 404 | ファイル/録音情報が見つからない |
| 500 | サーバーエラー |

### キャッシュ
録音情報はRedisキャッシュに2時間保存されます。
キャッシュキー形式: `recording_{recording_id}`

---

## 使用例

### JavaScript (Fetch API)

```javascript
// 録音開始
const startRecording = async () => {
  const response = await fetch('/recording/timefree/start', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({
      station_id: 'TBS',
      title: 'テスト番組',
      start_time: '202509301200',
      end_time: '202509301230'
    })
  });

  const data = await response.json();
  console.log(data.recording_id);
};

// 進捗確認
const checkProgress = async (recordingId) => {
  const response = await fetch(`/recording/status?recording_id=${recordingId}`);
  const data = await response.json();
  console.log(`進捗: ${data.progress_percentage}%`);
};

// ダウンロード
const downloadRecording = (recordingId) => {
  window.location.href = `/recording/download?recording_id=${recordingId}`;
};
```

### cURL

```bash
# 録音開始
curl -X POST https://radio-review.com/recording/timefree/start \
  -H "Content-Type: application/json" \
  -d '{
    "station_id": "TBS",
    "title": "テスト番組",
    "start_time": "202509301200",
    "end_time": "202509301230"
  }'

# 進捗確認
curl "https://radio-review.com/recording/status?recording_id=TBS_202509301200_20250930163000"

# ダウンロード
curl -O "https://radio-review.com/recording/download?recording_id=TBS_202509301200_20250930163000"
```

---

## 更新履歴

| バージョン | 日付 | 変更内容 |
|----------|------|---------|
| 1.0.0 | 2025-09-30 | 初版リリース |