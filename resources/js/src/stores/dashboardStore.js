import { ref } from 'vue';
import { defineStore } from 'pinia';
import { ventasApi, logisticaApi } from '@/api/operaciones';
import { variantesApi } from '@/api/catalogos';

export const useDashboardStore = defineStore('dashboard', () => {
    const loading = ref(false);
    const metrics = ref({
        ventasPendientes: 0,
        stockBajo: 0,
        pedidosCamino: 0,
        comprasPendientes: 0,
    });

    const loadMetrics = async () => {
        loading.value = true;
        try {
            const [ventasResp, variantesResp, kanbanResp] = await Promise.all([
                ventasApi.list(),
                variantesApi.list(),
                logisticaApi.kanban(),
            ]);

            const ventas = ventasResp?.data?.data || [];
            const variantes = variantesResp?.data?.data || [];
            const kanban = kanbanResp?.data?.data || {};

            metrics.value = {
                ventasPendientes: ventas.filter((venta) => venta.estado_financiero === 'Pendiente').length,
                stockBajo: variantes.filter((item) => Number(item.stock_actual) <= Number(item.stock_minimo)).length,
                pedidosCamino: (kanban.en_camino || []).length,
                comprasPendientes: 0,
            };
        } finally {
            loading.value = false;
        }
    };

    return { loading, metrics, loadMetrics };
});
