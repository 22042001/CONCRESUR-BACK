<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cliente', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('nombre', 150);
            $table->string('telefono', 20)->nullable();
            $table->string('direccion', 50)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestampTz('creado_en')->useCurrent();
        });

        Schema::create('cotizacion', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('numero', 20)->unique();
            $table->unsignedInteger('cliente_id');
            $table->decimal('importe_total', 14, 2)->default(0);
            $table->enum('estado', ['Pendiente', 'Confirmada', 'Anulada'])->default('Pendiente');
            $table->timestampTz('creado_en')->useCurrent();
            $table->timestampTz('confirmada_en')->nullable();
            $table->unsignedInteger('venta_id')->nullable();
            $table->unsignedInteger('usuario_id')->nullable();

            $table->foreign('cliente_id')->references('id')->on('cliente');
            $table->foreign('usuario_id')->references('id')->on('usuario');
        });

        Schema::create('detalle_cotizacion', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('cotizacion_id');
            $table->unsignedInteger('variante_id');
            $table->integer('cantidad');
            $table->decimal('precio_unitario', 12, 2);
            $table->decimal('subtotal', 14, 2);

            $table->foreign('cotizacion_id')->references('id')->on('cotizacion')->cascadeOnDelete();
            $table->foreign('variante_id')->references('id')->on('variante_producto');
        });

        Schema::create('venta', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('numero', 20)->unique();
            $table->unsignedInteger('cliente_id');
            $table->unsignedInteger('cotizacion_id')->nullable();
            $table->enum('metodo_pago', ['Contado', 'Credito']);
            $table->enum('forma_pago', ['Efectivo', 'QR']);
            $table->date('fecha_entrega')->nullable();
            $table->decimal('importe_total', 14, 2)->default(0);
            $table->decimal('adelanto', 14, 2)->default(0);
            $table->decimal('saldo_total', 14, 2)->default(0);
            $table->enum('estado_operativo', ['Confirmada', 'En produccion', 'Lista', 'En camino', 'Completada'])->default('Confirmada');
            $table->enum('estado_financiero', ['Pendiente', 'Pagado'])->default('Pagado');
            $table->unsignedInteger('usuario_id')->nullable();
            $table->timestampTz('creado_en')->useCurrent();
            $table->timestampTz('completada_en')->nullable();

            $table->foreign('cliente_id')->references('id')->on('cliente');
            $table->foreign('cotizacion_id')->references('id')->on('cotizacion');
            $table->foreign('usuario_id')->references('id')->on('usuario');
        });

        Schema::create('detalle_venta', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('venta_id');
            $table->unsignedInteger('variante_id');
            $table->integer('cantidad');
            $table->decimal('precio_unitario', 12, 2);
            $table->decimal('subtotal', 14, 2);

            $table->foreign('venta_id')->references('id')->on('venta')->cascadeOnDelete();
            $table->foreign('variante_id')->references('id')->on('variante_producto');
        });

        Schema::create('abono_venta', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('venta_id');
            $table->decimal('monto', 14, 2);
            $table->enum('forma_pago', ['Efectivo', 'QR']);
            $table->unsignedInteger('usuario_id')->nullable();
            $table->timestampTz('creado_en')->useCurrent();

            $table->foreign('venta_id')->references('id')->on('venta')->cascadeOnDelete();
            $table->foreign('usuario_id')->references('id')->on('usuario');
        });

        Schema::create('movimiento_inventario', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->unsignedInteger('variante_id');
            $table->enum('tipo', ['ENTRADA', 'SALIDA', 'AJUSTE']);
            $table->integer('cantidad');
            $table->integer('stock_resultante');
            $table->string('referencia_tipo', 30)->nullable();
            $table->unsignedInteger('referencia_id')->nullable();
            $table->text('observacion')->nullable();
            $table->timestampTz('creado_en')->useCurrent();

            $table->foreign('variante_id')->references('id')->on('variante_producto');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimiento_inventario');
        Schema::dropIfExists('abono_venta');
        Schema::dropIfExists('detalle_venta');
        Schema::dropIfExists('venta');
        Schema::dropIfExists('detalle_cotizacion');
        Schema::dropIfExists('cotizacion');
        Schema::dropIfExists('cliente');
    }
};
