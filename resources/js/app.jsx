/**
 * 最適化されたJavaScript設定
 * 必要最小限のライブラリのみをインポート
 */

// axiosのみをインポート（API通信用）
import axios from 'axios';
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Bootstrapのドロップダウン機能のみをインポート
import { Dropdown } from 'bootstrap';

// React関連
import React from 'react';
import { createRoot } from 'react-dom/client';
import { ToastContainer } from 'react-toastify';
import 'react-toastify/dist/ReactToastify.css';

import ToastComponent from './components/ToastComponent.jsx';

// アプリケーションのメインコンポーネント
const App = () => {
    return (
        <div>
            {/* ToastContainerを追加してトースト通知を表示 */}
            <ToastContainer />
        </div>
    );
};

// DOMが読み込まれた後にReactアプリを初期化
document.addEventListener('DOMContentLoaded', () => {
    // ToastContainerのみを追加（既存のコンテンツを保持）
    const toastContainer = document.createElement('div');
    document.body.appendChild(toastContainer);
    const root = createRoot(toastContainer);
    root.render(<App />);
    
    // Bootstrapのドロップダウンを初期化
    const dropdownElementList = document.querySelectorAll('[data-toggle="dropdown"]');
    [...dropdownElementList].map(dropdownToggleEl => new Dropdown(dropdownToggleEl));
});

// Reactコンポーネントをグローバルに使用可能にする
window.ToastComponent = ToastComponent;
window.React = React;
