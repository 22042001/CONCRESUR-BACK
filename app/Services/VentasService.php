<?php

namespace App\Services;

use App\Models\AbonoVenta;
use App\Models\Cotizacion;
use App\Models\DetalleCotizacion;
use App\Models\DetalleVenta;
use App\Models\MovimientoInventario;
use App\Models\VarianteProducto;
use App\Models\Venta;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VentasService
{
    /**
     * @param array<int, array{variante_id:int, cantidad:int, precio_unitario:numeric-string|int|float}> $detalles
     */
    public function crearCotizacion(int $clienteId, ?int $usuarioId, array $detalles): Cotizacion
    {
        return DB::transaction(function () use ($clienteId, $usuarioId, $detalles): Cotizacion {
            $cotizacion = Cotizacion::query()->create([
                'cliente_id' => $clienteId,
                'estado' => 'Pendiente',
                'usuario_id' => $usuarioId,
                'importe_total' => 0,
            ]);

            $importeTotal = 0;

            foreach ($detalles as $detalle) {
                $cantidad = (int) $detalle['cantidad'];
                $precioUnitario = (float) $detalle['precio_unitario'];
                $subtotal = $cantidad * $precioUnitario;
                $importeTotal += $subtotal;

                DetalleCotizacion::query()->create([
                    'cotizacion_id' => $cotizacion->id,
                    'variante_id' => $detalle['variante_id'],
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precioUnitario,
                    'subtotal' => $subtotal,
                ]);
            }

            $cotizacion->importe_total = $importeTotal;
            $cotizacion->save();

            return $cotizacion->load('detalles');
        });
    }

    public function confirmarCotizacion(Cotizacion $cotizacion, string $metodoPago, string $formaPago, ?string $fechaEntrega = null): Venta
    {
        return DB::transaction(function () use ($cotizacion, $metodoPago, $formaPago, $fechaEntrega): Venta {
            $cotizacion = Cotizacion::query()
                ->whereKey($cotizacion->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($cotizacion->estado !== 'Pendiente') {
                throw ValidationException::withMessages([
                    'cotizacion' => 'Solo se puede confirmar una cotizacion pendiente.',
                ]);
            }

            $detallesCotizacion = $cotizacion->detalles()->get();

            if ($detallesCotizacion->isEmpty()) {
                throw ValidationException::withMessages([
                    'detalles' => 'La cotizacion no tiene detalles para confirmar.',
                ]);
            }

            $venta = Venta::query()->create([
                'cliente_id' => $cotizacion->cliente_id,
                'cotizacion_id' => $cotizacion->id,
                'metodo_pago' => $metodoPago,
                'forma_pago' => $formaPago,
                'fecha_entrega' => $fechaEntrega,
                'importe_total' => $cotizacion->importe_total,
                'adelanto' => 0,
                'saldo_total' => $cotizacion->importe_total,
                'estado_operativo' => 'Confirmada',
                'estado_financiero' => 'Pendiente',
                'usuario_id' => $cotizacion->usuario_id,
            ]);

            foreach ($detallesCotizacion as $detalleCotizacion) {
                $this->descontarStockYCrearDetalleVenta(
                    $venta,
                    $detalleCotizacion->variante_id,
                    (int) $detalleCotizacion->cantidad,
                    (float) $detalleCotizacion->precio_unitario
                );
            }

            $cotizacion->estado = 'Confirmada';
            $cotizacion->confirmada_en = Carbon::now();
            $cotizacion->venta_id = $venta->id;
            $cotizacion->save();

            return $venta->load('detalles');
        });
    }

    /**
     * @param array<int, array{variante_id:int, cantidad:int, precio_unitario?:numeric-string|int|float}> $detalles
     */
    public function crearVentaDirecta(
        int $clienteId,
        string $metodoPago,
        string $formaPago,
        array $detalles,
        ?int $usuarioId,
        ?string $fechaEntrega = null,
    ): Venta {
        return DB::transaction(function () use ($clienteId, $metodoPago, $formaPago, $detalles, $usuarioId, $fechaEntrega): Venta {
            $venta = Venta::query()->create([
                'cliente_id' => $clienteId,
                'metodo_pago' => $metodoPago,
                'forma_pago' => $formaPago,
                'fecha_entrega' => $fechaEntrega,
                'importe_total' => 0,
                'adelanto' => 0,
                'saldo_total' => 0,
                'estado_operativo' => 'Confirmada',
                'estado_financiero' => 'Pendiente',
                'usuario_id' => $usuarioId,
            ]);

            $importeTotal = 0;

            foreach ($detalles as $detalle) {
                $variante = VarianteProducto::query()->findOrFail($detalle['variante_id']);
                $precioUnitario = isset($detalle['precio_unitario'])
                    ? (float) $detalle['precio_unitario']
                    : (float) $variante->precio_venta;

                $importeTotal += $this->descontarStockYCrearDetalleVenta(
                    $venta,
                    $variante->id,
                    (int) $detalle['cantidad'],
                    $precioUnitario
                );
            }

            $venta->importe_total = $importeTotal;
            $venta->saldo_total = $importeTotal;
            $venta->save();

            return $venta->load('detalles');
        });
    }

    public function registrarAbono(Venta $venta, float $monto, string $formaPago, ?int $usuarioId): AbonoVenta
    {
        return DB::transaction(function () use ($venta, $monto, $formaPago, $usuarioId): AbonoVenta {
            $venta = Venta::query()->whereKey($venta->id)->lockForUpdate()->firstOrFail();

            return AbonoVenta::query()->create([
                'venta_id' => $venta->id,
                'monto' => $monto,
                'forma_pago' => $formaPago,
                'usuario_id' => $usuarioId,
            ]);
        });
    }

    private function descontarStockYCrearDetalleVenta(Venta $venta, int $varianteId, int $cantidad, float $precioUnitario): float
    {
        $variante = VarianteProducto::query()
            ->whereKey($varianteId)
            ->lockForUpdate()
            ->firstOrFail();

        if ($variante->stock_actual < $cantidad) {
            throw ValidationException::withMessages([
                'stock' => sprintf('Stock insuficiente para variante %d.', $variante->id),
            ]);
        }

        $subtotal = $cantidad * $precioUnitario;
        $stockResultante = $variante->stock_actual - $cantidad;

        $detalleVenta = DetalleVenta::query()->create([
            'venta_id' => $venta->id,
            'variante_id' => $variante->id,
            'cantidad' => $cantidad,
            'precio_unitario' => $precioUnitario,
            'subtotal' => $subtotal,
        ]);

        $variante->stock_actual = $stockResultante;
        $variante->save();

        MovimientoInventario::query()->create([
            'variante_id' => $variante->id,
            'tipo' => 'SALIDA',
            'cantidad' => $cantidad,
            'stock_resultante' => $stockResultante,
            'referencia_tipo' => 'detalle_venta',
            'referencia_id' => $detalleVenta->id,
        ]);

        return $subtotal;
    }
}
