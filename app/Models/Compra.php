<?php

namespace App\Models;

use App\Services\CorrelativoService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Compra extends Model
{
    use HasFactory;

    protected $table = 'compra';

    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = null;

    protected $fillable = [
        'numero',
        'categoria_id',
        'proveedor_id',
        'metodo_pago',
        'forma_pago',
        'importe_total',
        'adelanto',
        'saldo_total',
        'estado_financiero',
        'procesada_inventario',
        'usuario_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'importe_total' => 'decimal:2',
            'adelanto' => 'decimal:2',
            'saldo_total' => 'decimal:2',
            'procesada_inventario' => 'boolean',
            'creado_en' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Compra $compra): void {
            if (! $compra->numero) {
                $compra->numero = app(CorrelativoService::class)->siguienteNumero('compra', 'CMP');
            }
        });
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(CategoriaCompra::class, 'categoria_id');
    }

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleCompra::class, 'compra_id');
    }

    public function abonos(): HasMany
    {
        return $this->hasMany(AbonoCompra::class, 'compra_id');
    }
}
