import api from './axios';

export const cotizacionesApi = {
    list: () => api.get('/cotizaciones'),
    get: (id) => api.get(`/cotizaciones/${id}`),
    create: (payload) => api.post('/cotizaciones', payload),
    confirmar: (id, payload) => api.post(`/cotizaciones/${id}/confirmar`, payload),
};

export const ventasApi = {
    list: () => api.get('/ventas'),
    get: (id) => api.get(`/ventas/${id}`),
    create: (payload) => api.post('/ventas/directa', payload),
    abonar: (id, payload) => api.post(`/ventas/${id}/abonos`, payload),
};

export const comprasApi = {
    list: () => api.get('/compras'),
    get: (id) => api.get(`/compras/${id}`),
    create: (payload) => api.post('/compras', payload),
    abonar: (id, payload) => api.post(`/compras/${id}/abonos`, payload),
};

export const ordenesApi = {
    list: () => api.get('/ordenes-produccion'),
    get: (id) => api.get(`/ordenes-produccion/${id}`),
    create: (payload) => api.post('/ordenes-produccion', payload),
    registrar: (id, payload) => api.post(`/ordenes-produccion/${id}/registros`, payload),
};

export const logisticaApi = {
    kanban: () => api.get('/logistica/kanban'),
    move: (id, payload) => api.put(`/logistica/pedidos/${id}/estado`, payload),
};
