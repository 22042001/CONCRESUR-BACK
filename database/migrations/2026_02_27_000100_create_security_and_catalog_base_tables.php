<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rol', function (Blueprint $table): void {
            $table->smallIncrements('id');
            $table->string('nombre', 50)->unique();
        });

        Schema::create('permiso', function (Blueprint $table): void {
            $table->smallIncrements('id');
            $table->string('clave', 50)->unique();
            $table->string('nombre', 80);
        });

        Schema::create('usuario', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('nombre', 120);
            $table->string('email', 150)->nullable()->unique();
            $table->text('password_hash');
            $table->unsignedSmallInteger('rol_id');
            $table->boolean('activo')->default(true);
            $table->timestampTz('creado_en')->useCurrent();

            $table->foreign('rol_id')->references('id')->on('rol');
        });

        Schema::create('rol_permiso', function (Blueprint $table): void {
            $table->unsignedSmallInteger('rol_id');
            $table->unsignedSmallInteger('permiso_id');

            $table->primary(['rol_id', 'permiso_id']);
            $table->foreign('rol_id')->references('id')->on('rol')->cascadeOnDelete();
            $table->foreign('permiso_id')->references('id')->on('permiso')->cascadeOnDelete();
        });

        Schema::create('personal', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('nombre', 120);
            $table->string('telefono', 20)->nullable();
            $table->string('tipo', 30);
            $table->boolean('activo')->default(true);
            $table->timestampTz('creado_en')->useCurrent();
        });

        Schema::create('producto', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('nombre', 150);
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestampTz('creado_en')->useCurrent();
        });

        Schema::create('variante_producto', function (Blueprint $table): void {
            $table->increments('id');
            $table->unsignedInteger('producto_id');
            $table->string('nombre', 150);
            $table->string('unidad_medida', 30)->default('unidad');
            $table->decimal('precio_venta', 12, 2)->default(0);
            $table->integer('stock_actual')->default(0);
            $table->integer('stock_minimo')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestampTz('creado_en')->useCurrent();

            $table->foreign('producto_id')->references('id')->on('producto');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('variante_producto');
        Schema::dropIfExists('producto');
        Schema::dropIfExists('personal');
        Schema::dropIfExists('rol_permiso');
        Schema::dropIfExists('usuario');
        Schema::dropIfExists('permiso');
        Schema::dropIfExists('rol');
    }
};
