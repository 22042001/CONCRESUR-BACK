<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\VarianteProducto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VarianteProductoController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $query = VarianteProducto::query()->with('producto')->orderByDesc('id');

        if ($request->filled('producto_id')) {
            $query->where('producto_id', (int) $request->integer('producto_id'));
        }

        if ($request->filled('activo')) {
            $query->where('activo', $request->boolean('activo'));
        }

        if ($request->filled('q')) {
            $search = '%'.trim((string) $request->string('q')).'%';
            $query->where('nombre', 'like', $search);
        }

        return $this->paginated($query->paginate($this->perPage()), 'Variantes de producto listadas.');
    }

    public function show(VarianteProducto $varianteProducto): JsonResponse
    {
        return $this->success($varianteProducto->load('producto'), 'Variante de producto obtenida.');
    }

    public function store(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'producto_id' => ['required', 'integer', 'exists:producto,id'],
            'nombre' => ['required', 'string', 'max:150'],
            'unidad_medida' => ['nullable', 'string', 'max:30'],
            'precio_venta' => ['nullable', 'numeric', 'min:0'],
            'stock_actual' => ['nullable', 'integer', 'min:0'],
            'stock_minimo' => ['nullable', 'integer', 'min:0'],
            'activo' => ['nullable', 'boolean'],
        ]);

        $variante = VarianteProducto::query()->create($payload);

        return $this->success($variante->load('producto'), 'Variante de producto creada.', 201);
    }

    public function update(Request $request, VarianteProducto $varianteProducto): JsonResponse
    {
        $payload = $request->validate([
            'producto_id' => ['sometimes', 'required', 'integer', 'exists:producto,id'],
            'nombre' => ['sometimes', 'required', 'string', 'max:150'],
            'unidad_medida' => ['nullable', 'string', 'max:30'],
            'precio_venta' => ['nullable', 'numeric', 'min:0'],
            'stock_actual' => ['nullable', 'integer', 'min:0'],
            'stock_minimo' => ['nullable', 'integer', 'min:0'],
            'activo' => ['nullable', 'boolean'],
        ]);

        $varianteProducto->fill($payload)->save();

        return $this->success($varianteProducto->load('producto'), 'Variante de producto actualizada.');
    }

    public function destroy(VarianteProducto $varianteProducto): JsonResponse
    {
        $varianteProducto->activo = false;
        $varianteProducto->save();

        return $this->success($varianteProducto->load('producto'), 'Variante de producto desactivada.');
    }
}
