<?php

namespace Database\Seeders;

use App\Models\Permiso;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $permisos = [
            'seguridad.base' => 'Acceso base de seguridad',
            'ventas.cotizaciones.ver' => 'Ver cotizaciones',
            'ventas.cotizaciones.crear' => 'Crear cotizaciones',
            'ventas.ventas.ver' => 'Ver ventas',
            'ventas.ventas.crear' => 'Crear ventas',
            'ventas.ventas.confirmar' => 'Confirmar cotizaciones a venta',
            'ventas.ventas.abonar' => 'Registrar abonos de ventas',
            'compras.compras.ver' => 'Ver compras',
            'compras.compras.crear' => 'Crear compras',
            'compras.compras.abonar' => 'Registrar abonos de compras',
            'produccion.ordenes.ver' => 'Ver ordenes de produccion',
            'produccion.ordenes.crear' => 'Crear ordenes de produccion',
            'produccion.registros.crear' => 'Registrar produccion',
            'logistica.kanban.ver' => 'Ver tablero logistico',
            'logistica.kanban.mover' => 'Mover estado logistico',
            'catalogos.clientes.ver' => 'Ver clientes',
            'catalogos.clientes.crear' => 'Crear o editar clientes',
            'catalogos.clientes.eliminar' => 'Desactivar clientes',
            'catalogos.proveedores.ver' => 'Ver proveedores',
            'catalogos.proveedores.crear' => 'Crear o editar proveedores',
            'catalogos.proveedores.eliminar' => 'Desactivar proveedores',
            'catalogos.personal.ver' => 'Ver personal',
            'catalogos.personal.crear' => 'Crear o editar personal',
            'catalogos.personal.eliminar' => 'Desactivar personal',
            'catalogos.categorias_compra.ver' => 'Ver categorias de compra',
            'catalogos.categorias_compra.crear' => 'Crear o editar categorias de compra',
            'catalogos.productos.ver' => 'Ver productos',
            'catalogos.productos.crear' => 'Crear o editar productos',
            'catalogos.productos.eliminar' => 'Desactivar productos',
            'catalogos.variantes.ver' => 'Ver variantes de producto',
            'catalogos.variantes.crear' => 'Crear o editar variantes de producto',
            'catalogos.variantes.eliminar' => 'Desactivar variantes de producto',
        ];

        $permisoIdsPorClave = [];

        foreach ($permisos as $clave => $nombre) {
            $permisoIdsPorClave[$clave] = Permiso::query()->firstOrCreate(
                ['clave' => $clave],
                ['nombre' => $nombre]
            )->id;
        }

        $matrizRoles = [
            'Administrador' => array_keys($permisos),
            'Ventas' => [
                'seguridad.base',
                'ventas.cotizaciones.ver',
                'ventas.cotizaciones.crear',
                'ventas.ventas.ver',
                'ventas.ventas.crear',
                'ventas.ventas.confirmar',
                'ventas.ventas.abonar',
                'catalogos.clientes.ver',
                'catalogos.clientes.crear',
                'catalogos.clientes.eliminar',
                'catalogos.productos.ver',
                'catalogos.variantes.ver',
            ],
            'Compras' => [
                'seguridad.base',
                'compras.compras.ver',
                'compras.compras.crear',
                'compras.compras.abonar',
                'catalogos.proveedores.ver',
                'catalogos.proveedores.crear',
                'catalogos.proveedores.eliminar',
                'catalogos.categorias_compra.ver',
                'catalogos.categorias_compra.crear',
            ],
            'Produccion' => [
                'seguridad.base',
                'produccion.ordenes.ver',
                'produccion.ordenes.crear',
                'produccion.registros.crear',
                'catalogos.personal.ver',
                'catalogos.productos.ver',
                'catalogos.variantes.ver',
            ],
            'Logistica' => [
                'seguridad.base',
                'logistica.kanban.ver',
                'logistica.kanban.mover',
            ],
        ];

        $usuariosPorRol = [
            'Administrador' => ['admin@concresur.com', 'Admin Concresur'],
            'Ventas' => ['ventas@concresur.com', 'Usuario Ventas'],
            'Compras' => ['compras@concresur.com', 'Usuario Compras'],
            'Produccion' => ['produccion@concresur.com', 'Usuario Produccion'],
            'Logistica' => ['logistica@concresur.com', 'Usuario Logistica'],
        ];

        foreach ($matrizRoles as $nombreRol => $clavesPermisos) {
            $rol = Rol::query()->firstOrCreate([
                'nombre' => $nombreRol,
            ]);

            $ids = array_values(array_map(
                static fn (string $clave): int => $permisoIdsPorClave[$clave],
                $clavesPermisos
            ));

            $rol->permisos()->sync($ids);

            [$email, $nombreUsuario] = $usuariosPorRol[$nombreRol];

            Usuario::query()->updateOrCreate([
                'email' => $email,
            ], [
                'nombre' => $nombreUsuario,
                'password_hash' => Hash::make('password'),
                'rol_id' => $rol->id,
                'activo' => true,
            ]);
        }
    }
}
