<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cotizacion', function (Blueprint $table): void {
            $table->index('estado', 'idx_cotizacion_estado');
            $table->index('cliente_id', 'idx_cotizacion_cliente_id');
            $table->index('creado_en', 'idx_cotizacion_creado_en');
        });

        Schema::table('venta', function (Blueprint $table): void {
            $table->index('cliente_id', 'idx_venta_cliente_id');
            $table->index('estado_operativo', 'idx_venta_estado_operativo');
            $table->index('estado_financiero', 'idx_venta_estado_financiero');
            $table->index('creado_en', 'idx_venta_creado_en');
        });

        Schema::table('compra', function (Blueprint $table): void {
            $table->index('categoria_id', 'idx_compra_categoria_id');
            $table->index('proveedor_id', 'idx_compra_proveedor_id');
            $table->index('estado_financiero', 'idx_compra_estado_financiero');
            $table->index('creado_en', 'idx_compra_creado_en');
        });

        Schema::table('orden_produccion', function (Blueprint $table): void {
            $table->index('variante_id', 'idx_orden_prod_variante_id');
            $table->index('venta_id', 'idx_orden_prod_venta_id');
            $table->index('estado', 'idx_orden_prod_estado');
            $table->index('creado_en', 'idx_orden_prod_creado_en');
        });

        Schema::table('pedido_logistico', function (Blueprint $table): void {
            $table->index('estado', 'idx_pedido_logistico_estado');
        });

        Schema::table('cliente', function (Blueprint $table): void {
            $table->index('activo', 'idx_cliente_activo');
            $table->index('nombre', 'idx_cliente_nombre');
        });

        Schema::table('proveedor', function (Blueprint $table): void {
            $table->index('activo', 'idx_proveedor_activo');
            $table->index('nombre', 'idx_proveedor_nombre');
        });

        Schema::table('personal', function (Blueprint $table): void {
            $table->index('activo', 'idx_personal_activo');
            $table->index('tipo', 'idx_personal_tipo');
            $table->index('nombre', 'idx_personal_nombre');
        });

        Schema::table('producto', function (Blueprint $table): void {
            $table->index('activo', 'idx_producto_activo');
            $table->index('nombre', 'idx_producto_nombre');
        });

        Schema::table('variante_producto', function (Blueprint $table): void {
            $table->index('producto_id', 'idx_variante_producto_producto_id');
            $table->index('activo', 'idx_variante_producto_activo');
            $table->index('nombre', 'idx_variante_producto_nombre');
        });
    }

    public function down(): void
    {
        Schema::table('variante_producto', function (Blueprint $table): void {
            $table->dropIndex('idx_variante_producto_nombre');
            $table->dropIndex('idx_variante_producto_activo');
            $table->dropIndex('idx_variante_producto_producto_id');
        });

        Schema::table('producto', function (Blueprint $table): void {
            $table->dropIndex('idx_producto_nombre');
            $table->dropIndex('idx_producto_activo');
        });

        Schema::table('personal', function (Blueprint $table): void {
            $table->dropIndex('idx_personal_nombre');
            $table->dropIndex('idx_personal_tipo');
            $table->dropIndex('idx_personal_activo');
        });

        Schema::table('proveedor', function (Blueprint $table): void {
            $table->dropIndex('idx_proveedor_nombre');
            $table->dropIndex('idx_proveedor_activo');
        });

        Schema::table('cliente', function (Blueprint $table): void {
            $table->dropIndex('idx_cliente_nombre');
            $table->dropIndex('idx_cliente_activo');
        });

        Schema::table('pedido_logistico', function (Blueprint $table): void {
            $table->dropIndex('idx_pedido_logistico_estado');
        });

        Schema::table('orden_produccion', function (Blueprint $table): void {
            $table->dropIndex('idx_orden_prod_creado_en');
            $table->dropIndex('idx_orden_prod_estado');
            $table->dropIndex('idx_orden_prod_venta_id');
            $table->dropIndex('idx_orden_prod_variante_id');
        });

        Schema::table('compra', function (Blueprint $table): void {
            $table->dropIndex('idx_compra_creado_en');
            $table->dropIndex('idx_compra_estado_financiero');
            $table->dropIndex('idx_compra_proveedor_id');
            $table->dropIndex('idx_compra_categoria_id');
        });

        Schema::table('venta', function (Blueprint $table): void {
            $table->dropIndex('idx_venta_creado_en');
            $table->dropIndex('idx_venta_estado_financiero');
            $table->dropIndex('idx_venta_estado_operativo');
            $table->dropIndex('idx_venta_cliente_id');
        });

        Schema::table('cotizacion', function (Blueprint $table): void {
            $table->dropIndex('idx_cotizacion_creado_en');
            $table->dropIndex('idx_cotizacion_cliente_id');
            $table->dropIndex('idx_cotizacion_estado');
        });
    }
};
