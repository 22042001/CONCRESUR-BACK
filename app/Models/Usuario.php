<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'usuario';

    public const CREATED_AT = 'creado_en';

    public const UPDATED_AT = null;

    protected $fillable = [
        'nombre',
        'email',
        'password_hash',
        'rol_id',
        'activo',
    ];

    protected $hidden = [
        'password_hash',
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

    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }

    public function cotizaciones(): HasMany
    {
        return $this->hasMany(Cotizacion::class, 'usuario_id');
    }

    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class, 'usuario_id');
    }

    public function abonosVenta(): HasMany
    {
        return $this->hasMany(AbonoVenta::class, 'usuario_id');
    }

    public function compras(): HasMany
    {
        return $this->hasMany(Compra::class, 'usuario_id');
    }

    public function abonosCompra(): HasMany
    {
        return $this->hasMany(AbonoCompra::class, 'usuario_id');
    }

    public function ordenesProduccionCreadas(): HasMany
    {
        return $this->hasMany(OrdenProduccion::class, 'creado_por');
    }

    public function pedidosLogisticos(): HasMany
    {
        return $this->hasMany(PedidoLogistico::class, 'usuario_id');
    }
}
