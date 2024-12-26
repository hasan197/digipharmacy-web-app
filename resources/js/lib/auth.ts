import axios from 'axios';

interface User {
    id: number;
    name: string;
    email: string;
}

interface AuthResponse {
    status: string;
    message: string;
    user: User;
    token: string;
}

export const auth = {
    async login(email: string, password: string): Promise<AuthResponse> {
        try {
            const response = await axios.post('/api/login', {
                email,
                password,
            }, {
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                }
            });
            
            if (response.data.token) {
                localStorage.setItem('auth', JSON.stringify(response.data));
            }
            
            return response.data;
        } catch (error: any) {
            throw new Error(error.response?.data?.message || 'Login failed');
        }
    },

    async logout(): Promise<void> {
        try {
            const token = this.getToken();
            await axios.post('/api/logout', {}, {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                }
            });
            localStorage.removeItem('auth');
        } catch (error) {
            console.error('Logout error:', error);
            localStorage.removeItem('auth');
        }
    },

    getUser(): User | null {
        const auth = localStorage.getItem('auth');
        if (auth) {
            const { user } = JSON.parse(auth);
            return user;
        }
        return null;
    },

    getToken(): string | null {
        const auth = localStorage.getItem('auth');
        if (auth) {
            const { token } = JSON.parse(auth);
            return token;
        }
        return null;
    },

    isAuthenticated(): boolean {
        return !!this.getToken();
    }
};
