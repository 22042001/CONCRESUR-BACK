import api from './axios';

const baseCrud = (basePath) => ({
    list: () => api.get(`/${basePath}`),
    get: (id) => api.get(`/${basePath}/${id}`),
    create: (payload) => api.post(`/${basePath}`, payload),
    update: (id, payload) => api.put(`/${basePath}/${id}`, payload),
});

export const clientesApi = baseCrud('clientes');
export const proveedoresApi = baseCrud('proveedores');
export const personalApi = baseCrud('personal');
export const categoriasCompraApi = baseCrud('categorias-compra');
export const productosApi = baseCrud('productos');
export const variantesApi = baseCrud('variantes-producto');
