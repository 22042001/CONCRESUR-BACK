<?php

namespace App\Models;

use App\Services\CorrelativoService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cotizacion extends Model
{
    use HasFactory;

    protected $table = 'cotizacion';

    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = null;

    protected $fillable = [
        'numero',
        'cliente_id',
        'importe_total',
        'estado',
        'confirmada_en',
        'venta_id',
        'usuario_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'importe_total' => 'decimal:2',
            'creado_en' => 'datetime',
            'confirmada_en' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Cotizacion $cotizacion): void {
            if (! $cotizacion->numero) {
                $cotizacion->numero = app(CorrelativoService::class)->siguienteNumero('cotizacion', 'COT');
            }
        });
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class, 'venta_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleCotizacion::class, 'cotizacion_id');
    }
}
