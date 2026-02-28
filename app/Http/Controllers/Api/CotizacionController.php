<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Cotizacion\ConfirmCotizacionRequest;
use App\Http\Requests\Cotizacion\StoreCotizacionRequest;
use App\Models\Cotizacion;
use App\Services\VentasService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CotizacionController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly VentasService $ventasService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $query = Cotizacion::query()->with(['cliente', 'detalles'])->orderByDesc('id');

        if ($request->filled('estado')) {
            $query->where('estado', (string) $request->string('estado'));
        }

        if ($request->filled('cliente_id')) {
            $query->where('cliente_id', (int) $request->integer('cliente_id'));
        }

        if ($request->filled('q')) {
            $search = '%'.trim((string) $request->string('q')).'%';
            $query->where('numero', 'like', $search);
        }

        return $this->paginated($query->paginate($this->perPage()), 'Cotizaciones listadas.');
    }

    public function store(StoreCotizacionRequest $request): JsonResponse
    {
        $cotizacion = $this->ventasService->crearCotizacion(
            (int) $request->integer('cliente_id'),
            $request->integer('usuario_id') ?: $request->user()?->id,
            $request->array('detalles')
        );

        return $this->success($cotizacion, 'Cotizacion creada.', 201);
    }

    public function show(Cotizacion $cotizacion): JsonResponse
    {
        return $this->success($cotizacion->load(['cliente', 'detalles']), 'Cotizacion obtenida.');
    }

    public function confirmar(ConfirmCotizacionRequest $request, Cotizacion $cotizacion): JsonResponse
    {
        $venta = $this->ventasService->confirmarCotizacion(
            $cotizacion,
            (string) $request->string('metodo_pago'),
            (string) $request->string('forma_pago'),
            $request->string('fecha_entrega')->toString() ?: null,
        );

        return $this->success($venta, 'Cotizacion confirmada y venta generada.');
    }
}
