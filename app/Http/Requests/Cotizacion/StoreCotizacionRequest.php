<?php

namespace App\Http\Requests\Cotizacion;

use Illuminate\Foundation\Http\FormRequest;

class StoreCotizacionRequest extends FormRequest
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
            'cliente_id' => ['required', 'integer', 'exists:cliente,id'],
            'usuario_id' => ['nullable', 'integer', 'exists:usuario,id'],
            'detalles' => ['required', 'array', 'min:1'],
            'detalles.*.variante_id' => ['required', 'integer', 'exists:variante_producto,id'],
            'detalles.*.cantidad' => ['required', 'integer', 'min:1'],
            'detalles.*.precio_unitario' => ['required', 'numeric', 'min:0'],
        ];
    }
}
