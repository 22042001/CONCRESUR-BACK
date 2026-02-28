<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistroProduccion extends Model
{
    use HasFactory;

    protected $table = 'registro_produccion';

    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = null;

    protected $fillable = [
        'orden_id',
        'maestro_id',
        'cantidad_fabricada',
        'fecha',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'cantidad_fabricada' => 'integer',
            'fecha' => 'date',
            'creado_en' => 'datetime',
        ];
    }

    public function orden(): BelongsTo
    {
        return $this->belongsTo(OrdenProduccion::class, 'orden_id');
    }

    public function maestro(): BelongsTo
    {
        return $this->belongsTo(Personal::class, 'maestro_id');
    }
}
