<?php

namespace App\Services;

use App\Models\AbonoCompra;
use App\Models\Compra;
use App\Models\DetalleCompra;
use Illuminate\Support\Facades\DB;

class ComprasService
{
    /**
     * @param array<int, array{descripcion:string, cantidad?:numeric-string|int|float|null, unidad_medida?:string|null, precio_unitario:numeric-string|int|float}> $detalles
     */
    public function crearCompra(
        int $categoriaId,
        ?int $proveedorId,
        string $metodoPago,
        string $formaPago,
        array $detalles,
        ?int $usuarioId,
    ): Compra {
        return DB::transaction(function () use ($categoriaId, $proveedorId, $metodoPago, $formaPago, $detalles, $usuarioId): Compra {
            $compra = Compra::query()->create([
                'categoria_id' => $categoriaId,
                'proveedor_id' => $proveedorId,
                'metodo_pago' => $metodoPago,
                'forma_pago' => $formaPago,
                'importe_total' => 0,
                'adelanto' => 0,
                'saldo_total' => 0,
                'estado_financiero' => $metodoPago === 'Contado' ? 'Pagado' : 'Pendiente',
                'usuario_id' => $usuarioId,
            ]);

            $importeTotal = 0;

            foreach ($detalles as $detalle) {
                $cantidad = array_key_exists('cantidad', $detalle) && $detalle['cantidad'] !== null
                    ? (float) $detalle['cantidad']
                    : 1.0;

                $precioUnitario = (float) $detalle['precio_unitario'];
                $subtotal = $cantidad * $precioUnitario;
                $importeTotal += $subtotal;

                DetalleCompra::query()->create([
                    'compra_id' => $compra->id,
                    'descripcion' => $detalle['descripcion'],
                    'cantidad' => array_key_exists('cantidad', $detalle) ? $detalle['cantidad'] : null,
                    'unidad_medida' => $detalle['unidad_medida'] ?? null,
                    'precio_unitario' => $precioUnitario,
                    'subtotal' => $subtotal,
                ]);
            }

            $compra->importe_total = $importeTotal;

            if ($metodoPago === 'Contado') {
                $compra->adelanto = $importeTotal;
                $compra->saldo_total = 0;
            } else {
                $compra->adelanto = 0;
                $compra->saldo_total = $importeTotal;
            }

            $compra->save();

            return $compra->load('detalles');
        });
    }

    public function registrarAbono(Compra $compra, float $monto, string $formaPago, ?int $usuarioId): AbonoCompra
    {
        return DB::transaction(function () use ($compra, $monto, $formaPago, $usuarioId): AbonoCompra {
            $compra = Compra::query()->whereKey($compra->id)->lockForUpdate()->firstOrFail();

            return AbonoCompra::query()->create([
                'compra_id' => $compra->id,
                'monto' => $monto,
                'forma_pago' => $formaPago,
                'usuario_id' => $usuarioId,
            ]);
        });
    }
}
