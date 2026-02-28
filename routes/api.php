<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoriaCompraController;
use App\Http\Controllers\Api\ClienteController;
use App\Http\Controllers\Api\CompraController;
use App\Http\Controllers\Api\CotizacionController;
use App\Http\Controllers\Api\LogisticaController;
use App\Http\Controllers\Api\OrdenProduccionController;
use App\Http\Controllers\Api\PersonalController;
use App\Http\Controllers\Api\ProductoController;
use App\Http\Controllers\Api\ProveedorController;
use App\Http\Controllers\Api\VarianteProductoController;
use App\Http\Controllers\Api\VentaController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function (): void {
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/me', [AuthController::class, 'me'])->middleware('auth.jwt');
});

Route::get('/seguridad/protegida', function () {
    return response()->json([
        'message' => 'Acceso autorizado.',
    ]);
})->middleware(['auth.jwt', 'permiso:seguridad.base']);

Route::prefix('cotizaciones')->group(function (): void {
    Route::get('/', [CotizacionController::class, 'index'])
        ->middleware(['auth.jwt', 'permiso:ventas.cotizaciones.ver']);
    Route::post('/', [CotizacionController::class, 'store'])
        ->middleware(['auth.jwt', 'permiso:ventas.cotizaciones.crear']);
    Route::get('/{cotizacion}', [CotizacionController::class, 'show'])
        ->middleware(['auth.jwt', 'permiso:ventas.cotizaciones.ver']);
    Route::post('/{cotizacion}/confirmar', [CotizacionController::class, 'confirmar'])
        ->middleware(['auth.jwt', 'permiso:ventas.ventas.confirmar']);
});

Route::prefix('ventas')->group(function (): void {
    Route::get('/', [VentaController::class, 'index'])
        ->middleware(['auth.jwt', 'permiso:ventas.ventas.ver']);
    Route::get('/{venta}', [VentaController::class, 'show'])
        ->middleware(['auth.jwt', 'permiso:ventas.ventas.ver']);
    Route::post('/directa', [VentaController::class, 'storeDirecta'])
        ->middleware(['auth.jwt', 'permiso:ventas.ventas.crear']);
    Route::post('/{venta}/abonos', [VentaController::class, 'storeAbono'])
        ->middleware(['auth.jwt', 'permiso:ventas.ventas.abonar']);
});

Route::prefix('compras')->group(function (): void {
    Route::get('/', [CompraController::class, 'index'])
        ->middleware(['auth.jwt', 'permiso:compras.compras.ver']);
    Route::post('/', [CompraController::class, 'store'])
        ->middleware(['auth.jwt', 'permiso:compras.compras.crear']);
    Route::get('/{compra}', [CompraController::class, 'show'])
        ->middleware(['auth.jwt', 'permiso:compras.compras.ver']);
    Route::post('/{compra}/abonos', [CompraController::class, 'storeAbono'])
        ->middleware(['auth.jwt', 'permiso:compras.compras.abonar']);
});

Route::prefix('ordenes-produccion')->group(function (): void {
    Route::get('/', [OrdenProduccionController::class, 'index'])
        ->middleware(['auth.jwt', 'permiso:produccion.ordenes.ver']);
    Route::post('/', [OrdenProduccionController::class, 'store'])
        ->middleware(['auth.jwt', 'permiso:produccion.ordenes.crear']);
    Route::get('/{ordenProduccion}', [OrdenProduccionController::class, 'show'])
        ->middleware(['auth.jwt', 'permiso:produccion.ordenes.ver']);
    Route::post('/{ordenProduccion}/registros', [OrdenProduccionController::class, 'storeRegistro'])
        ->middleware(['auth.jwt', 'permiso:produccion.registros.crear']);
});

Route::prefix('logistica')->group(function (): void {
    Route::get('/kanban', [LogisticaController::class, 'kanban'])
        ->middleware(['auth.jwt', 'permiso:logistica.kanban.ver']);
    Route::get('/pedidos', [LogisticaController::class, 'index'])
        ->middleware(['auth.jwt', 'permiso:logistica.kanban.ver']);
    Route::get('/pedidos/{pedidoLogistico}', [LogisticaController::class, 'show'])
        ->middleware(['auth.jwt', 'permiso:logistica.kanban.ver']);
    Route::put('/pedidos/{pedidoLogistico}/estado', [LogisticaController::class, 'updateEstado'])
        ->middleware(['auth.jwt', 'permiso:logistica.kanban.mover']);
});

