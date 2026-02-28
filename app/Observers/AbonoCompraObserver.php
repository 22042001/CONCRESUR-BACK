<?php

namespace App\Observers;

use App\Models\AbonoCompra;
use App\Models\Compra;
use Illuminate\Support\Facades\DB;

class AbonoCompraObserver
{
    public function created(AbonoCompra $abonoCompra): void
    {
        DB::transaction(function () use ($abonoCompra): void {
            $compra = Compra::query()
                ->whereKey($abonoCompra->compra_id)
                ->lockForUpdate()
                ->firstOrFail();

            $adelanto = (float) AbonoCompra::query()
                ->where('compra_id', $compra->id)
                ->sum('monto');

            $saldoTotal = (float) $compra->importe_total - $adelanto;

            $compra->adelanto = $adelanto;
            $compra->saldo_total = $saldoTotal;
            $compra->estado_financiero = $saldoTotal <= 0 ? 'Pagado' : 'Pendiente';
            $compra->save();
        });
    }
}
