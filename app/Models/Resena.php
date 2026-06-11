<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resena extends Model
{
    protected $fillable = [
        'user_id', 'local_id', 'pedido_id', 'estrellas', 'comentario',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function local()
    {
        return $this->belongsTo(Local::class);
    }

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }
}
