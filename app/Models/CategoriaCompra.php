<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategoriaCompra extends Model
{
    use HasFactory;

    protected $table = 'categoria_compra';

    public const CREATED_AT = null;

    public const UPDATED_AT = null;

    protected $fillable = [
        'nombre',
    ];

    public function compras(): HasMany
    {
        return $this->hasMany(Compra::class, 'categoria_id');
    }
}
