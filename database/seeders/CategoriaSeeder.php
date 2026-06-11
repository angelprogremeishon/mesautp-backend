<?php

namespace Database\Seeders;

use App\Models\Categoria;
use Illuminate\Database\Seeder;

class CategoriaSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = [
            ['nombre' => 'Criolla',          'icono' => '🍲'],
            ['nombre' => 'Pollo a la brasa', 'icono' => '🍗'],
            ['nombre' => 'Mariscos',         'icono' => '🦐'],
            ['nombre' => 'Fusión',           'icono' => '🌮'],
            ['nombre' => 'Menú del día',     'icono' => '🍱'],
            ['nombre' => 'Bebidas',          'icono' => '🥤'],
            ['nombre' => 'Snacks',           'icono' => '🍿'],
            ['nombre' => 'Postres',          'icono' => '🍰'],
        ];

        foreach ($categorias as $data) {
            Categoria::firstOrCreate(['nombre' => $data['nombre']], $data);
        }
    }
}