Route::prefix('clientes')->group(function (): void {
    Route::get('/', [ClienteController::class, 'index'])
        ->middleware(['auth.jwt', 'permiso:catalogos.clientes.ver']);
    Route::post('/', [ClienteController::class, 'store'])
        ->middleware(['auth.jwt', 'permiso:catalogos.clientes.crear']);
    Route::get('/{cliente}', [ClienteController::class, 'show'])
        ->middleware(['auth.jwt', 'permiso:catalogos.clientes.ver']);
    Route::put('/{cliente}', [ClienteController::class, 'update'])
        ->middleware(['auth.jwt', 'permiso:catalogos.clientes.crear']);
    Route::delete('/{cliente}', [ClienteController::class, 'destroy'])
        ->middleware(['auth.jwt', 'permiso:catalogos.clientes.eliminar']);
});

Route::prefix('proveedores')->group(function (): void {
    Route::get('/', [ProveedorController::class, 'index'])
        ->middleware(['auth.jwt', 'permiso:catalogos.proveedores.ver']);
    Route::post('/', [ProveedorController::class, 'store'])
        ->middleware(['auth.jwt', 'permiso:catalogos.proveedores.crear']);
    Route::get('/{proveedor}', [ProveedorController::class, 'show'])
        ->middleware(['auth.jwt', 'permiso:catalogos.proveedores.ver']);
    Route::put('/{proveedor}', [ProveedorController::class, 'update'])
        ->middleware(['auth.jwt', 'permiso:catalogos.proveedores.crear']);
    Route::delete('/{proveedor}', [ProveedorController::class, 'destroy'])
        ->middleware(['auth.jwt', 'permiso:catalogos.proveedores.eliminar']);
});

Route::prefix('personal')->group(function (): void {
    Route::get('/', [PersonalController::class, 'index'])
        ->middleware(['auth.jwt', 'permiso:catalogos.personal.ver']);
    Route::post('/', [PersonalController::class, 'store'])
        ->middleware(['auth.jwt', 'permiso:catalogos.personal.crear']);
    Route::get('/{personal}', [PersonalController::class, 'show'])
        ->middleware(['auth.jwt', 'permiso:catalogos.personal.ver']);
    Route::put('/{personal}', [PersonalController::class, 'update'])
        ->middleware(['auth.jwt', 'permiso:catalogos.personal.crear']);
    Route::delete('/{personal}', [PersonalController::class, 'destroy'])
        ->middleware(['auth.jwt', 'permiso:catalogos.personal.eliminar']);
});

Route::prefix('categorias-compra')->group(function (): void {
    Route::get('/', [CategoriaCompraController::class, 'index'])
        ->middleware(['auth.jwt', 'permiso:catalogos.categorias_compra.ver']);
    Route::post('/', [CategoriaCompraController::class, 'store'])
        ->middleware(['auth.jwt', 'permiso:catalogos.categorias_compra.crear']);
    Route::get('/{categoriaCompra}', [CategoriaCompraController::class, 'show'])
        ->middleware(['auth.jwt', 'permiso:catalogos.categorias_compra.ver']);
    Route::put('/{categoriaCompra}', [CategoriaCompraController::class, 'update'])
        ->middleware(['auth.jwt', 'permiso:catalogos.categorias_compra.crear']);
});

Route::prefix('productos')->group(function (): void {
    Route::get('/', [ProductoController::class, 'index'])
        ->middleware(['auth.jwt', 'permiso:catalogos.productos.ver']);
    Route::post('/', [ProductoController::class, 'store'])
        ->middleware(['auth.jwt', 'permiso:catalogos.productos.crear']);
    Route::get('/{producto}', [ProductoController::class, 'show'])
        ->middleware(['auth.jwt', 'permiso:catalogos.productos.ver']);
    Route::put('/{producto}', [ProductoController::class, 'update'])
        ->middleware(['auth.jwt', 'permiso:catalogos.productos.crear']);
    Route::delete('/{producto}', [ProductoController::class, 'destroy'])
        ->middleware(['auth.jwt', 'permiso:catalogos.productos.eliminar']);
});

Route::prefix('variantes-producto')->group(function (): void {
    Route::get('/', [VarianteProductoController::class, 'index'])
        ->middleware(['auth.jwt', 'permiso:catalogos.variantes.ver']);
    Route::post('/', [VarianteProductoController::class, 'store'])
        ->middleware(['auth.jwt', 'permiso:catalogos.variantes.crear']);
    Route::get('/{varianteProducto}', [VarianteProductoController::class, 'show'])
        ->middleware(['auth.jwt', 'permiso:catalogos.variantes.ver']);
    Route::put('/{varianteProducto}', [VarianteProductoController::class, 'update'])
        ->middleware(['auth.jwt', 'permiso:catalogos.variantes.crear']);
    Route::delete('/{varianteProducto}', [VarianteProductoController::class, 'destroy'])
        ->middleware(['auth.jwt', 'permiso:catalogos.variantes.eliminar']);
});
