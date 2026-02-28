<?php

namespace Tests\Feature;

use App\Models\CategoriaCompra;
use App\Models\Cliente;
use App\Models\Personal;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\VarianteProducto;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Iteracion3FlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_compra_y_abonos_actualizan_estado_financiero(): void
    {
        $headers = $this->authHeadersWithPermisos([
            'compras.compras.crear',
            'compras.compras.abonar',
        ]);

        $categoria = CategoriaCompra::query()->create([
            'nombre' => 'Materia Prima',
        ]);

        $proveedor = Proveedor::query()->create([
            'nombre' => 'Proveedor Norte',
            'activo' => true,
        ]);

        $responseCompra = $this->withHeaders($headers)->postJson('/api/v1/compras', [
            'categoria_id' => $categoria->id,
            'proveedor_id' => $proveedor->id,
            'metodo_pago' => 'Credito',
            'forma_pago' => 'Efectivo',
            'detalles' => [
                [
                    'descripcion' => 'Cemento',
                    'cantidad' => 2,
                    'unidad_medida' => 'bolsa',
                    'precio_unitario' => 50,
                ],
            ],
        ]);

        $responseCompra->assertCreated();
        $compraId = $responseCompra->json('data.id');

        $this->assertDatabaseHas('compra', [
            'id' => $compraId,
            'numero' => 'CMP-0001',
            'importe_total' => 100,
            'adelanto' => 0,
            'saldo_total' => 100,
            'estado_financiero' => 'Pendiente',
        ]);

        $this->withHeaders($headers)->postJson("/api/v1/compras/{$compraId}/abonos", [
            'monto' => 40,
            'forma_pago' => 'QR',
        ])->assertCreated();

        $this->assertDatabaseHas('compra', [
            'id' => $compraId,
            'adelanto' => 40,
            'saldo_total' => 60,
            'estado_financiero' => 'Pendiente',
        ]);

        $this->withHeaders($headers)->postJson("/api/v1/compras/{$compraId}/abonos", [
            'monto' => 60,
            'forma_pago' => 'Efectivo',
        ])->assertCreated();

        $this->assertDatabaseHas('compra', [
            'id' => $compraId,
            'adelanto' => 100,
            'saldo_total' => 0,
            'estado_financiero' => 'Pagado',
        ]);
    }

    public function test_registro_produccion_actualiza_stock_orden_y_logistica(): void
    {
        $headers = $this->authHeadersWithPermisos([
            'produccion.ordenes.crear',
            'produccion.registros.crear',
        ]);

        $cliente = Cliente::query()->create([
            'nombre' => 'Cliente Produccion',
            'activo' => true,
        ]);

        $producto = Producto::query()->create([
            'nombre' => 'Bloque Estandar',
            'activo' => true,
        ]);

        $variante = VarianteProducto::query()->create([
            'producto_id' => $producto->id,
            'nombre' => '20x40',
            'precio_venta' => 30,
            'stock_actual' => 10,
            'stock_minimo' => 2,
            'activo' => true,
        ]);

        $venta = Venta::query()->create([
            'cliente_id' => $cliente->id,
            'metodo_pago' => 'Credito',
            'forma_pago' => 'Efectivo',
            'importe_total' => 150,
            'adelanto' => 0,
            'saldo_total' => 150,
            'estado_operativo' => 'Confirmada',
            'estado_financiero' => 'Pendiente',
        ]);

        $maestro = Personal::query()->create([
            'nombre' => 'Maestro Uno',
            'tipo' => 'maestro',
            'activo' => true,
        ]);

        $ayudante = Personal::query()->create([
            'nombre' => 'Ayudante Uno',
            'tipo' => 'ayudante',
            'activo' => true,
        ]);

        $responseOrden = $this->withHeaders($headers)->postJson('/api/v1/ordenes-produccion', [
            'variante_id' => $variante->id,
            'venta_id' => $venta->id,
            'cantidad_requerida' => 5,
            'personal_ids' => [$maestro->id, $ayudante->id],
        ]);

        $responseOrden->assertCreated();
        $ordenId = $responseOrden->json('data.id');

        $this->assertDatabaseHas('orden_produccion', [
            'id' => $ordenId,
            'numero' => 'OP-0001',
            'estado' => 'Pendiente',
            'cantidad_requerida' => 5,
            'cantidad_producida' => 0,
        ]);

        $this->withHeaders($headers)->postJson("/api/v1/ordenes-produccion/{$ordenId}/registros", [
            'maestro_id' => $maestro->id,
            'cantidad_fabricada' => 5,
        ])->assertCreated();

        $this->assertDatabaseHas('variante_producto', [
            'id' => $variante->id,
            'stock_actual' => 15,
        ]);

        $this->assertDatabaseHas('movimiento_inventario', [
            'variante_id' => $variante->id,
            'tipo' => 'ENTRADA',
            'cantidad' => 5,
            'stock_resultante' => 15,
            'referencia_tipo' => 'registro_produccion',
        ]);

        $this->assertDatabaseHas('orden_produccion', [
            'id' => $ordenId,
            'cantidad_producida' => 5,
            'estado' => 'Completada',
        ]);

        $this->assertDatabaseHas('pedido_logistico', [
            'venta_id' => $venta->id,
            'estado' => 'En espera',
        ]);

        $this->assertDatabaseHas('venta', [
            'id' => $venta->id,
            'estado_operativo' => 'Lista',
        ]);
    }
}
