<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PedidoLogistico extends Model
{
    use HasFactory;

    public const ESTADO_EN_ESPERA = 'En espera';

    public const ESTADO_EN_CAMINO = 'En camino';

    public const ESTADO_ENTREGADO = 'Entregado';

    protected $table = 'pedido_logistico';

    public const CREATED_AT = null;

    public const UPDATED_AT = null;

    protected $fillable = [
        'venta_id',
        'estado',
        'observaciones',
        'fecha_en_espera',
        'fecha_en_camino',
        'fecha_entregado',
        'usuario_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha_en_espera' => 'datetime',
            'fecha_en_camino' => 'datetime',
            'fecha_entregado' => 'datetime',
        ];
    }

    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class, 'venta_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}
