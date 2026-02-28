import axios from 'axios';
import { createToastInterface } from 'vue-toastification';

const toast = createToastInterface();

const api = axios.create({
    baseURL: import.meta.env.VITE_API_BASE_URL || '/api/v1',
    headers: {
        Accept: 'application/json',
    },
});

api.interceptors.request.use((config) => {
    const token = localStorage.getItem('concresur_token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

api.interceptors.response.use(
    (response) => response,
    (error) => {
        const status = error?.response?.status;

        if (status === 401) {
            localStorage.removeItem('concresur_token');
            localStorage.removeItem('concresur_user');
            localStorage.removeItem('concresur_permisos');
            if (!window.location.pathname.startsWith('/login')) {
                window.location.href = '/login';
            }
        }

        if (status === 403) {
            toast.error('Sin permisos para esta accion');
        }

        if (status === 500) {
            toast.error('Error interno del servidor');
        }

        return Promise.reject(error);
    },
);

export const getApiErrors = (error) => error?.response?.data?.errors ?? {};

export default api;
