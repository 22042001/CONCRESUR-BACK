<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VarianteProducto extends Model
{
    use HasFactory;

    protected $table = 'variante_producto';

    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = null;

    protected $fillable = [
        'producto_id',
        'nombre',
        'unidad_medida',
        'precio_venta',
        'stock_actual',
        'stock_minimo',
        'activo',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'precio_venta' => 'decimal:2',
            'stock_actual' => 'integer',
            'stock_minimo' => 'integer',
            'activo' => 'boolean',
            'creado_en' => 'datetime',
        ];
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function detalleCotizaciones(): HasMany
    {
        return $this->hasMany(DetalleCotizacion::class, 'variante_id');
    }

    public function detalleVentas(): HasMany
    {
        return $this->hasMany(DetalleVenta::class, 'variante_id');
    }

    public function movimientosInventario(): HasMany
    {
        return $this->hasMany(MovimientoInventario::class, 'variante_id');
    }

    public function ordenesProduccion(): HasMany
    {
        return $this->hasMany(OrdenProduccion::class, 'variante_id');
    }
}
