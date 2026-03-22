/**
 * タイムフリー録音処理の共通モジュール
 *
 * 複数の画面（お気に入り、週間番組表、2週間番組表、番組詳細）で
 * 共通して使用される録音関連の処理を提供します。
 */

import { toast } from 'react-toastify';

// 進行中の録音を管理するMap
window.activeRecordings = window.activeRecordings || new Map();

/**
 * 録音監視を開始
 * @param {string} recordingId - 録音ID
 * @param {HTMLElement} button - 録音ボタン要素
 * @param {string} filename - ファイル名
 * @param {HTMLElement} statusDiv - 進行状況表示エリア
 * @param {number} durationMinutes - 録音時間（分）
 */
export function startRecordingMonitor(recordingId, button, filename, statusDiv, durationMinutes) {
    const startTime = Date.now();
    const totalSeconds = durationMinutes * 60;

    window.activeRecordings.set(recordingId, {
        button: button,
        statusDiv: statusDiv,
        filename: filename,
        startTime: startTime,
        totalSeconds: totalSeconds
    });

    // 総録音時間を表示
    const totalTimeSpan = statusDiv.querySelector('.total-time');
    if (totalTimeSpan) {
        totalTimeSpan.textContent = formatTime(totalSeconds);
    }

    // 即座に最初のチェックを実行
    checkRecordingStatus(recordingId, button, filename, statusDiv, null, startTime, totalSeconds);

    // 500ms間隔で状態をチェック（高速ダウンロード対応）
    const intervalId = setInterval(() => {
        checkRecordingStatus(recordingId, button, filename, statusDiv, intervalId, startTime, totalSeconds);
    }, 500);

    window.activeRecordings.get(recordingId).intervalId = intervalId;
}

/**
 * 録音状態をチェック
 * @param {string} recordingId - 録音ID
 * @param {HTMLElement} button - 録音ボタン要素
 * @param {string} filename - ファイル名
 * @param {HTMLElement} statusDiv - 進行状況表示エリア
 * @param {number|null} intervalId - インターバルID
 * @param {number} startTime - 開始時刻（ミリ秒）
 * @param {number} totalSeconds - 総録音時間（秒）
 */
