<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Produccion\StoreOrdenProduccionRequest;
use App\Http\Requests\Produccion\StoreRegistroProduccionRequest;
use App\Models\OrdenProduccion;
use App\Services\ProduccionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrdenProduccionController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly ProduccionService $produccionService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $query = OrdenProduccion::query()
            ->with(['venta', 'variante', 'personal', 'registros'])
            ->orderByDesc('id');

        if ($request->filled('estado')) {
            $query->where('estado', (string) $request->string('estado'));
        }

        if ($request->filled('venta_id')) {
            $query->where('venta_id', (int) $request->integer('venta_id'));
        }

        if ($request->filled('variante_id')) {
            $query->where('variante_id', (int) $request->integer('variante_id'));
        }

        if ($request->filled('q')) {
            $search = '%'.trim((string) $request->string('q')).'%';
            $query->where('numero', 'like', $search);
        }

        return $this->paginated($query->paginate($this->perPage()), 'Ordenes de produccion listadas.');
    }

    public function show(OrdenProduccion $ordenProduccion): JsonResponse
    {
        return $this->success($ordenProduccion->load(['venta', 'variante', 'personal', 'registros']), 'Orden de produccion obtenida.');
    }

    public function store(StoreOrdenProduccionRequest $request): JsonResponse
    {
        $orden = $this->produccionService->crearOrdenProduccion(
            (int) $request->integer('variante_id'),
            (int) $request->integer('cantidad_requerida'),
            $request->integer('venta_id') ?: null,
            $request->string('fecha_entrega_requerida')->toString() ?: null,
            $request->integer('creado_por') ?: $request->user()?->id,
            $request->array('personal_ids'),
        );

        return $this->success($orden, 'Orden de produccion creada.', 201);
    }

    public function storeRegistro(StoreRegistroProduccionRequest $request, OrdenProduccion $ordenProduccion): JsonResponse
    {
        $registro = $this->produccionService->registrarProduccion(
            $ordenProduccion,
            (int) $request->integer('maestro_id'),
            (int) $request->integer('cantidad_fabricada'),
            $request->string('fecha')->toString() ?: null,
        );

        return $this->success($registro, 'Registro de produccion creado.', 201);
    }
}
