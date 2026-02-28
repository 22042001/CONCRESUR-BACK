import { ref } from 'vue';
import { defineStore } from 'pinia';
import { createToastInterface } from 'vue-toastification';
import { comprasApi, cotizacionesApi, logisticaApi, ordenesApi, ventasApi } from '@/api/operaciones';

const toast = createToastInterface();

export const useOperacionesStore = defineStore('operaciones', () => {
    const loading = ref(false);
    const cotizaciones = ref([]);
    const ventas = ref([]);
    const compras = ref([]);
    const ordenes = ref([]);
    const kanban = ref({ en_espera: [], en_camino: [], entregado: [] });

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

    const loadCotizaciones = async () => {
        const { data } = await execute(() => cotizacionesApi.list());
        cotizaciones.value = data.data || [];
    };
    const createCotizacion = async (payload) => execute(() => cotizacionesApi.create(payload), 'Cotizacion creada');
    const confirmarCotizacion = async (id, payload) => execute(() => cotizacionesApi.confirmar(id, payload), 'Cotizacion confirmada');

    const loadVentas = async () => {
        const { data } = await execute(() => ventasApi.list());
        ventas.value = data.data || [];
    };
    const createVenta = async (payload) => execute(() => ventasApi.create(payload), 'Venta creada');
    const createAbonoVenta = async (id, payload) => execute(() => ventasApi.abonar(id, payload), 'Abono registrado');

    const loadCompras = async () => {
        const { data } = await execute(() => comprasApi.list());
        compras.value = data.data || [];
    };
    const createCompra = async (payload) => execute(() => comprasApi.create(payload), 'Compra creada');
    const createAbonoCompra = async (id, payload) => execute(() => comprasApi.abonar(id, payload), 'Pago registrado');

    const loadOrdenes = async () => {
        const { data } = await execute(() => ordenesApi.list());
        ordenes.value = data.data || [];
    };
    const createOrden = async (payload) => execute(() => ordenesApi.create(payload), 'Orden creada');
    const createRegistro = async (id, payload) => execute(() => ordenesApi.registrar(id, payload), 'Registro guardado');

    const loadKanban = async () => {
        const { data } = await execute(() => logisticaApi.kanban());
        kanban.value = data.data || { en_espera: [], en_camino: [], entregado: [] };
    };
    const moveKanbanState = async (id, payload) => execute(() => logisticaApi.move(id, payload), 'Estado actualizado');

    return {
        loading,
        cotizaciones,
        ventas,
        compras,
        ordenes,
        kanban,
        loadCotizaciones,
        createCotizacion,
        confirmarCotizacion,
        loadVentas,
        createVenta,
        createAbonoVenta,
        loadCompras,
        createCompra,
        createAbonoCompra,
        loadOrdenes,
        createOrden,
        createRegistro,
        loadKanban,
        moveKanbanState,
    };
});
