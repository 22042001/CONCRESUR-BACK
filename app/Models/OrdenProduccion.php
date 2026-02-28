<?php

namespace App\Models;

use App\Services\CorrelativoService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrdenProduccion extends Model
{
    use HasFactory;

    protected $table = 'orden_produccion';

    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = null;

    protected $fillable = [
        'numero',
        'variante_id',
        'venta_id',
        'cantidad_requerida',
        'cantidad_producida',
        'estado',
        'fecha_entrega_requerida',
        'completada_en',
        'creado_por',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'cantidad_requerida' => 'integer',
            'cantidad_producida' => 'integer',
            'fecha_entrega_requerida' => 'date',
            'creado_en' => 'datetime',
            'completada_en' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (OrdenProduccion $ordenProduccion): void {
            if (! $ordenProduccion->numero) {
                $ordenProduccion->numero = app(CorrelativoService::class)->siguienteNumero('orden_produccion', 'OP');
            }
        });
    }

    public function variante(): BelongsTo
    {
        return $this->belongsTo(VarianteProducto::class, 'variante_id');
    }

    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class, 'venta_id');
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'creado_por');
    }

    public function registros(): HasMany
    {
        return $this->hasMany(RegistroProduccion::class, 'orden_id');
    }

    public function personal(): BelongsToMany
    {
        return $this->belongsToMany(Personal::class, 'orden_produccion_personal', 'orden_id', 'personal_id');
    }
}
