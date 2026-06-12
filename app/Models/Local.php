<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Local extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'categoria_id', 'codigo_matricula', 'ciclo_carrera',
        'nombre', 'tipo', 'descripcion', 'foto',
        'direccion', 'punto_entrega', 'distancia_metros', 'horario',
        'precio_min', 'precio_max', 'yape', 'plin', 'whatsapp',
        'estado', 'activo', 'rating_promedio', 'total_resenas',
    ];

    protected $appends = ['foto_url'];

    protected $casts = [
        'activo' => 'boolean',
        'precio_min' => 'decimal:2',
        'precio_max' => 'decimal:2',
        'rating_promedio' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function productos()
    {
        return $this->hasMany(Producto::class);
    }

    public function pedidos()
    {
        return $this->hasMany(Pedido::class);
    }

    public function resenas()
    {
        return $this->hasMany(Resena::class);
    }

    public function getFotoUrlAttribute(): ?string
    {
        if (!$this->foto) return null;
        if (str_starts_with($this->foto, 'http')) return $this->foto;
        return asset('storage/' . $this->foto);
    }

    public function scopeAprobados($query)
    {
        return $query->where('estado', 'aprobado')->where('activo', true);
    }

    public function scopeExternos($query)
    {
        return $query->where('tipo', 'externo');
    }

    public function scopeInternos($query)
    {
        return $query->where('tipo', 'interno');
    }
}
