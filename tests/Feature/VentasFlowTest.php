<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Producto;
use App\Models\VarianteProducto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VentasFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_confirmar_cotizacion_generates_sale_and_inventory_movement(): void
    {
        $headers = $this->authHeadersWithPermisos([
            'ventas.cotizaciones.crear',
            'ventas.ventas.confirmar',
        ]);

        $cliente = Cliente::query()->create([
            'nombre' => 'Cliente Demo',
            'activo' => true,
        ]);

        $producto = Producto::query()->create([
            'nombre' => 'Bloque',
            'activo' => true,
        ]);

        $variante = VarianteProducto::query()->create([
            'producto_id' => $producto->id,
            'nombre' => '20x40',
            'precio_venta' => 50,
            'stock_actual' => 10,
            'stock_minimo' => 2,
            'activo' => true,
        ]);

        $responseCotizacion = $this->withHeaders($headers)->postJson('/api/v1/cotizaciones', [
            'cliente_id' => $cliente->id,
            'detalles' => [
                [
                    'variante_id' => $variante->id,
                    'cantidad' => 2,
                    'precio_unitario' => 50,
                ],
            ],
        ]);

        $responseCotizacion->assertCreated();

        $cotizacionId = $responseCotizacion->json('data.id');

        $responseConfirmar = $this->withHeaders($headers)->postJson("/api/v1/cotizaciones/{$cotizacionId}/confirmar", [
            'metodo_pago' => 'Credito',
            'forma_pago' => 'Efectivo',
        ]);

        $responseConfirmar->assertOk();

        $this->assertDatabaseHas('cotizacion', [
            'id' => $cotizacionId,
            'estado' => 'Confirmada',
            'numero' => 'COT-0001',
        ]);

        $this->assertDatabaseHas('venta', [
            'cotizacion_id' => $cotizacionId,
            'numero' => 'VTA-0001',
            'importe_total' => 100,
        ]);

        $this->assertDatabaseHas('variante_producto', [
            'id' => $variante->id,
            'stock_actual' => 8,
        ]);

        $this->assertDatabaseHas('movimiento_inventario', [
            'variante_id' => $variante->id,
            'tipo' => 'SALIDA',
            'cantidad' => 2,
            'stock_resultante' => 8,
        ]);
    }

    public function test_abono_endpoint_updates_sale_balances_via_observer(): void
    {
        $headers = $this->authHeadersWithPermisos([
            'ventas.ventas.crear',
            'ventas.ventas.abonar',
        ]);

        $cliente = Cliente::query()->create([
            'nombre' => 'Cliente Abono',
            'activo' => true,
        ]);

        $producto = Producto::query()->create([
            'nombre' => 'Ladrillo',
            'activo' => true,
        ]);

        $variante = VarianteProducto::query()->create([
            'producto_id' => $producto->id,
            'nombre' => 'Simple',
            'precio_venta' => 25,
            'stock_actual' => 20,
            'stock_minimo' => 3,
            'activo' => true,
        ]);

        $responseVenta = $this->withHeaders($headers)->postJson('/api/v1/ventas/directa', [
            'cliente_id' => $cliente->id,
            'metodo_pago' => 'Credito',
            'forma_pago' => 'QR',
            'detalles' => [
                [
                    'variante_id' => $variante->id,
                    'cantidad' => 4,
                    'precio_unitario' => 25,
                ],
            ],
        ]);

        $responseVenta->assertCreated();
        $ventaId = $responseVenta->json('data.id');

        $this->withHeaders($headers)->postJson("/api/v1/ventas/{$ventaId}/abonos", [
            'monto' => 30,
            'forma_pago' => 'Efectivo',
        ])->assertCreated();

        $this->assertDatabaseHas('venta', [
            'id' => $ventaId,
            'adelanto' => 30,
            'saldo_total' => 70,
            'estado_financiero' => 'Pendiente',
        ]);

        $this->withHeaders($headers)->postJson("/api/v1/ventas/{$ventaId}/abonos", [
            'monto' => 70,
            'forma_pago' => 'QR',
        ])->assertCreated();

        $this->assertDatabaseHas('venta', [
            'id' => $ventaId,
            'adelanto' => 100,
            'saldo_total' => 0,
            'estado_financiero' => 'Pagado',
        ]);
    }

    public function test_venta_directa_con_stock_insuficiente_hace_rollback_transaccional(): void
    {
        $headers = $this->authHeadersWithPermisos([
            'ventas.ventas.crear',
        ]);

        $cliente = Cliente::query()->create([
            'nombre' => 'Cliente Rollback',
            'activo' => true,
        ]);

        $producto = Producto::query()->create([
            'nombre' => 'Ladrillo Rollback',
            'activo' => true,
        ]);

        $variante = VarianteProducto::query()->create([
            'producto_id' => $producto->id,
            'nombre' => 'RB',
            'precio_venta' => 10,
            'stock_actual' => 1,
            'stock_minimo' => 0,
            'activo' => true,
        ]);

        $this->withHeaders($headers)
            ->postJson('/api/v1/ventas/directa', [
                'cliente_id' => $cliente->id,
                'metodo_pago' => 'Contado',
                'forma_pago' => 'Efectivo',
                'detalles' => [
                    [
                        'variante_id' => $variante->id,
                        'cantidad' => 2,
                        'precio_unitario' => 10,
                    ],
                ],
            ])
            ->assertStatus(422)
            ->assertJsonStructure(['message', 'data', 'errors'])
            ->assertJsonPath('data', null);

        $this->assertDatabaseHas('variante_producto', [
            'id' => $variante->id,
            'stock_actual' => 1,
        ]);

        $this->assertDatabaseMissing('movimiento_inventario', [
            'variante_id' => $variante->id,
            'tipo' => 'SALIDA',
        ]);
    }
}
