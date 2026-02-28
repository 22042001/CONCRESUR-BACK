import { ref } from 'vue';
import { defineStore } from 'pinia';
import { createToastInterface } from 'vue-toastification';
import {
    categoriasCompraApi,
    clientesApi,
    personalApi,
    productosApi,
    proveedoresApi,
    variantesApi,
} from '@/api/catalogos';

const toast = createToastInterface();

export const useCatalogosStore = defineStore('catalogos', () => {
    const loading = ref(false);
    const clientes = ref([]);
    const proveedores = ref([]);
    const personal = ref([]);
    const categoriasCompra = ref([]);
    const productos = ref([]);
    const variantes = ref([]);

    const execute = async (action, successMessage = null) => {
        loading.value = true;
        try {
            const result = await action();
            if (successMessage) toast.success(successMessage);
            return result;
        } catch (error) {
            toast.error(error?.response?.data?.message || 'Operacion fallida');
            throw error;
        } finally {
            loading.value = false;
        }
    };

    const loadClientes = async () => {
        const { data } = await execute(() => clientesApi.list());
        clientes.value = data.data || [];
    };
    const createCliente = async (payload) => execute(() => clientesApi.create(payload), 'Cliente creado');
    const updateCliente = async (id, payload) => execute(() => clientesApi.update(id, payload), 'Cliente actualizado');

    const loadProveedores = async () => {
        const { data } = await execute(() => proveedoresApi.list());
        proveedores.value = data.data || [];
    };
    const createProveedor = async (payload) => execute(() => proveedoresApi.create(payload), 'Proveedor creado');
    const updateProveedor = async (id, payload) => execute(() => proveedoresApi.update(id, payload), 'Proveedor actualizado');

    const loadPersonal = async () => {
        const { data } = await execute(() => personalApi.list());
        personal.value = data.data || [];
    };
    const createPersonal = async (payload) => execute(() => personalApi.create(payload), 'Personal creado');
    const updatePersonal = async (id, payload) => execute(() => personalApi.update(id, payload), 'Personal actualizado');

    const loadCategoriasCompra = async () => {
        const { data } = await execute(() => categoriasCompraApi.list());
        categoriasCompra.value = data.data || [];
    };
    const createCategoriaCompra = async (payload) => execute(() => categoriasCompraApi.create(payload), 'Categoria creada');
    const updateCategoriaCompra = async (id, payload) => execute(() => categoriasCompraApi.update(id, payload), 'Categoria actualizada');

    const loadProductos = async () => {
        const [productosResp, variantesResp] = await Promise.all([
            execute(() => productosApi.list()),
            execute(() => variantesApi.list()),
        ]);
        productos.value = productosResp.data.data || [];
        variantes.value = variantesResp.data.data || [];
    };
    const createProducto = async (payload) => execute(() => productosApi.create(payload), 'Producto creado');
    const updateProducto = async (id, payload) => execute(() => productosApi.update(id, payload), 'Producto actualizado');
    const createVariante = async (payload) => execute(() => variantesApi.create(payload), 'Variante creada');
    const updateVariante = async (id, payload) => execute(() => variantesApi.update(id, payload), 'Variante actualizada');

    return {
        loading,
        clientes,
        proveedores,
        personal,
        categoriasCompra,
        productos,
        variantes,
        loadClientes,
        createCliente,
        updateCliente,
        loadProveedores,
        createProveedor,
        updateProveedor,
        loadPersonal,
        createPersonal,
        updatePersonal,
        loadCategoriasCompra,
        createCategoriaCompra,
        updateCategoriaCompra,
        loadProductos,
        createProducto,
        updateProducto,
        createVariante,
        updateVariante,
    };
});