export function checkRecordingStatus(recordingId, button, filename, statusDiv, intervalId, startTime, totalSeconds) {
    // 既に完了済みの録音はチェックしない
    if (!window.activeRecordings.has(recordingId)) {
        return;
    }

    fetch(window.recordingStatusUrl + '?' + new URLSearchParams({
        recording_id: recordingId
    }))
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // 既に完了済みならスキップ
            if (!window.activeRecordings.has(recordingId)) {
                return;
            }

            // 経過時間を表示（整数に変換）
            const elapsedSeconds = Math.floor(data.elapsed_seconds || Math.floor((Date.now() - startTime) / 1000));
            const elapsedTimeSpan = statusDiv.querySelector('.elapsed-time');
            if (elapsedTimeSpan) {
                elapsedTimeSpan.textContent = formatTime(elapsedSeconds);
            }

            // 進捗率を表示（サーバーからの値を優先）
            const progress = data.progress_percentage !== undefined ? data.progress_percentage : 0;
            const progressBar = statusDiv.querySelector('.progress-bar');
            if (progressBar) {
                const progressInt = Math.floor(progress);
                progressBar.style.width = progressInt + '%';
                progressBar.textContent = progressInt + '%';
                progressBar.setAttribute('aria-valuenow', progressInt);
            }

            // ファイルサイズを表示
            if (data.file_size !== undefined && data.file_size > 0) {
                const fileSizeSpan = statusDiv.querySelector('.file-size');
                if (fileSizeSpan) {
                    fileSizeSpan.textContent = data.file_size_formatted || formatFileSize(data.file_size);
                }
            }

            // 録音完了判定
            if (data.status === 'completed' || (data.file_exists && !data.is_recording)) {
                // 録音情報を削除（重複実行防止）
                const recording = window.activeRecordings.get(recordingId);
                window.activeRecordings.delete(recordingId);

                // タイマーを停止
                if (recording && recording.intervalId) {
                    clearInterval(recording.intervalId);
                }

                // 進行状況表示を非表示
                statusDiv.style.display = 'none';

                // ボタンとラッパーを確実に取得して再表示
                const controlsWrapper = statusDiv.parentElement;

                // recording-btn-wrapper を探す
                let btnWrapper = controlsWrapper ? controlsWrapper.querySelector('.recording-btn-wrapper') : null;

                // ボタンを再取得（元のbutton参照が失われている可能性があるため）
                let recordingButton = button;
                if (btnWrapper) {
                    const foundButton = btnWrapper.querySelector('.recording-btn, .timefree-btn');
                    if (foundButton) {
                        recordingButton = foundButton;
                    }
                }

                // ラッパーを再表示
                if (btnWrapper) {
                    btnWrapper.style.display = 'flex';
                    btnWrapper.style.visibility = 'visible';
                }

                // ボタンをダウンロードボタンに変更
                if (recordingButton) {
                    recordingButton.textContent = 'ダウンロード';
                    recordingButton.classList.remove('btn-success', 'btn-warning');
                    recordingButton.classList.add('btn-primary');
                    recordingButton.disabled = false;
                    recordingButton.style.display = 'inline-block';

                    // クリックイベントを設定
                    recordingButton.onclick = function(e) {
                        e.preventDefault();
                        downloadRecording(recordingId, filename);
                    };
                }

                // 目立つ完了ポップアップを表示
                showRecordingCompletePopup(filename, recordingId);

                // ブラウザ通知も表示
                showBrowserNotification('録音完了', filename + ' の録音が完了しました');
            }
        }
    })
    .catch(error => {
        // エラー表示
        if (window.activeRecordings.has(recordingId)) {
            const recording = window.activeRecordings.get(recordingId);
            if (recording && recording.statusDiv) {
                const recordingInfo = recording.statusDiv.querySelector('.recording-info');
                if (recordingInfo) {
                    recordingInfo.innerHTML = '<span style="color: red;">エラー: 状態取得失敗</span>';
                }
            }
        }
    });
}

/**
 * 録音を停止
 * @param {string} recordingId - 録音ID
 * @param {HTMLElement} button - 録音ボタン要素
 * @param {HTMLElement} statusDiv - 進行状況表示エリア
 */
export function stopRecording(recordingId, button, statusDiv) {
    if (!confirm('録音を停止しますか？')) {
        return;
    }

    fetch(window.recordingStopUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            recording_id: recordingId
        })
    })
    .then(response => {
        // レスポンスのステータスコードをチェック
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        // Content-Typeを確認
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            throw new Error('サーバーから無効なレスポンスが返されました');
        }
        
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // 監視を停止
            const recording = window.activeRecordings.get(recordingId);
            if (recording && recording.intervalId) {
                clearInterval(recording.intervalId);
            }
            window.activeRecordings.delete(recordingId);

            // 進行状況表示を非表示
            statusDiv.style.display = 'none';

            // ボタンラッパーを再表示
            const btnWrapper = button.closest('.recording-btn-wrapper');
            if (btnWrapper) {
                btnWrapper.style.display = 'flex';
            }

            // ボタンを元に戻す
            button.textContent = 'タイムフリー録音';
            button.classList.remove('btn-warning', 'btn-primary');
            button.classList.add('btn-success');
            button.disabled = false;

            toast.success('録音を停止しました');
        } else {
            toast.error('録音停止に失敗しました: ' + (data.message || '不明なエラー'));
        }
    })
    .catch(error => {
        console.error('録音停止エラー:', error);
        toast.error('エラーが発生しました: ' + error.message);
    });
}

/**
 * 録音ファイルをダウンロード
 * @param {string} recordingId - 録音ID
 * @param {string} filename - ファイル名（オプション）
 */
