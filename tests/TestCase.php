<?php

namespace Tests;

use App\Models\Permiso;
use App\Models\Rol;
use App\Models\Usuario;
use App\Services\JwtTokenService;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * @param  array<int, string>  $permisos
     * @return array<string, string>
     */
    protected function authHeadersWithPermisos(array $permisos): array
    {
        $rol = Rol::query()->create([
            'nombre' => 'Rol test '.uniqid(),
        ]);

        $permisoIds = [];

        foreach ($permisos as $clave) {
            $permisoIds[] = Permiso::query()->firstOrCreate(
                ['clave' => $clave],
                ['nombre' => $clave]
            )->id;
        }

        $rol->permisos()->sync($permisoIds);

        $usuario = Usuario::query()->create([
            'nombre' => 'Usuario Test '.uniqid(),
            'email' => uniqid('test_', true).'@example.com',
            'password_hash' => 'hash-no-usado',
            'rol_id' => $rol->id,
            'activo' => true,
        ]);

        $token = app(JwtTokenService::class)->issueToken($usuario);

        return [
            'Authorization' => 'Bearer '.$token,
        ];
    }
}
