<?php

namespace App\Observers;

use App\Models\OrdenProduccion;
use App\Models\PedidoLogistico;
use App\Models\Venta;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class OrdenProduccionObserver
{
    public function updated(OrdenProduccion $ordenProduccion): void
    {
        if (! $ordenProduccion->wasChanged('estado')) {
            return;
        }

        if ($ordenProduccion->estado !== 'Completada') {
            return;
        }

        if ($ordenProduccion->venta_id === null) {
            return;
        }

        try {
            DB::transaction(function () use ($ordenProduccion): void {
                $pedidoExiste = PedidoLogistico::query()
                    ->where('venta_id', $ordenProduccion->venta_id)
                    ->lockForUpdate()
                    ->exists();

                if (! $pedidoExiste) {
                    PedidoLogistico::query()->create([
                        'venta_id' => $ordenProduccion->venta_id,
                        'estado' => 'En espera',
                        'fecha_en_espera' => Carbon::now(),
                        'usuario_id' => $ordenProduccion->creado_por,
                    ]);
                }

                Venta::query()
                    ->whereKey($ordenProduccion->venta_id)
                    ->update(['estado_operativo' => 'Lista']);
            });
        } catch (Throwable $exception) {
            Log::error('No se pudo crear pedido logístico al completar orden.', [
                'orden_id' => $ordenProduccion->id,
                'venta_id' => $ordenProduccion->venta_id,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
