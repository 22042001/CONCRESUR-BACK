<?php

namespace App\Observers;

use App\Models\PedidoLogistico;
use Illuminate\Support\Carbon;

class PedidoLogisticoObserver
{
    public function updated(PedidoLogistico $pedidoLogistico): void
    {
        if (! $pedidoLogistico->wasChanged('estado')) {
            return;
        }

        if ($pedidoLogistico->estado === PedidoLogistico::ESTADO_EN_CAMINO) {
            $pedidoLogistico->fecha_en_camino = Carbon::now();
            $pedidoLogistico->saveQuietly();

            $pedidoLogistico->venta()
                ->update(['estado_operativo' => PedidoLogistico::ESTADO_EN_CAMINO]);

            return;
        }

        if ($pedidoLogistico->estado === PedidoLogistico::ESTADO_ENTREGADO) {
            $ahora = Carbon::now();

            $pedidoLogistico->fecha_entregado = $ahora;
            $pedidoLogistico->saveQuietly();

            $pedidoLogistico->venta()->update([
                'estado_operativo' => 'Completada',
                'completada_en' => $ahora,
            ]);
        }
    }
}
