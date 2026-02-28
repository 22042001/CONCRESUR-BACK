import { ref } from 'vue';

export const useCrud = (actions) => {
    const loading = ref(false);
    const error = ref(null);

    const run = async (action, ...args) => {
        loading.value = true;
        error.value = null;
        try {
            return await action(...args);
        } catch (err) {
            error.value = err;
            throw err;
        } finally {
            loading.value = false;
        }
    };

    return {
        loading,
        error,
        fetchAll: (...args) => run(actions.fetchAll, ...args),
        create: (...args) => run(actions.create, ...args),
        update: (...args) => run(actions.update, ...args),
        deactivate: (...args) => run(actions.deactivate, ...args),
    };
};
