<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Cotizacion;
use App\Models\DetalleCotizacion;
use App\Models\Producto;
use App\Models\VarianteProducto;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContratosApiPaginacionTest extends TestCase
{
    use RefreshDatabase;

    public function test_listado_ventas_devuelve_contrato_consistente_y_meta_de_paginacion(): void
    {
        $headers = $this->authHeadersWithPermisos(['ventas.ventas.ver']);

        $cliente = Cliente::query()->create([
            'nombre' => 'Cliente Paginado',
            'activo' => true,
        ]);

        Venta::query()->create([
            'numero' => 'VTA-0001',
            'cliente_id' => $cliente->id,
            'metodo_pago' => 'Credito',
            'forma_pago' => 'Efectivo',
            'importe_total' => 100,
            'adelanto' => 0,
            'saldo_total' => 100,
            'estado_operativo' => 'Confirmada',
            'estado_financiero' => 'Pendiente',
        ]);

        Venta::query()->create([
            'numero' => 'VTA-0002',
            'cliente_id' => $cliente->id,
            'metodo_pago' => 'Contado',
            'forma_pago' => 'QR',
            'importe_total' => 80,
            'adelanto' => 80,
            'saldo_total' => 0,
            'estado_operativo' => 'Completada',
            'estado_financiero' => 'Pagado',
        ]);

        $this->withHeaders($headers)
            ->getJson('/api/v1/ventas?per_page=1')
            ->assertOk()
            ->assertJsonStructure([
                'message',
                'data',
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ])
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 2);
    }

    public function test_listado_cotizaciones_permite_filtrar_por_estado_y_busqueda(): void
    {
        $headers = $this->authHeadersWithPermisos(['ventas.cotizaciones.ver']);

        $cliente = Cliente::query()->create([
            'nombre' => 'Cliente Cotizaciones',
            'activo' => true,
        ]);

        $producto = Producto::query()->create([
            'nombre' => 'Bloque Test',
            'activo' => true,
        ]);

        $variante = VarianteProducto::query()->create([
            'producto_id' => $producto->id,
            'nombre' => '20x40',
            'stock_actual' => 100,
            'stock_minimo' => 10,
            'activo' => true,
        ]);

        $cotPendiente = Cotizacion::query()->create([
            'numero' => 'COT-9001',
            'cliente_id' => $cliente->id,
            'estado' => 'Pendiente',
            'importe_total' => 50,
        ]);

        DetalleCotizacion::query()->create([
            'cotizacion_id' => $cotPendiente->id,
            'variante_id' => $variante->id,
            'cantidad' => 1,
            'precio_unitario' => 50,
            'subtotal' => 50,
        ]);

        $cotConfirmada = Cotizacion::query()->create([
            'numero' => 'COT-9002',
            'cliente_id' => $cliente->id,
            'estado' => 'Confirmada',
            'importe_total' => 60,
        ]);

        DetalleCotizacion::query()->create([
            'cotizacion_id' => $cotConfirmada->id,
            'variante_id' => $variante->id,
            'cantidad' => 1,
            'precio_unitario' => 60,
            'subtotal' => 60,
        ]);

        $this->withHeaders($headers)
            ->getJson('/api/v1/cotizaciones?estado=Pendiente&q=9001')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.numero', 'COT-9001')
            ->assertJsonPath('data.0.estado', 'Pendiente');
    }

    public function test_catalogos_clientes_aplican_paginacion_y_filtro_por_busqueda(): void
    {
        $headers = $this->authHeadersWithPermisos(['catalogos.clientes.ver']);

        Cliente::query()->create(['nombre' => 'Cliente Alfa', 'activo' => true]);
        Cliente::query()->create(['nombre' => 'Cliente Beta', 'activo' => true]);

        $this->withHeaders($headers)
            ->getJson('/api/v1/clientes?per_page=1&q=Beta')
            ->assertOk()
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.nombre', 'Cliente Beta');
    }
}
