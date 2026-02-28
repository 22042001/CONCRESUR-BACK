<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('cotizacion', function (Blueprint $table): void {
            $table->foreign('venta_id', 'fk_cotizacion_venta')->references('id')->on('venta');
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('cotizacion', function (Blueprint $table): void {
            $table->dropForeign('fk_cotizacion_venta');
        });
    }
};
