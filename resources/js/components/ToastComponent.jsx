import React, { useEffect } from 'react';
import { toast } from 'react-toastify';

const ToastComponent = ({ message, type = 'default' }) => {
    useEffect(() => {
        const toastOptions = {
            position: "bottom-center",
            autoClose: 3000,
            hideProgressBar: false,
            closeOnClick: true,
            pauseOnHover: true,
            draggable: true,
        };

        switch (type) {
            case 'success':
                toast.success(message, toastOptions);
                break;
            case 'error':
                toast.error(message, toastOptions);
                break;
            case 'warning':
                toast.warn(message, toastOptions);
                break;
            case 'info':
                toast.info(message, toastOptions);
                break;
            default:
                toast(message, toastOptions);
        }
    }, [message, type]);

    return null; // このコンポーネントは何もレンダリングしない
};

export default ToastComponent;