export async function downloadRecording(recordingId, filename) {
    try {
        // ダウンロードURLを構築
        const downloadUrl = window.recordingDownloadUrl + '?' + new URLSearchParams({
            recording_id: recordingId
        });

        // ファイルを取得
        const response = await fetch(downloadUrl);
        if (!response.ok) {
            throw new Error('ダウンロードに失敗しました');
        }

        const blob = await response.blob();
        const finalFilename = filename || getFilenameFromRecordingId(recordingId);

        // File System Access APIをサポートしているかチェック
        if ('showSaveFilePicker' in window) {
            try {
                const fileHandle = await window.showSaveFilePicker({
                    suggestedName: finalFilename,
                    startIn: 'music',
                    types: [{
                        description: '音声ファイル',
                        accept: {
                            'audio/mp4': ['.m4a'],
                            'audio/mpeg': ['.mp3']
                        }
                    }]
                });

                const writable = await fileHandle.createWritable();
                await writable.write(blob);
                await writable.close();

                return;
            } catch (e) {
                if (e.name !== 'AbortError') {
                    console.error('Save picker error:', e);
                }
                // ユーザーがキャンセルした場合はそのまま終了
                return;
            }
        }

        // File System Access API未対応の場合のフォールバック
        downloadWithCustomName(blob, finalFilename);

    } catch (error) {
        console.error('ダウンロードエラー:', error);
        toast.error('ダウンロードに失敗しました: ' + error.message);
    }
}

/**
 * カスタム名でダウンロード（フォールバック用）
 * @param {Blob} blob - ダウンロードするBlob
 * @param {string} filename - ファイル名
 */
function downloadWithCustomName(blob, filename) {
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.style.display = 'none';
    a.href = url;
    a.download = filename;

    document.body.appendChild(a);
    a.click();

    window.URL.revokeObjectURL(url);
    document.body.removeChild(a);
}

/**
 * 録音IDからファイル名を取得
 * @param {string} recordingId - 録音ID
 * @returns {string} ファイル名
 */
export function getFilenameFromRecordingId(recordingId) {
    const parts = recordingId.split('_');
    if (parts.length >= 3) {
        const station = parts[0];
        const datetime = parts[1];

        const year = datetime.substring(0, 4);
        const month = datetime.substring(4, 6);
        const day = datetime.substring(6, 8);
        const hour = datetime.substring(8, 10);
        const minute = datetime.substring(10, 12);

        return `${station}_${year}${month}${day}_${hour}${minute}.m4a`;
    }
    return recordingId + '.m4a';
}

/**
 * 時間をフォーマット（秒 -> MM:SS）
 * @param {number} seconds - 秒数
 * @returns {string} フォーマットされた時間
 */
export function formatTime(seconds) {
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return String(mins).padStart(2, '0') + ':' + String(secs).padStart(2, '0');
}

/**
 * ファイルサイズをフォーマット
 * @param {number} bytes - バイト数
 * @returns {string} フォーマットされたファイルサイズ
 */
export function formatFileSize(bytes) {
    if (bytes < 1024) {
        return bytes + ' B';
    } else if (bytes < 1024 * 1024) {
        return (bytes / 1024).toFixed(0) + ' KB';
    } else {
        return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
    }
}

/**
 * ブラウザ通知を表示
 * @param {string} title - 通知タイトル
 * @param {string} body - 通知本文
 */
export function showBrowserNotification(title, body) {
    if ('Notification' in window && Notification.permission === 'granted') {
        try {
            const notification = new Notification(title, {
                body: body,
                icon: '/favicon.ico',
                badge: '/favicon.ico',
                tag: 'recording-complete',
                requireInteraction: false,
                silent: false
            });

            // 通知をクリックした時の動作
            notification.onclick = function() {
                window.focus();
                this.close();
            };

            // 5秒後に自動で閉じる
            setTimeout(() => {
                notification.close();
            }, 5000);
        } catch (error) {
            console.error('通知の表示に失敗しました:', error);
        }
    }
}

/**
 * 保存成功ポップアップ表示
 * @param {string} filename - ファイル名
 */
