<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Personal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PersonalController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $query = Personal::query()->orderByDesc('id');

        if ($request->filled('tipo')) {
            $query->where('tipo', (string) $request->string('tipo'));
        }

        if ($request->filled('activo')) {
            $query->where('activo', $request->boolean('activo'));
        }

        if ($request->filled('q')) {
            $search = '%'.trim((string) $request->string('q')).'%';
            $query->where(function ($subQuery) use ($search): void {
                $subQuery->where('nombre', 'like', $search)
                    ->orWhere('telefono', 'like', $search);
            });
        }

        return $this->paginated($query->paginate($this->perPage()), 'Personal listado.');
    }

    public function show(Personal $personal): JsonResponse
    {
        return $this->success($personal, 'Personal obtenido.');
    }

    public function store(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'nombre' => ['required', 'string', 'max:120'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'tipo' => ['required', Rule::in(['maestro', 'ayudante'])],
            'activo' => ['nullable', 'boolean'],
        ]);

        $personal = Personal::query()->create($payload);

        return $this->success($personal, 'Personal creado.', 201);
    }

    public function update(Request $request, Personal $personal): JsonResponse
    {
        $payload = $request->validate([
            'nombre' => ['sometimes', 'required', 'string', 'max:120'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'tipo' => ['sometimes', 'required', Rule::in(['maestro', 'ayudante'])],
            'activo' => ['nullable', 'boolean'],
        ]);

        $personal->fill($payload)->save();

        return $this->success($personal, 'Personal actualizado.');
    }

    public function destroy(Personal $personal): JsonResponse
    {
        $personal->activo = false;
        $personal->save();

        return $this->success($personal, 'Personal desactivado.');
    }
}
