<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Producto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $query = Producto::query()->with('variantes')->orderByDesc('id');

        if ($request->filled('activo')) {
            $query->where('activo', $request->boolean('activo'));
        }

        if ($request->filled('q')) {
            $search = '%'.trim((string) $request->string('q')).'%';
            $query->where(function ($subQuery) use ($search): void {
                $subQuery->where('nombre', 'like', $search)
                    ->orWhere('descripcion', 'like', $search);
            });
        }

        return $this->paginated($query->paginate($this->perPage()), 'Productos listados.');
    }

    public function show(Producto $producto): JsonResponse
    {
        return $this->success($producto->load('variantes'), 'Producto obtenido.');
    }

    public function store(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'descripcion' => ['nullable', 'string'],
            'activo' => ['nullable', 'boolean'],
        ]);

        $producto = Producto::query()->create($payload);

        return $this->success($producto, 'Producto creado.', 201);
    }

    public function update(Request $request, Producto $producto): JsonResponse
    {
        $payload = $request->validate([
            'nombre' => ['sometimes', 'required', 'string', 'max:150'],
            'descripcion' => ['nullable', 'string'],
            'activo' => ['nullable', 'boolean'],
        ]);

        $producto->fill($payload)->save();

        return $this->success($producto, 'Producto actualizado.');
    }

    public function destroy(Producto $producto): JsonResponse
    {
        $producto->activo = false;
        $producto->save();

        return $this->success($producto, 'Producto desactivado.');
    }
}
