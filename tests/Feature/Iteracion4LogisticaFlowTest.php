<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\PedidoLogistico;
use App\Models\Personal;
use App\Models\Producto;
use App\Models\VarianteProducto;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Iteracion4LogisticaFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_orden_completada_para_venta_crea_pedido_logistico(): void
    {
        [$venta, $maestro, $variante] = $this->crearEscenarioBase();
        $headers = $this->authHeadersProduccion();

        $responseOrden = $this->withHeaders($headers)->postJson('/api/v1/ordenes-produccion', [
            'variante_id' => $variante->id,
            'venta_id' => $venta->id,
            'cantidad_requerida' => 4,
            'personal_ids' => [$maestro->id],
        ]);

        $responseOrden->assertCreated();

        $ordenId = $responseOrden->json('data.id');

        $this->withHeaders($headers)->postJson("/api/v1/ordenes-produccion/{$ordenId}/registros", [
            'maestro_id' => $maestro->id,
            'cantidad_fabricada' => 4,
        ])->assertCreated();

        $this->assertDatabaseHas('pedido_logistico', [
            'venta_id' => $venta->id,
            'estado' => 'En espera',
        ]);

        $this->assertDatabaseHas('venta', [
            'id' => $venta->id,
            'estado_operativo' => 'Lista',
        ]);
    }

    public function test_mover_pedido_a_en_camino_actualiza_estado_operativo_de_venta(): void
    {
        $pedido = $this->crearPedidoLogisticoDesdeOrdenCompletada();

        $this->withHeaders($this->authHeadersLogistica())
            ->putJson("/api/v1/logistica/pedidos/{$pedido->id}/estado", [
            'estado' => 'En camino',
            'observaciones' => 'Salida de planta',
        ])->assertOk();

        $this->assertDatabaseHas('pedido_logistico', [
            'id' => $pedido->id,
            'estado' => 'En camino',
            'observaciones' => 'Salida de planta',
        ]);

        $this->assertDatabaseHas('venta', [
            'id' => $pedido->venta_id,
            'estado_operativo' => 'En camino',
        ]);

        $this->assertNotNull(PedidoLogistico::query()->findOrFail($pedido->id)->fecha_en_camino);
    }

    public function test_listar_y_ver_detalle_de_pedidos_logisticos(): void
    {
        $pedido = $this->crearPedidoLogisticoDesdeOrdenCompletada();

        $this->withHeaders($this->authHeadersLogistica())
            ->getJson('/api/v1/logistica/pedidos')
            ->assertOk()
            ->assertJsonPath('data.0.id', $pedido->id);

        $this->withHeaders($this->authHeadersLogistica())
            ->getJson("/api/v1/logistica/pedidos/{$pedido->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $pedido->id)
            ->assertJsonPath('data.venta.id', $pedido->venta_id);
    }

    public function test_mover_pedido_a_entregado_actualiza_venta_y_fechas(): void
    {
        $pedido = $this->crearPedidoLogisticoDesdeOrdenCompletada();

        $this->withHeaders($this->authHeadersLogistica())
            ->putJson("/api/v1/logistica/pedidos/{$pedido->id}/estado", [
            'estado' => 'En camino',
        ])->assertOk();

        $this->withHeaders($this->authHeadersLogistica())
            ->putJson("/api/v1/logistica/pedidos/{$pedido->id}/estado", [
            'estado' => 'Entregado',
            'observaciones' => 'Entrega finalizada',
        ])->assertOk();

        $pedidoRefrescado = PedidoLogistico::query()->findOrFail($pedido->id);
        $ventaRefrescada = Venta::query()->findOrFail($pedido->venta_id);

        $this->assertSame('Entregado', $pedidoRefrescado->estado);
        $this->assertNotNull($pedidoRefrescado->fecha_entregado);
        $this->assertSame('Completada', $ventaRefrescada->estado_operativo);
        $this->assertNotNull($ventaRefrescada->completada_en);
    }

    public function test_transicion_invalida_de_pedido_logistico_retorna_422(): void
    {
        $pedido = $this->crearPedidoLogisticoDesdeOrdenCompletada();

        $this->withHeaders($this->authHeadersLogistica())
            ->putJson("/api/v1/logistica/pedidos/{$pedido->id}/estado", [
            'estado' => 'Entregado',
        ])->assertStatus(422)->assertJsonValidationErrors(['estado']);

        $this->assertDatabaseHas('pedido_logistico', [
            'id' => $pedido->id,
            'estado' => 'En espera',
        ]);
    }

    /**
     * @return array{Venta, Personal, VarianteProducto}
     */
    private function crearEscenarioBase(): array
    {
        $cliente = Cliente::query()->create([
            'nombre' => 'Cliente Logistica',
            'activo' => true,
        ]);

        $producto = Producto::query()->create([
            'nombre' => 'Ladrillo estructural',
            'activo' => true,
        ]);

        $variante = VarianteProducto::query()->create([
            'producto_id' => $producto->id,
            'nombre' => '10x20',
            'precio_venta' => 18,
            'stock_actual' => 5,
            'stock_minimo' => 2,
            'activo' => true,
        ]);

        $venta = Venta::query()->create([
            'cliente_id' => $cliente->id,
            'metodo_pago' => 'Credito',
            'forma_pago' => 'Efectivo',
            'importe_total' => 72,
            'adelanto' => 0,
            'saldo_total' => 72,
            'estado_operativo' => 'Confirmada',
            'estado_financiero' => 'Pendiente',
        ]);

        $maestro = Personal::query()->create([
            'nombre' => 'Maestro Logistica',
            'tipo' => 'maestro',
            'activo' => true,
        ]);

        return [$venta, $maestro, $variante];
    }

    private function crearPedidoLogisticoDesdeOrdenCompletada(): PedidoLogistico
    {
        [$venta, $maestro, $variante] = $this->crearEscenarioBase();
        $headers = $this->authHeadersProduccion();

        $ordenId = $this->withHeaders($headers)->postJson('/api/v1/ordenes-produccion', [
            'variante_id' => $variante->id,
            'venta_id' => $venta->id,
            'cantidad_requerida' => 4,
            'personal_ids' => [$maestro->id],
        ])->assertCreated()->json('data.id');

        $this->withHeaders($headers)->postJson("/api/v1/ordenes-produccion/{$ordenId}/registros", [
            'maestro_id' => $maestro->id,
            'cantidad_fabricada' => 4,
        ])->assertCreated();

        return PedidoLogistico::query()->where('venta_id', $venta->id)->firstOrFail();
    }

    /**
     * @return array<string, string>
     */
    private function authHeadersLogistica(): array
    {
        return $this->authHeadersWithPermisos([
            'logistica.kanban.ver',
            'logistica.kanban.mover',
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function authHeadersProduccion(): array
    {
        return $this->authHeadersWithPermisos([
            'produccion.ordenes.crear',
            'produccion.registros.crear',
        ]);
    }
}
