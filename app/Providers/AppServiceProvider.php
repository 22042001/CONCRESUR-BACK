<?php

namespace App\Providers;

use App\Models\AbonoVenta;
use App\Models\AbonoCompra;
use App\Models\OrdenProduccion;
use App\Models\PedidoLogistico;
use App\Models\RegistroProduccion;
use App\Observers\AbonoCompraObserver;
use App\Observers\AbonoVentaObserver;
use App\Observers\OrdenProduccionObserver;
use App\Observers\PedidoLogisticoObserver;
use App\Observers\RegistroProduccionObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        AbonoVenta::observe(AbonoVentaObserver::class);
        AbonoCompra::observe(AbonoCompraObserver::class);
        RegistroProduccion::observe(RegistroProduccionObserver::class);
        OrdenProduccion::observe(OrdenProduccionObserver::class);
        PedidoLogistico::observe(PedidoLogisticoObserver::class);
    }
}
