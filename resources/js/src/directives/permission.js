import { useAuthStore } from '@/stores/authStore';

const enforcePermission = (el, binding) => {
    const authStore = useAuthStore();
    if (!binding.value) return;

    if (!authStore.hasPermiso(binding.value)) {
        el.style.display = 'none';
    }
};

export default {
    mounted: enforcePermission,
    updated: enforcePermission,
};
