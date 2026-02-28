<?php

namespace App\Http\Requests\Venta;

use Illuminate\Foundation\Http\FormRequest;

class StoreVentaDirectaRequest extends FormRequest
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
            'metodo_pago' => ['required', 'in:Contado,Credito'],
            'forma_pago' => ['required', 'in:Efectivo,QR'],
            'fecha_entrega' => ['nullable', 'date'],
            'detalles' => ['required', 'array', 'min:1'],
            'detalles.*.variante_id' => ['required', 'integer', 'exists:variante_producto,id'],
            'detalles.*.cantidad' => ['required', 'integer', 'min:1'],
            'detalles.*.precio_unitario' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
