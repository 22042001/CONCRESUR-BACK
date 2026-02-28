<?php

namespace App\Http\Requests\Cotizacion;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmCotizacionRequest extends FormRequest
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
            'metodo_pago' => ['required', 'in:Contado,Credito'],
            'forma_pago' => ['required', 'in:Efectivo,QR'],
            'fecha_entrega' => ['nullable', 'date'],
        ];
    }
}
