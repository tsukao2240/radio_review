/**
 * First we will load all of this project's JavaScript dependencies which
 * includes React and other libraries. It is a great starting point when
 * building robust, powerful web applications using React and Laravel.
 */

import('./bootstrap');

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

// DOM要素が存在する場合のみReactアプリを初期化
const container = document.getElementById('app');
if (container) {
    const root = createRoot(container);
    root.render(<App />);
}

// Reactコンポーネントをグローバルに使用可能にする
window.ToastComponent = ToastComponent;
window.React = React;
