<?php

namespace App\Http\Requests\Produccion;

use Illuminate\Foundation\Http\FormRequest;

class StoreRegistroProduccionRequest extends FormRequest
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
            'maestro_id' => ['required', 'integer', 'exists:personal,id'],
            'cantidad_fabricada' => ['required', 'integer', 'min:1'],
            'fecha' => ['nullable', 'date'],
        ];
    }
}
