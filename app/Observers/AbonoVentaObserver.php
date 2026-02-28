<?php

namespace App\Observers;

use App\Models\AbonoVenta;
use App\Models\Venta;
use Illuminate\Support\Facades\DB;

class AbonoVentaObserver
{
    public function created(AbonoVenta $abonoVenta): void
    {
        DB::transaction(function () use ($abonoVenta): void {
            $venta = Venta::query()
                ->whereKey($abonoVenta->venta_id)
                ->lockForUpdate()
                ->firstOrFail();

            $adelanto = (float) Venta::query()
                ->whereKey($venta->id)
                ->firstOrFail()
                ->abonos()
                ->sum('monto');

            $saldoTotal = (float) $venta->importe_total - $adelanto;

            $venta->adelanto = $adelanto;
            $venta->saldo_total = $saldoTotal;

            if ($saldoTotal <= 0) {
                $venta->estado_financiero = 'Pagado';
            }

            $venta->save();
        });
    }
}
