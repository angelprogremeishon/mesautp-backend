<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    protected $fillable = [
        'user_id', 'local_id', 'producto_id', 'cantidad',
        'total', 'estado', 'nota', 'hora_recojo',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'hora_recojo' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function local()
    {
        return $this->belongsTo(Local::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function resena()
    {
        return $this->hasOne(Resena::class);
    }
}
