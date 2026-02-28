<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Venta\StoreAbonoVentaRequest;
use App\Http\Requests\Venta\StoreVentaDirectaRequest;
use App\Models\Venta;
use App\Services\VentasService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VentaController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly VentasService $ventasService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $query = Venta::query()
            ->with(['cliente', 'detalles', 'abonos'])
            ->orderByDesc('id');

        if ($request->filled('estado_operativo')) {
            $query->where('estado_operativo', (string) $request->string('estado_operativo'));
        }

        if ($request->filled('estado_financiero')) {
            $query->where('estado_financiero', (string) $request->string('estado_financiero'));
        }

        if ($request->filled('cliente_id')) {
            $query->where('cliente_id', (int) $request->integer('cliente_id'));
        }

        if ($request->filled('q')) {
            $search = '%'.trim((string) $request->string('q')).'%';
            $query->where('numero', 'like', $search);
        }

        return $this->paginated($query->paginate($this->perPage()), 'Ventas listadas.');
    }

    public function show(Venta $venta): JsonResponse
    {
        return $this->success($venta->load(['cliente', 'detalles', 'abonos']), 'Venta obtenida.');
    }

    public function storeDirecta(StoreVentaDirectaRequest $request): JsonResponse
    {
        $venta = $this->ventasService->crearVentaDirecta(
            (int) $request->integer('cliente_id'),
            (string) $request->string('metodo_pago'),
            (string) $request->string('forma_pago'),
            $request->array('detalles'),
            $request->integer('usuario_id') ?: $request->user()?->id,
            $request->string('fecha_entrega')->toString() ?: null,
        );

        return $this->success($venta, 'Venta creada.', 201);
    }

    public function storeAbono(StoreAbonoVentaRequest $request, Venta $venta): JsonResponse
    {
        $abono = $this->ventasService->registrarAbono(
            $venta,
            (float) $request->input('monto'),
            (string) $request->string('forma_pago'),
            $request->integer('usuario_id') ?: $request->user()?->id,
        );

        return $this->success($abono, 'Abono registrado.', 201);
    }
}
