<?php

namespace App\Http\Requests\Produccion;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrdenProduccionRequest extends FormRequest
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
            'variante_id' => ['required', 'integer', 'exists:variante_producto,id'],
            'venta_id' => ['nullable', 'integer', 'exists:venta,id'],
            'cantidad_requerida' => ['required', 'integer', 'min:1'],
            'fecha_entrega_requerida' => ['nullable', 'date'],
            'creado_por' => ['nullable', 'integer', 'exists:usuario,id'],
            'personal_ids' => ['nullable', 'array'],
            'personal_ids.*' => ['integer', 'distinct', 'exists:personal,id'],
        ];
    }
}