function showSaveSuccessPopup(filename) {
    const popup = document.createElement('div');
    popup.style.cssText = `
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: white;
        border: 2px solid #28a745;
        border-radius: 10px;
        padding: 25px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        z-index: 10000;
        text-align: center;
        min-width: 350px;
    `;

    popup.innerHTML = `
        <div style="font-size: 24px; margin-bottom: 15px;">💾✅</div>
        <h4 style="color: #28a745; margin-bottom: 10px;">保存完了！</h4>
        <p style="margin-bottom: 15px; word-break: break-all;">
            <strong>${filename}</strong><br>
            が選択した場所に保存されました
        </p>
        <button id="closeSuccessPopup" class="btn btn-success">
            OK
        </button>
    `;

    document.body.appendChild(popup);

    // OKボタンのイベント
    document.getElementById('closeSuccessPopup').onclick = function() {
        document.body.removeChild(popup);
    };

    // 5秒後に自動で閉じる
    setTimeout(() => {
        if (document.body.contains(popup)) {
            document.body.removeChild(popup);
        }
    }, 5000);
}

/**
 * 録音完了ポップアップを表示
 * @param {string} filename - ファイル名
 * @param {string} recordingId - 録音ID
 */
function showRecordingCompletePopup(filename, recordingId) {
    const popup = document.createElement('div');
    popup.style.cssText = `
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 8px 32px rgba(0,0,0,0.4);
        z-index: 10002;
        text-align: center;
        min-width: 400px;
        animation: popupSlideIn 0.3s ease-out;
    `;

    popup.innerHTML = `
        <style>
            @keyframes popupSlideIn {
                from {
                    transform: translate(-50%, -60%);
                    opacity: 0;
                }
                to {
                    transform: translate(-50%, -50%);
                    opacity: 1;
                }
            }
        </style>
        <div style="font-size: 48px; margin-bottom: 15px;">🎉</div>
        <h3 style="color: white; margin-bottom: 15px; font-weight: bold;">録音完了！</h3>
        <p style="margin-bottom: 20px; font-size: 16px; word-break: break-all; line-height: 1.6;">
            <strong style="background: rgba(255,255,255,0.2); padding: 5px 10px; border-radius: 5px;">${filename}</strong><br>
            <span style="font-size: 14px; opacity: 0.9; margin-top: 10px; display: block;">の録音が完了しました</span>
        </p>
        <button id="closeRecordingPopup" class="btn btn-light" style="font-weight: bold; padding: 10px 30px;">
            ダウンロードボタンを確認
        </button>
    `;

    document.body.appendChild(popup);

    // OKボタンのイベント
    document.getElementById('closeRecordingPopup').onclick = function() {
        document.body.removeChild(popup);
    };

    // 8秒後に自動で閉じる
    setTimeout(() => {
        if (document.body.contains(popup)) {
            document.body.removeChild(popup);
        }
    }, 8000);
}

/**
 * ブラウザダウンロードの案内表示
 */
function showBrowserDownloadInfo() {
    const info = document.createElement('div');
    info.style.cssText = `
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: #007bff;
        color: white;
        padding: 15px 20px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        z-index: 10001;
        max-width: 400px;
    `;

    info.innerHTML = `
        <div style="font-weight: bold; margin-bottom: 8px;">💾 ダウンロード完了</div>
        <div style="font-size: 14px; line-height: 1.4;">
            <strong>推奨保存場所:</strong><br>
            D:\\ミュージック\\radio フォルダ<br>
            <small style="opacity: 0.8;">ブラウザ設定から変更できます</small>
        </div>
    `;

    document.body.appendChild(info);

    // クリックで閉じる
    info.onclick = function() {
        if (document.body.contains(info)) {
            document.body.removeChild(info);
        }
    };

    // 10秒後に自動で消す
    setTimeout(() => {
        if (document.body.contains(info)) {
            document.body.removeChild(info);
        }
    }, 10000);
}

// グローバルに関数を公開（Bladeテンプレートから直接使用できるように）
window.startRecordingMonitor = startRecordingMonitor;
window.checkRecordingStatus = checkRecordingStatus;
window.stopRecording = stopRecording;
window.downloadRecording = downloadRecording;
window.getFilenameFromRecordingId = getFilenameFromRecordingId;
window.formatTime = formatTime;
window.formatFileSize = formatFileSize;
window.showBrowserNotification = showBrowserNotification;
window.showRecordingCompletePopup = showRecordingCompletePopup;
