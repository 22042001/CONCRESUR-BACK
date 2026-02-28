<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\PedidoLogistico;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeguridadPermisosTest extends TestCase
{
    use RefreshDatabase;

    public function test_ruta_protegida_retorna_401_sin_token(): void
    {
        $this->getJson('/api/v1/ventas')->assertStatus(401);
    }

    public function test_ruta_protegida_retorna_403_sin_permiso_requerido(): void
    {
        $headers = $this->authHeadersWithPermisos([]);

        $this->withHeaders($headers)
            ->getJson('/api/v1/ventas')
            ->assertStatus(403);
    }

    public function test_modulo_catalogos_exige_permiso_para_listar_clientes(): void
    {
        $headersSinPermiso = $this->authHeadersWithPermisos([]);

        $this->withHeaders($headersSinPermiso)
            ->getJson('/api/v1/clientes')
            ->assertStatus(403);

        $headersConPermiso = $this->authHeadersWithPermisos(['catalogos.clientes.ver']);

        $this->withHeaders($headersConPermiso)
            ->getJson('/api/v1/clientes')
            ->assertOk();
    }

    public function test_modulo_compras_exige_permiso_para_crear_compra(): void
    {
        $headers = $this->authHeadersWithPermisos([]);

        $this->withHeaders($headers)
            ->postJson('/api/v1/compras', [])
            ->assertStatus(403);
    }

    public function test_logistica_mover_estado_exige_permiso_mover(): void
    {
        $pedido = $this->crearPedidoLogistico();

        $headers = $this->authHeadersWithPermisos(['logistica.kanban.ver']);

        $this->withHeaders($headers)
            ->putJson("/api/v1/logistica/pedidos/{$pedido->id}/estado", [
                'estado' => 'En camino',
            ])
            ->assertStatus(403);
    }

    private function crearPedidoLogistico(): PedidoLogistico
    {
        $cliente = Cliente::query()->create([
            'nombre' => 'Cliente Seguridad',
            'activo' => true,
        ]);

        $venta = Venta::query()->create([
            'cliente_id' => $cliente->id,
            'metodo_pago' => 'Credito',
            'forma_pago' => 'Efectivo',
            'importe_total' => 100,
            'adelanto' => 0,
            'saldo_total' => 100,
            'estado_operativo' => 'Lista',
            'estado_financiero' => 'Pendiente',
        ]);

        return PedidoLogistico::query()->create([
            'venta_id' => $venta->id,
            'estado' => 'En espera',
        ]);
    }
}
