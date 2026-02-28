<?php

namespace Tests\Feature;

use App\Models\Usuario;
use App\Services\JwtTokenService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MatrizRolesTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_ventas_tiene_accesos_de_ventas_y_no_de_compras(): void
    {
        $this->seed(DatabaseSeeder::class);
        $headers = $this->headersFor('ventas@concresur.com');

        $this->withHeaders($headers)->getJson('/api/v1/ventas')->assertOk();
        $this->withHeaders($headers)->getJson('/api/v1/cotizaciones')->assertOk();
        $this->withHeaders($headers)->getJson('/api/v1/compras')->assertStatus(403);
        $this->withHeaders($headers)->getJson('/api/v1/logistica/pedidos')->assertStatus(403);
    }

    public function test_usuario_compras_tiene_accesos_de_compras_y_no_de_produccion(): void
    {
        $this->seed(DatabaseSeeder::class);
        $headers = $this->headersFor('compras@concresur.com');

        $this->withHeaders($headers)->getJson('/api/v1/compras')->assertOk();
        $this->withHeaders($headers)->getJson('/api/v1/proveedores')->assertOk();
        $this->withHeaders($headers)->getJson('/api/v1/ordenes-produccion')->assertStatus(403);
    }

    public function test_usuario_produccion_tiene_accesos_de_produccion_y_no_de_logistica(): void
    {
        $this->seed(DatabaseSeeder::class);
        $headers = $this->headersFor('produccion@concresur.com');

        $this->withHeaders($headers)->getJson('/api/v1/ordenes-produccion')->assertOk();
        $this->withHeaders($headers)->getJson('/api/v1/personal')->assertOk();
        $this->withHeaders($headers)->getJson('/api/v1/logistica/kanban')->assertStatus(403);
    }

    public function test_usuario_logistica_tiene_accesos_de_logistica_y_no_de_ventas(): void
    {
        $this->seed(DatabaseSeeder::class);
        $headers = $this->headersFor('logistica@concresur.com');

        $this->withHeaders($headers)->getJson('/api/v1/logistica/kanban')->assertOk();
        $this->withHeaders($headers)->getJson('/api/v1/logistica/pedidos')->assertOk();
        $this->withHeaders($headers)->getJson('/api/v1/ventas')->assertStatus(403);
    }

    public function test_admin_tiene_acceso_transversal(): void
    {
        $this->seed(DatabaseSeeder::class);
        $headers = $this->headersFor('admin@concresur.com');

        $this->withHeaders($headers)->getJson('/api/v1/ventas')->assertOk();
        $this->withHeaders($headers)->getJson('/api/v1/compras')->assertOk();
        $this->withHeaders($headers)->getJson('/api/v1/ordenes-produccion')->assertOk();
        $this->withHeaders($headers)->getJson('/api/v1/logistica/kanban')->assertOk();
    }

    /**
     * @return array<string, string>
     */
    private function headersFor(string $email): array
    {
        $usuario = Usuario::query()->where('email', $email)->firstOrFail();
        $token = app(JwtTokenService::class)->issueToken($usuario);

        return [
            'Authorization' => 'Bearer '.$token,
        ];
    }
}
