<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $query = Cliente::query()->orderByDesc('id');

        if ($request->filled('activo')) {
            $query->where('activo', $request->boolean('activo'));
        }

        if ($request->filled('q')) {
            $search = '%'.trim((string) $request->string('q')).'%';
            $query->where(function ($subQuery) use ($search): void {
                $subQuery->where('nombre', 'like', $search)
                    ->orWhere('telefono', 'like', $search)
                    ->orWhere('direccion', 'like', $search);
            });
        }

        return $this->paginated($query->paginate($this->perPage()), 'Clientes listados.');
    }

    public function show(Cliente $cliente): JsonResponse
    {
        return $this->success($cliente, 'Cliente obtenido.');
    }

    public function store(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'direccion' => ['nullable', 'string', 'max:50'],
            'activo' => ['nullable', 'boolean'],
        ]);

        $cliente = Cliente::query()->create($payload);

        return $this->success($cliente, 'Cliente creado.', 201);
    }

    public function update(Request $request, Cliente $cliente): JsonResponse
    {
        $payload = $request->validate([
            'nombre' => ['sometimes', 'required', 'string', 'max:150'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'direccion' => ['nullable', 'string', 'max:50'],
            'activo' => ['nullable', 'boolean'],
        ]);

        $cliente->fill($payload)->save();

        return $this->success($cliente, 'Cliente actualizado.');
    }

    public function destroy(Cliente $cliente): JsonResponse
    {
        $cliente->activo = false;
        $cliente->save();

        return $this->success($cliente, 'Cliente desactivado.');
    }
}
