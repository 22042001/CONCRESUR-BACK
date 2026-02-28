<?php

namespace App\Http\Requests\Logistica;

use App\Models\PedidoLogistico;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MoverEstadoPedidoLogisticoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'estado' => ['required', Rule::in([PedidoLogistico::ESTADO_EN_CAMINO, PedidoLogistico::ESTADO_ENTREGADO])],
            'observaciones' => ['nullable', 'string'],
            'usuario_id' => ['nullable', 'integer', 'exists:usuario,id'],
        ];
    }
}
