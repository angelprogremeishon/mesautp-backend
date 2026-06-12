<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $fillable = [
        'local_id', 'nombre', 'descripcion', 'foto',
        'precio', 'cantidad_disponible', 'disponible', 'es_menu_dia',
        'hora_inicio', 'hora_fin',
    ];

    protected $casts = [
        'disponible' => 'boolean',
        'es_menu_dia' => 'boolean',
        'precio' => 'decimal:2',
    ];

    protected $appends = ['foto_url'];

    public function getFotoUrlAttribute(): ?string
    {
        if (!$this->foto) return null;
        if (str_starts_with($this->foto, 'http')) return $this->foto;
        return asset('storage/' . $this->foto);
    }

    public function local()
    {
        return $this->belongsTo(Local::class);
    }
}
