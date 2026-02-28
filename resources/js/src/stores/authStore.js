import { computed, ref } from 'vue';
import { defineStore } from 'pinia';
import { createToastInterface } from 'vue-toastification';
import { loginRequest, logoutRequest, meRequest } from '@/api/auth';

const toast = createToastInterface();

export const useAuthStore = defineStore('auth', () => {
    const token = ref(localStorage.getItem('concresur_token'));
    const user = ref(JSON.parse(localStorage.getItem('concresur_user') || 'null'));
    const permisos = ref(JSON.parse(localStorage.getItem('concresur_permisos') || '[]'));

    const isAuthenticated = computed(() => Boolean(token.value));

    const setSession = (newToken, newUser, newPermisos = []) => {
        token.value = newToken;
        user.value = newUser;
        permisos.value = newPermisos;
        localStorage.setItem('concresur_token', newToken || '');
        localStorage.setItem('concresur_user', JSON.stringify(newUser || null));
        localStorage.setItem('concresur_permisos', JSON.stringify(newPermisos));
    };

    const clearSession = () => {
        token.value = null;
        user.value = null;
        permisos.value = [];
        localStorage.removeItem('concresur_token');
        localStorage.removeItem('concresur_user');
        localStorage.removeItem('concresur_permisos');
    };

    const hasPermiso = (clave) => {
        if (!clave) return true;
        if (!Array.isArray(permisos.value) || permisos.value.length === 0) return true;
        return permisos.value.includes(clave);
    };

    const login = async (credentials) => {
        const { data } = await loginRequest(credentials);
        setSession(data.access_token, null, []);
        await initAuth();
        toast.success('Sesion iniciada');
    };

    const logout = async () => {
        try {
            await logoutRequest();
        } catch {
            // endpoint optional
        } finally {
            clearSession();
            window.location.href = '/login';
        }
    };

    const initAuth = async () => {
        if (!token.value) return;
        try {
            const { data } = await meRequest();
            const profile = data?.data ?? null;
            const backendPermisos = profile?.permisos ?? profile?.permissions ?? [];
            setSession(token.value, profile, backendPermisos);
        } catch {
            clearSession();
        }
    };

    return {
        token,
        user,
        permisos,
        isAuthenticated,
        hasPermiso,
        login,
        logout,
        initAuth,
        clearSession,
    };
});
