<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Permiso;
use App\Models\Producto;
use App\Models\Rol;
use App\Models\Usuario;
use App\Models\VarianteProducto;
use App\Services\JwtTokenService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CierreBackendTest extends TestCase
{
    use RefreshDatabase;

    public function test_auth_me_devuelve_rol_y_permisos(): void
    {
        $this->seed(DatabaseSeeder::class);

        $login = $this->postJson('/api/v1/auth/login', [
            'email' => 'ventas@concresur.com',
            'password' => 'password',
        ])->assertOk();

        $token = $login->json('access_token');

        $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.rol', 'Ventas')
            ->assertJsonStructure([
                'message',
                'data' => ['id', 'nombre', 'email', 'rol', 'permisos'],
            ]);
    }

    public function test_delete_logico_cliente_desactiva_registro(): void
    {
        $headers = $this->authHeadersWithPermisos([
            'catalogos.clientes.eliminar',
            'catalogos.clientes.ver',
        ]);

        $cliente = Cliente::query()->create([
            'nombre' => 'Cliente Eliminar',
            'activo' => true,
        ]);

        $this->withHeaders($headers)
            ->deleteJson("/api/v1/clientes/{$cliente->id}")
            ->assertOk()
            ->assertJsonPath('data.activo', false);

        $this->assertDatabaseHas('cliente', [
            'id' => $cliente->id,
            'activo' => false,
        ]);
    }

    public function test_error_422_mantiene_contrato_estandar(): void
    {
        $headers = $this->authHeadersWithPermisos(['compras.compras.crear']);

        $this->withHeaders($headers)
            ->postJson('/api/v1/compras', [])
            ->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'data',
                'errors',
            ])
            ->assertJsonPath('data', null);
    }

    public function test_error_404_model_binding_mantiene_contrato_estandar(): void
    {
        $headers = $this->authHeadersWithPermisos(['ventas.ventas.ver']);

        $this->withHeaders($headers)
            ->getJson('/api/v1/ventas/999999')
            ->assertStatus(404)
            ->assertJsonStructure([
                'message',
                'data',
                'errors',
            ])
            ->assertJsonPath('data', null);
    }

    public function test_registro_abono_venta_usa_usuario_autenticado_por_defecto(): void
    {
        $rol = Rol::query()->create([
            'nombre' => 'AuditoriaVentas',
        ]);

        $permisoCrear = Permiso::query()->create([
            'clave' => 'ventas.ventas.crear',
            'nombre' => 'ventas.ventas.crear',
        ]);

        $permisoAbonar = Permiso::query()->create([
            'clave' => 'ventas.ventas.abonar',
            'nombre' => 'ventas.ventas.abonar',
        ]);

        $rol->permisos()->sync([$permisoCrear->id, $permisoAbonar->id]);

        $usuario = Usuario::query()->create([
            'nombre' => 'Audit User',
            'email' => 'audit@example.com',
            'password_hash' => 'hash-no-usado',
            'rol_id' => $rol->id,
            'activo' => true,
        ]);

        $token = app(JwtTokenService::class)->issueToken($usuario);
        $headers = ['Authorization' => 'Bearer '.$token];

        $cliente = Cliente::query()->create([
            'nombre' => 'Cliente Audit',
            'activo' => true,
        ]);

        $producto = Producto::query()->create([
            'nombre' => 'Bloque Audit',
            'activo' => true,
        ]);

        $variante = VarianteProducto::query()->create([
            'producto_id' => $producto->id,
            'nombre' => '10x20',
            'stock_actual' => 20,
            'stock_minimo' => 2,
            'activo' => true,
        ]);

        $ventaResponse = $this->withHeaders($headers)
            ->postJson('/api/v1/ventas/directa', [
                'cliente_id' => $cliente->id,
                'metodo_pago' => 'Credito',
                'forma_pago' => 'Efectivo',
                'detalles' => [
                    [
                        'variante_id' => $variante->id,
                        'cantidad' => 1,
                        'precio_unitario' => 12,
                    ],
                ],
            ])
            ->assertCreated();

        $ventaId = $ventaResponse->json('data.id');

        $this->withHeaders($headers)
            ->postJson("/api/v1/ventas/{$ventaId}/abonos", [
                'monto' => 5,
                'forma_pago' => 'QR',
            ])
            ->assertCreated()
            ->assertJsonPath('data.usuario_id', $usuario->id);
    }
}
