<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categoria_compra', function (Blueprint $table): void {
            $table->smallIncrements('id');
            $table->string('nombre', 80)->unique();
        });

        Schema::create('proveedor', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('nombre', 150);
            $table->string('telefono', 20)->nullable();
            $table->text('observaciones')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestampTz('creado_en')->useCurrent();
        });

        Schema::create('compra', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('numero', 20)->unique();
            $table->unsignedSmallInteger('categoria_id');
            $table->unsignedInteger('proveedor_id')->nullable();
            $table->enum('metodo_pago', ['Contado', 'Credito']);
            $table->enum('forma_pago', ['Efectivo', 'QR']);
            $table->decimal('importe_total', 14, 2)->default(0);
            $table->decimal('adelanto', 14, 2)->default(0);
            $table->decimal('saldo_total', 14, 2)->default(0);
            $table->enum('estado_financiero', ['Pendiente', 'Pagado'])->default('Pagado');
            $table->boolean('procesada_inventario')->default(false);
            $table->unsignedInteger('usuario_id')->nullable();
            $table->timestampTz('creado_en')->useCurrent();

            $table->foreign('categoria_id')->references('id')->on('categoria_compra');
            $table->foreign('proveedor_id')->references('id')->on('proveedor');
            $table->foreign('usuario_id')->references('id')->on('usuario');
        });

        Schema::create('detalle_compra', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('compra_id');
            $table->string('descripcion', 200);
            $table->decimal('cantidad', 12, 3)->nullable();
            $table->string('unidad_medida', 30)->nullable();
            $table->decimal('precio_unitario', 12, 2);
            $table->decimal('subtotal', 14, 2);

            $table->foreign('compra_id')->references('id')->on('compra')->cascadeOnDelete();
        });

        Schema::create('abono_compra', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('compra_id');
            $table->decimal('monto', 14, 2);
            $table->enum('forma_pago', ['Efectivo', 'QR']);
            $table->unsignedInteger('usuario_id')->nullable();
            $table->timestampTz('creado_en')->useCurrent();

            $table->foreign('compra_id')->references('id')->on('compra')->cascadeOnDelete();
            $table->foreign('usuario_id')->references('id')->on('usuario');
        });

        Schema::create('orden_produccion', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('numero', 20)->unique();
            $table->unsignedInteger('variante_id');
            $table->unsignedInteger('venta_id')->nullable();
            $table->integer('cantidad_requerida');
            $table->integer('cantidad_producida')->default(0);
            $table->enum('estado', ['Pendiente', 'En proceso', 'Completada'])->default('Pendiente');
            $table->date('fecha_entrega_requerida')->nullable();
            $table->timestampTz('creado_en')->useCurrent();
            $table->timestampTz('completada_en')->nullable();
            $table->unsignedInteger('creado_por')->nullable();

            $table->foreign('variante_id')->references('id')->on('variante_producto');
            $table->foreign('venta_id')->references('id')->on('venta');
            $table->foreign('creado_por')->references('id')->on('usuario');
        });

        Schema::create('registro_produccion', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('orden_id');
            $table->unsignedInteger('maestro_id');
            $table->integer('cantidad_fabricada');
            $table->date('fecha')->useCurrent();
            $table->timestampTz('creado_en')->useCurrent();

            $table->foreign('orden_id')->references('id')->on('orden_produccion')->cascadeOnDelete();
            $table->foreign('maestro_id')->references('id')->on('personal');
        });

        Schema::create('orden_produccion_personal', function (Blueprint $table): void {
            $table->unsignedInteger('orden_id');
            $table->unsignedInteger('personal_id');

            $table->primary(['orden_id', 'personal_id']);
            $table->foreign('orden_id')->references('id')->on('orden_produccion')->cascadeOnDelete();
            $table->foreign('personal_id')->references('id')->on('personal');
        });

        Schema::create('registro_jornal', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('personal_id');
            $table->date('fecha')->useCurrent();
            $table->time('hora_entrada');
            $table->time('hora_salida')->nullable();
            $table->text('observacion')->nullable();
            $table->timestampTz('creado_en')->useCurrent();

            $table->foreign('personal_id')->references('id')->on('personal');
        });

        Schema::create('pedido_logistico', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('venta_id')->unique();
            $table->enum('estado', ['En espera', 'En camino', 'Entregado'])->default('En espera');
            $table->text('observaciones')->nullable();
            $table->timestampTz('fecha_en_espera')->useCurrent();
            $table->timestampTz('fecha_en_camino')->nullable();
            $table->timestampTz('fecha_entregado')->nullable();
            $table->unsignedInteger('usuario_id')->nullable();

            $table->foreign('venta_id')->references('id')->on('venta');
            $table->foreign('usuario_id')->references('id')->on('usuario');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedido_logistico');
        Schema::dropIfExists('registro_jornal');
        Schema::dropIfExists('orden_produccion_personal');
        Schema::dropIfExists('registro_produccion');
        Schema::dropIfExists('orden_produccion');
        Schema::dropIfExists('abono_compra');
        Schema::dropIfExists('detalle_compra');
        Schema::dropIfExists('compra');
        Schema::dropIfExists('proveedor');
        Schema::dropIfExists('categoria_compra');
    }
};
