<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Compra\StoreAbonoCompraRequest;
use App\Http\Requests\Compra\StoreCompraRequest;
use App\Models\Compra;
use App\Services\ComprasService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompraController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly ComprasService $comprasService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $query = Compra::query()
            ->with(['categoria', 'proveedor', 'detalles', 'abonos'])
            ->orderByDesc('id');

        if ($request->filled('estado_financiero')) {
            $query->where('estado_financiero', (string) $request->string('estado_financiero'));
        }

        if ($request->filled('categoria_id')) {
            $query->where('categoria_id', (int) $request->integer('categoria_id'));
        }

        if ($request->filled('proveedor_id')) {
            $query->where('proveedor_id', (int) $request->integer('proveedor_id'));
        }

        if ($request->filled('q')) {
            $search = '%'.trim((string) $request->string('q')).'%';
            $query->where('numero', 'like', $search);
        }

        return $this->paginated($query->paginate($this->perPage()), 'Compras listadas.');
    }

    public function show(Compra $compra): JsonResponse
    {
        return $this->success($compra->load(['categoria', 'proveedor', 'detalles', 'abonos']), 'Compra obtenida.');
    }

    public function store(StoreCompraRequest $request): JsonResponse
    {
        $compra = $this->comprasService->crearCompra(
            (int) $request->integer('categoria_id'),
            $request->integer('proveedor_id') ?: null,
            (string) $request->string('metodo_pago'),
            (string) $request->string('forma_pago'),
            $request->array('detalles'),
            $request->integer('usuario_id') ?: $request->user()?->id,
        );

        return $this->success($compra, 'Compra creada.', 201);
    }

    public function storeAbono(StoreAbonoCompraRequest $request, Compra $compra): JsonResponse
    {
        $abono = $this->comprasService->registrarAbono(
            $compra,
            (float) $request->input('monto'),
            (string) $request->string('forma_pago'),
            $request->integer('usuario_id') ?: $request->user()?->id,
        );

        return $this->success($abono, 'Abono registrado.', 201);
    }
}
