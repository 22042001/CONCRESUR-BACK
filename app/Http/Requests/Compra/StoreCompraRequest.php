<?php

namespace App\Http\Requests\Compra;

use Illuminate\Foundation\Http\FormRequest;

class StoreCompraRequest extends FormRequest
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
            'categoria_id' => ['required', 'integer', 'exists:categoria_compra,id'],
            'proveedor_id' => ['nullable', 'integer', 'exists:proveedor,id'],
            'usuario_id' => ['nullable', 'integer', 'exists:usuario,id'],
            'metodo_pago' => ['required', 'in:Contado,Credito'],
            'forma_pago' => ['required', 'in:Efectivo,QR'],
            'detalles' => ['required', 'array', 'min:1'],
            'detalles.*.descripcion' => ['required', 'string', 'max:200'],
            'detalles.*.cantidad' => ['nullable', 'numeric', 'gt:0'],
            'detalles.*.unidad_medida' => ['nullable', 'string', 'max:30'],
            'detalles.*.precio_unitario' => ['required', 'numeric', 'min:0'],
        ];
    }
}
