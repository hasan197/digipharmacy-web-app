import React from 'react';
import './bootstrap';
import '../css/app.css';

import { createRoot } from 'react-dom/client';
import App from './components/App';
import axios from 'axios';

const container = document.getElementById('root');
if (container) {
    const root = createRoot(container);
    root.render(
        <React.StrictMode>
            <App />
        </React.StrictMode>
    );
}

axios.interceptors.response.use(
    response => response,
    error => {
        if (error.response.status === 419) { // Status 419 biasanya menunjukkan CSRF token mismatch
            // Menonaktifkan UI sebelum reload
            const overlay = document.createElement('div');
            overlay.id = 'loading-overlay';
            overlay.style.position = 'fixed';
            overlay.style.top = '0';
            overlay.style.left = '0';
            overlay.style.width = '100%';
            overlay.style.height = '100%';
            overlay.style.backgroundColor = 'rgba(0, 0, 0, 0.7)';
            overlay.style.zIndex = '9999';
            overlay.innerHTML = '<div style="position:absolute; top:50%; left:50%; transform: translate(-50%, -50%); color:white;">Memuat ulang...</div>';
            document.body.appendChild(overlay);
            // Reload halaman secara otomatis
            window.location.reload();
        }
        return Promise.reject(error);
    }
);