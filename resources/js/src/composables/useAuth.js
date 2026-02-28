import { computed } from 'vue';
import { useAuthStore } from '@/stores/authStore';

export const useAuth = () => {
    const authStore = useAuthStore();

    return {
        user: computed(() => authStore.user),
        isAuthenticated: computed(() => authStore.isAuthenticated),
        hasPermiso: authStore.hasPermiso,
        login: authStore.login,
        logout: authStore.logout,
    };
};
