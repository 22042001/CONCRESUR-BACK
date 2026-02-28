<?php

namespace App\Observers;

use App\Models\MovimientoInventario;
use App\Models\OrdenProduccion;
use App\Models\RegistroProduccion;
use App\Models\VarianteProducto;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class RegistroProduccionObserver
{
    public function created(RegistroProduccion $registroProduccion): void
    {
        DB::transaction(function () use ($registroProduccion): void {
            $orden = OrdenProduccion::query()
                ->whereKey($registroProduccion->orden_id)
                ->lockForUpdate()
                ->firstOrFail();

            $variante = VarianteProducto::query()
                ->whereKey($orden->variante_id)
                ->lockForUpdate()
                ->firstOrFail();

            $nuevoStock = $variante->stock_actual + $registroProduccion->cantidad_fabricada;
            $variante->stock_actual = $nuevoStock;
            $variante->save();

            MovimientoInventario::query()->create([
                'variante_id' => $variante->id,
                'tipo' => 'ENTRADA',
                'cantidad' => $registroProduccion->cantidad_fabricada,
                'stock_resultante' => $nuevoStock,
                'referencia_tipo' => 'registro_produccion',
                'referencia_id' => $registroProduccion->id,
            ]);

            $orden->cantidad_producida = (int) $orden->cantidad_producida + (int) $registroProduccion->cantidad_fabricada;

            if ($orden->cantidad_producida >= $orden->cantidad_requerida) {
                $orden->estado = 'Completada';
                $orden->completada_en = Carbon::now();
            } elseif ($orden->cantidad_producida > 0 && $orden->estado === 'Pendiente') {
                $orden->estado = 'En proceso';
            }

            $orden->save();
        });
    }
}
