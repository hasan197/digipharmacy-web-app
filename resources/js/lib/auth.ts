import axios from 'axios';
import { showSessionExpiredModal } from '../utils/modal';

// Buat instance axios dengan konfigurasi default
export const api = axios.create({
    baseURL: '/api',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    }
});

// Fungsi untuk menset token JWT di axios instances
const setJwtToken = (token: string | null) => {
    if (token) {
        api.defaults.headers.common['Authorization'] = `Bearer ${token}`;
        axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
    } else {
        delete api.defaults.headers.common['Authorization'];
        delete axios.defaults.headers.common['Authorization'];
    }
};

// Tambahkan interceptor request untuk menghandle method conversion dan auth
api.interceptors.request.use((config) => {
    const method = config.method?.toUpperCase();
    if (['PUT', 'PATCH', 'DELETE'].includes(method || '')) {
        const originalMethod = config.method;
        config.method = 'post';
        config.data = {
            ...config.data,
            _method: originalMethod?.toUpperCase(),
        };
    }
    
    try {
        // Ambil data auth dari localStorage
        const authData = localStorage.getItem('auth');
        if (authData) {
            const { access_token } = JSON.parse(authData);
            if (access_token) {
                // Set token JWT di headers
                setJwtToken(access_token);
            } else {
                throw new Error('No access token in auth data');
            }
        }
    } catch (error) {
        console.error('Error setting auth header:', error);
        // Hapus semua data auth jika ada error
        localStorage.removeItem('auth');
        setJwtToken(null);
    }
    
    return config;
});

// Tambahkan interceptor response untuk menghandle error auth
api.interceptors.response.use(
    (response) => response,
    async (error) => {
        if (error.response?.status === 401) {
            // Hapus semua data auth jika sudah expired
            localStorage.removeItem('auth');
            
            // Hapus header Authorization
            delete axios.defaults.headers.common['Authorization'];
            
            // Tampilkan modal session expired
            showSessionExpiredModal();
        }
        return Promise.reject(error);
    }
);

interface Role {
    id: number;
    name: string;
}

interface User {
    id: number;
    name: string;
    email: string;
    roles: Role[];
}

interface AuthResponse {
    access_token: string;
    token_type: string;
    expires_in: number;
    user: User;
}

export const auth = {
    async login(email: string, password: string): Promise<AuthResponse> {
        try {
            // Coba login
            const response = await api.post<AuthResponse>('/auth/login', {
                email,
                password,
            });
            
            // Simpan data auth ke localStorage
            localStorage.setItem('auth', JSON.stringify(response.data));
            
            // Set token JWT di headers
            setJwtToken(response.data.access_token);
            
            // Kembalikan data auth
            return response.data;

        } catch (error: any) {
            console.error('Login error:', error);
            // Hapus data auth jika ada error
            localStorage.removeItem('auth');
            setJwtToken(null);

            // Handle berbagai kasus error
            if (error.response?.status === 401) {
                throw new Error('Invalid credentials');
            } else if (error.response?.data?.message) {
                throw new Error(error.response.data.message);
            } else if (error.message) {
                throw new Error(error.message);
            } else {
                throw new Error('Login failed. Please try again.');
            }
        }
    },

    async logout(): Promise<void> {
        try {
            // Coba logout
            const authData = localStorage.getItem('auth');
            if (authData) {
                await api.post('/auth/logout');
            }
        } catch (error) {
            console.error('Logout error:', error);
        } finally {
            // Hapus data auth
            localStorage.removeItem('auth');
            setJwtToken(null);
        }
    },

    getUser(): User | null {
        // Ambil data user dari localStorage
        const auth = localStorage.getItem('auth');
        if (auth) {
            const { user } = JSON.parse(auth);
            return user;
        }
        return null;
    },

    getToken(): string | null {
        // Ambil token JWT dari localStorage
        const auth = localStorage.getItem('auth');
        if (auth) {
            const { access_token } = JSON.parse(auth);
            return access_token;
        }
        return null;
    },

    isAuthenticated(): boolean {
        // Periksa apakah ada data auth di localStorage
        const auth = localStorage.getItem('auth');
        if (!auth) return false;

        try {
            // Ambil data user dari localStorage
            const { access_token } = JSON.parse(auth);
            if (!access_token) return false;

            // Set token JWT di headers jika auth ada
            setJwtToken(access_token);
            return true;
        } catch (error) {
            console.error('Error checking authentication:', error);
            return false;
        }
    }
};
