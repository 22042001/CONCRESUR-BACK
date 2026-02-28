# CONCRESUR Backend (Laravel)

Backend API para ventas, compras, produccion, logistica y catalogos de CONCRESUR.

## Requisitos

- PHP 8.2+
- Composer
- MySQL o PostgreSQL (segun `.env`)
- Node.js (solo si deseas compilar assets web)

## Instalacion rapida

1. Copia variables de entorno:

```bash
cp .env.example .env
```

2. Configura la conexion DB en `.env`.

3. Genera llave de app y ejecuta migraciones + seed:

```bash
php artisan key:generate
php artisan migrate --seed
```

4. Levanta servidor:

```bash
php artisan serve
```

Base URL API: `http://127.0.0.1:8000/api/v1`

## Auth y credenciales de prueba

Password para todos los usuarios seed: `password`

- `admin@concresur.com` (Administrador)
- `ventas@concresur.com` (Ventas)
- `compras@concresur.com` (Compras)
- `produccion@concresur.com` (Produccion)
- `logistica@concresur.com` (Logistica)

Login:

`POST /api/v1/auth/login`

Perfil:

`GET /api/v1/auth/me`

`/auth/me` devuelve: `id`, `nombre`, `email`, `rol`, `permisos[]`.

## Contrato API v1

### Respuesta exitosa

```json
{
  "message": "Texto descriptivo",
  "data": {}
}
```

### Respuesta de listado paginado

```json
{
  "message": "Texto descriptivo",
  "data": [],
  "meta": {
    "current_page": 1,
    "last_page": 3,
    "per_page": 15,
    "total": 42
  }
}
```

### Respuesta de error API

```json
{
  "message": "Descripcion del error",
  "data": null,
  "errors": {}
}
```

## Filtros y paginacion

Listados principales soportan:

- `per_page` (default 15, max 100)
- `q` (busqueda)
- Filtros por modulo (ejemplos):
  - `ventas`: `estado_operativo`, `estado_financiero`, `cliente_id`
  - `cotizaciones`: `estado`, `cliente_id`
  - `compras`: `estado_financiero`, `categoria_id`, `proveedor_id`
  - `ordenes-produccion`: `estado`, `venta_id`, `variante_id`
  - `logistica/pedidos`: `estado`

## Seguridad y permisos

Las rutas protegidas usan:

- middleware `auth.jwt`
- middleware `permiso:<clave>`

La matriz real de roles/permisos se define en `database/seeders/DatabaseSeeder.php`.

## Eliminacion logica en catalogos

Se agregaron endpoints `DELETE` para desactivar (`activo=false`):

- `DELETE /api/v1/clientes/{cliente}`
- `DELETE /api/v1/proveedores/{proveedor}`
- `DELETE /api/v1/personal/{personal}`
- `DELETE /api/v1/productos/{producto}`
- `DELETE /api/v1/variantes-producto/{varianteProducto}`

## Postman

Colecciones disponibles en `postman/`:

- `concresur-iteracion-1.postman_collection.json`
- `concresur-iteracion-2.postman_collection.json`
- `concresur-iteracion-3.postman_collection.json`
- `concresur-iteracion-4.postman_collection.json`
- `concresur-catalogos.postman_collection.json`
- `concresur-catalogos-put.postman_collection.json`
- `concresur-matriz-roles.postman_collection.json`
- `concresur-filtros-paginacion.postman_collection.json`

## Testing

Ejecutar suite:

```bash
php artisan test
```

La suite cubre:

- flujos de ventas/compras/produccion/logistica
- matriz de roles
- seguridad 401/403
- contrato API y paginacion/filtros
