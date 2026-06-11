<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $fillable = [
        'local_id', 'nombre', 'descripcion', 'foto',
        'precio', 'cantidad_disponible', 'disponible', 'es_menu_dia',
    ];

    protected $casts = [
        'disponible' => 'boolean',
        'es_menu_dia' => 'boolean',
        'precio' => 'decimal:2',
    ];

    public function local()
    {
        return $this->belongsTo(Local::class);
    }
}
