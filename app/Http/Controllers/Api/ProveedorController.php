<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Proveedor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProveedorController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $query = Proveedor::query()->orderByDesc('id');

        if ($request->filled('activo')) {
            $query->where('activo', $request->boolean('activo'));
        }

        if ($request->filled('q')) {
            $search = '%'.trim((string) $request->string('q')).'%';
            $query->where(function ($subQuery) use ($search): void {
                $subQuery->where('nombre', 'like', $search)
                    ->orWhere('telefono', 'like', $search)
                    ->orWhere('observaciones', 'like', $search);
            });
        }

        return $this->paginated($query->paginate($this->perPage()), 'Proveedores listados.');
    }

    public function show(Proveedor $proveedor): JsonResponse
    {
        return $this->success($proveedor, 'Proveedor obtenido.');
    }

    public function store(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'observaciones' => ['nullable', 'string'],
            'activo' => ['nullable', 'boolean'],
        ]);

        $proveedor = Proveedor::query()->create($payload);

        return $this->success($proveedor, 'Proveedor creado.', 201);
    }

    public function update(Request $request, Proveedor $proveedor): JsonResponse
    {
        $payload = $request->validate([
            'nombre' => ['sometimes', 'required', 'string', 'max:150'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'observaciones' => ['nullable', 'string'],
            'activo' => ['nullable', 'boolean'],
        ]);

        $proveedor->fill($payload)->save();

        return $this->success($proveedor, 'Proveedor actualizado.');
    }

    public function destroy(Proveedor $proveedor): JsonResponse
    {
        $proveedor->activo = false;
        $proveedor->save();

        return $this->success($proveedor, 'Proveedor desactivado.');
    }
}
