<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Logistica\MoverEstadoPedidoLogisticoRequest;
use App\Models\PedidoLogistico;
use App\Services\LogisticaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LogisticaController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly LogisticaService $logisticaService)
    {
    }

    public function kanban(): JsonResponse
    {
        return $this->success($this->logisticaService->getKanban(), 'Tablero logistico obtenido.');
    }

    public function index(Request $request): JsonResponse
    {
        $query = PedidoLogistico::query()
            ->with(['venta.cliente', 'venta.detalles.variante.producto'])
            ->orderByDesc('id');

        if ($request->filled('estado')) {
            $query->where('estado', (string) $request->string('estado'));
        }

        if ($request->filled('q')) {
            $search = '%'.trim((string) $request->string('q')).'%';
            $query->whereHas('venta', static function ($ventaQuery) use ($search): void {
                $ventaQuery->where('numero', 'like', $search);
            });
        }

        return $this->paginated($query->paginate($this->perPage()), 'Pedidos logisticos listados.');
    }

    public function show(PedidoLogistico $pedidoLogistico): JsonResponse
    {
        return $this->success($pedidoLogistico->load(['venta.cliente', 'venta.detalles.variante.producto']), 'Pedido logistico obtenido.');
    }

    public function updateEstado(MoverEstadoPedidoLogisticoRequest $request, PedidoLogistico $pedidoLogistico): JsonResponse
    {
        $pedidoActualizado = $this->logisticaService->moverEstado(
            $pedidoLogistico,
            (string) $request->string('estado'),
            $request->exists('observaciones'),
            $request->string('observaciones')->toString() ?: null,
            $request->exists('usuario_id') || $request->user() !== null,
            $request->integer('usuario_id') ?: $request->user()?->id,
        );

        return $this->success($pedidoActualizado, 'Estado logistico actualizado.');
    }
}
