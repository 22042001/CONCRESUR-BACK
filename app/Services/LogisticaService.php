<?php

namespace App\Services;

use App\Models\PedidoLogistico;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LogisticaService
{
    /**
     * @return Collection<int, PedidoLogistico>
     */
    public function listarPedidos(): Collection
    {
        return PedidoLogistico::query()
            ->with(['venta.cliente', 'venta.detalles.variante.producto'])
            ->orderByDesc('id')
            ->get();
    }

    /**
     * @return array{en_espera: Collection<int, PedidoLogistico>, en_camino: Collection<int, PedidoLogistico>, entregado: Collection<int, PedidoLogistico>}
     */
    public function getKanban(): array
    {
        $baseQuery = PedidoLogistico::query()->with(['venta.cliente', 'venta.detalles.variante.producto']);

        return [
            'en_espera' => (clone $baseQuery)
                ->where('estado', PedidoLogistico::ESTADO_EN_ESPERA)
                ->orderBy('fecha_en_espera')
                ->get(),
            'en_camino' => (clone $baseQuery)
                ->where('estado', PedidoLogistico::ESTADO_EN_CAMINO)
                ->orderBy('fecha_en_camino')
                ->get(),
            'entregado' => (clone $baseQuery)
                ->where('estado', PedidoLogistico::ESTADO_ENTREGADO)
                ->where('fecha_entregado', '>=', Carbon::now()->subDays(7))
                ->orderByDesc('fecha_entregado')
                ->get(),
        ];
    }

    public function moverEstado(
        PedidoLogistico $pedidoLogistico,
        string $nuevoEstado,
        bool $actualizarObservaciones,
        ?string $observaciones = null,
        bool $actualizarUsuario = false,
        ?int $usuarioId = null,
    ): PedidoLogistico
    {
        return DB::transaction(function () use ($pedidoLogistico, $nuevoEstado, $actualizarObservaciones, $observaciones, $actualizarUsuario, $usuarioId): PedidoLogistico {
            $pedido = PedidoLogistico::query()
                ->whereKey($pedidoLogistico->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $this->esTransicionValida((string) $pedido->estado, $nuevoEstado)) {
                throw ValidationException::withMessages([
                    'estado' => sprintf('Transicion invalida de "%s" a "%s".', $pedido->estado, $nuevoEstado),
                ]);
            }

            $pedido->estado = $nuevoEstado;

            if ($actualizarObservaciones) {
                $pedido->observaciones = $observaciones;
            }

            if ($actualizarUsuario) {
                $pedido->usuario_id = $usuarioId;
            }

            $pedido->save();

            return $pedido->load(['venta.cliente', 'venta.detalles.variante.producto']);
        });
    }

    private function esTransicionValida(string $estadoActual, string $nuevoEstado): bool
    {
        return match ($estadoActual) {
            PedidoLogistico::ESTADO_EN_ESPERA => $nuevoEstado === PedidoLogistico::ESTADO_EN_CAMINO,
            PedidoLogistico::ESTADO_EN_CAMINO => $nuevoEstado === PedidoLogistico::ESTADO_ENTREGADO,
            default => false,
        };
    }
}
