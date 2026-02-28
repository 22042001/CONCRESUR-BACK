<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class CorrelativoService
{
    public function siguienteNumero(string $tabla, string $prefijo): string
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement(sprintf('LOCK TABLE %s IN EXCLUSIVE MODE', $tabla));
        }

        $ultimoNumero = DB::table($tabla)
            ->select('numero')
            ->orderByDesc('id')
            ->lockForUpdate()
            ->value('numero');

        $correlativo = $ultimoNumero === null
            ? 1
            : ((int) preg_replace('/\D/', '', $ultimoNumero)) + 1;

        return sprintf('%s-%04d', $prefijo, $correlativo);
    }
}
