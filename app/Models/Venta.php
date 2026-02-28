<?php

namespace App\Models;

use App\Services\CorrelativoService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Venta extends Model
{
    use HasFactory;

    protected $table = 'venta';

    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = null;

    protected $fillable = [
        'numero',
        'cliente_id',
        'cotizacion_id',
        'metodo_pago',
        'forma_pago',
        'fecha_entrega',
        'importe_total',
        'adelanto',
        'saldo_total',
        'estado_operativo',
        'estado_financiero',
        'usuario_id',
        'completada_en',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha_entrega' => 'date',
            'importe_total' => 'decimal:2',
            'adelanto' => 'decimal:2',
            'saldo_total' => 'decimal:2',
            'creado_en' => 'datetime',
            'completada_en' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Venta $venta): void {
            if (! $venta->numero) {
                $venta->numero = app(CorrelativoService::class)->siguienteNumero('venta', 'VTA');
            }
        });
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function cotizacion(): BelongsTo
    {
        return $this->belongsTo(Cotizacion::class, 'cotizacion_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleVenta::class, 'venta_id');
    }

    public function abonos(): HasMany
    {
        return $this->hasMany(AbonoVenta::class, 'venta_id');
    }

    public function ordenesProduccion(): HasMany
    {
        return $this->hasMany(OrdenProduccion::class, 'venta_id');
    }

    public function pedidoLogistico(): HasOne
    {
        return $this->hasOne(PedidoLogistico::class, 'venta_id');
    }
}
