<?php

namespace App\Http\Requests\Compra;

use Illuminate\Foundation\Http\FormRequest;

class StoreAbonoCompraRequest extends FormRequest
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
            'monto' => ['required', 'numeric', 'gt:0'],
            'forma_pago' => ['required', 'in:Efectivo,QR'],
            'usuario_id' => ['nullable', 'integer', 'exists:usuario,id'],
        ];
    }
}
