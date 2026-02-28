<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\CategoriaCompra;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoriaCompraController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $query = CategoriaCompra::query()->orderBy('nombre');

        if ($request->filled('q')) {
            $search = '%'.trim((string) $request->string('q')).'%';
            $query->where('nombre', 'like', $search);
        }

        return $this->paginated($query->paginate($this->perPage()), 'Categorias de compra listadas.');
    }

    public function show(CategoriaCompra $categoriaCompra): JsonResponse
    {
        return $this->success($categoriaCompra, 'Categoria de compra obtenida.');
    }

    public function store(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'nombre' => ['required', 'string', 'max:80', 'unique:categoria_compra,nombre'],
        ]);

        $categoria = CategoriaCompra::query()->create($payload);

        return $this->success($categoria, 'Categoria de compra creada.', 201);
    }

    public function update(Request $request, CategoriaCompra $categoriaCompra): JsonResponse
    {
        $payload = $request->validate([
            'nombre' => [
                'sometimes',
                'required',
                'string',
                'max:80',
                Rule::unique('categoria_compra', 'nombre')->ignore($categoriaCompra->id),
            ],
        ]);

        $categoriaCompra->fill($payload)->save();

        return $this->success($categoriaCompra, 'Categoria de compra actualizada.');
    }
}
