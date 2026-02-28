<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Personal extends Model
{
    use HasFactory;

    protected $table = 'personal';

    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = null;

    protected $fillable = [
        'nombre',
        'telefono',
        'tipo',
        'activo',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'creado_en' => 'datetime',
        ];
    }

    public function ordenesProduccion(): BelongsToMany
    {
        return $this->belongsToMany(OrdenProduccion::class, 'orden_produccion_personal', 'personal_id', 'orden_id');
    }

    public function registrosProduccionComoMaestro(): HasMany
    {
        return $this->hasMany(RegistroProduccion::class, 'maestro_id');
    }

    public function jornales(): HasMany
    {
        return $this->hasMany(RegistroJornal::class, 'personal_id');
    }
}
