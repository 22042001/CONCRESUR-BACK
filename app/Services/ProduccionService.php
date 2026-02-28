<?php

namespace App\Services;

use App\Models\OrdenProduccion;
use App\Models\RegistroProduccion;
use App\Models\Venta;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProduccionService
{
    /**
     * @param array<int, int> $personalIds
     */
    public function crearOrdenProduccion(
        int $varianteId,
        int $cantidadRequerida,
        ?int $ventaId,
        ?string $fechaEntregaRequerida,
        ?int $creadoPor,
        array $personalIds,
    ): OrdenProduccion {
        return DB::transaction(function () use ($varianteId, $cantidadRequerida, $ventaId, $fechaEntregaRequerida, $creadoPor, $personalIds): OrdenProduccion {
            $orden = OrdenProduccion::query()->create([
                'variante_id' => $varianteId,
                'venta_id' => $ventaId,
                'cantidad_requerida' => $cantidadRequerida,
                'cantidad_producida' => 0,
                'estado' => 'Pendiente',
                'fecha_entrega_requerida' => $fechaEntregaRequerida,
                'creado_por' => $creadoPor,
            ]);

            if ($personalIds !== []) {
                $orden->personal()->sync($personalIds);
            }

            if ($ventaId !== null) {
                Venta::query()->whereKey($ventaId)->update(['estado_operativo' => 'En produccion']);
            }

            return $orden->load(['personal', 'registros']);
        });
    }

    public function registrarProduccion(
        OrdenProduccion $ordenProduccion,
        int $maestroId,
        int $cantidadFabricada,
        ?string $fecha,
    ): RegistroProduccion {
        return DB::transaction(function () use ($ordenProduccion, $maestroId, $cantidadFabricada, $fecha): RegistroProduccion {
            $ordenProduccion = OrdenProduccion::query()
                ->whereKey($ordenProduccion->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($ordenProduccion->estado === 'Completada') {
                throw ValidationException::withMessages([
                    'orden' => 'No se puede registrar produccion en una orden completada.',
                ]);
            }

            $cantidadDisponible = (int) $ordenProduccion->cantidad_requerida - (int) $ordenProduccion->cantidad_producida;

            if ($cantidadFabricada > $cantidadDisponible) {
                throw ValidationException::withMessages([
                    'cantidad_fabricada' => 'La cantidad fabricada excede la cantidad pendiente de la orden.',
                ]);
            }

            $payload = [
                'orden_id' => $ordenProduccion->id,
                'maestro_id' => $maestroId,
                'cantidad_fabricada' => $cantidadFabricada,
            ];

            if ($fecha !== null) {
                $payload['fecha'] = $fecha;
            }

            return RegistroProduccion::query()->create($payload);
        });
    }
}
