<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    protected $fillable = ['nombre', 'icono'];

    public function locals()
    {
        return $this->hasMany(Local::class);
    }
}
