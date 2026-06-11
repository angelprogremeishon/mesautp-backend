<?php

namespace Database\Seeders;

use App\Models\Categoria;
use Illuminate\Database\Seeder;

class LocalSeeder extends Seeder
{
    public function run(): void
    {
        $admin = \App\Models\User::create([
            'name'              => 'Admin MesaUTP',
            'email'             => 'admin@utp.edu.pe',
            'password'          => bcrypt(env('ADMIN_PASSWORD', 'MesaUTP@2026!')),
            'role'              => 'admin',
            'email_verified_at' => now(),
        ]);

        $externos = [
            [
                'nombre' => 'Doña Lucha', 'categoria' => 'Criolla',
                'descripcion' => 'El mejor menú criollo cerca de UTP Ate',
                'precio_min' => 8, 'precio_max' => 12, 'distancia_metros' => 80,
                'horario' => 'Lun–Vie 11:30–15:00', 'whatsapp' => '51999000001',
                'foto' => 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=600&q=80',
            ],
            [
                'nombre' => 'El Rinconcito del Sabor', 'categoria' => 'Pollo a la brasa',
                'descripcion' => 'Pollo a la brasa y combinados desde S/ 10',
                'precio_min' => 10, 'precio_max' => 18, 'distancia_metros' => 120,
                'horario' => 'Lun–Dom 12:00–20:00', 'whatsapp' => '51999000002',
                'foto' => 'https://images.unsplash.com/photo-1598514982901-2b3f6e73b2d9?w=600&q=80',
            ],
            [
                'nombre' => 'La Olla de Barro', 'categoria' => 'Fusión',
                'descripcion' => 'Cocina casera y vegetariana',
                'precio_min' => 7, 'precio_max' => 11, 'distancia_metros' => 200,
                'horario' => 'Lun–Vie 11:00–15:30', 'whatsapp' => '51999000003',
                'foto' => 'https://images.unsplash.com/photo-1565557623262-b51c2513a641?w=600&q=80',
            ],
            [
                'nombre' => 'Pollería Los Andes', 'categoria' => 'Pollo a la brasa',
                'descripcion' => 'Familia pollo y más desde S/ 8',
                'precio_min' => 8, 'precio_max' => 22, 'distancia_metros' => 350,
                'horario' => 'Todos los días 11:00–22:00', 'whatsapp' => '51999000004',
                'foto' => 'https://images.unsplash.com/photo-1555939594-58d7cb561ad1?w=600&q=80',
            ],
            [
                'nombre' => 'Cevichería Arte', 'categoria' => 'Mariscos',
                'descripcion' => 'Ceviches y tiraditos frescos',
                'precio_min' => 12, 'precio_max' => 25, 'distancia_metros' => 450,
                'horario' => 'Mar–Dom 10:00–16:00', 'whatsapp' => '51999000005',
                'foto' => 'https://images.unsplash.com/photo-1565958011703-44f9829ba187?w=600&q=80',
            ],
        ];

        foreach ($externos as $i => $data) {
            $cat = Categoria::where('nombre', $data['categoria'])->first();

            $user = \App\Models\User::create([
                'name'              => 'Emp ' . $data['nombre'],
                'email'             => "emp{$i}@utp.edu.pe",
                'role'              => 'emprendedor',
                'email_verified_at' => now(),
            ]);

            $local = \App\Models\Local::create([
                'user_id'        => $user->id,
                'categoria_id'   => $cat?->id,
                'nombre'         => $data['nombre'],
                'tipo'           => 'externo',
                'descripcion'    => $data['descripcion'],
                'foto'           => $data['foto'] ?? null,
                'precio_min'     => $data['precio_min'],
                'precio_max'     => $data['precio_max'],
                'distancia_metros'=> $data['distancia_metros'],
                'horario'        => $data['horario'],
                'whatsapp'       => $data['whatsapp'],
                'yape'           => '9999000' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'plin'           => '9999001' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'estado'         => 'aprobado',
                'activo'         => true,
                'rating_promedio'=> round(3.8 + ($i * 0.2), 1),
                'total_resenas'  => rand(10, 80),
            ]);

            \App\Models\Producto::create([
                'local_id'           => $local->id,
                'nombre'             => 'Menú del día — ' . $data['categoria'],
                'descripcion'        => 'Sopa + segundo + refresco',
                'precio'             => $data['precio_min'],
                'cantidad_disponible'=> 20,
                'disponible'         => true,
                'es_menu_dia'        => true,
            ]);
        }

        $menudel = Categoria::where('nombre', 'Menú del día')->first();

        $internos = [
            [
                'nombre' => 'Seco de pollo casero', 'descripcion' => 'Seco con frijoles y arroz, hecho en casa',
                'precio_min' => 7, 'punto_entrega' => 'Patio principal – escalera norte',
                'foto' => 'https://images.unsplash.com/photo-1512058564366-18510be2db19?w=600&q=80',
            ],
            [
                'nombre' => 'Causa rellena y pollo', 'descripcion' => 'Causa limeña + pollo + ensalada',
                'precio_min' => 6, 'punto_entrega' => 'Ingreso pabellón D',
                'foto' => 'https://images.unsplash.com/photo-1476224203421-9ac39bcb3327?w=600&q=80',
            ],
            [
                'nombre' => 'Lomo saltado casero', 'descripcion' => 'Lomo saltado con arroz y papas fritas',
                'precio_min' => 8, 'punto_entrega' => 'Cafetería piso 2',
                'foto' => 'https://images.unsplash.com/photo-1542367592-8849eb950fd8?w=600&q=80',
            ],
        ];

        foreach ($internos as $j => $data) {
            $user = \App\Models\User::create([
                'name'              => 'Estudiante ' . ($j + 1),
                'email'             => "est{$j}@utp.edu.pe",
                'role'              => 'emprendedor',
                'email_verified_at' => now(),
            ]);

            $local = \App\Models\Local::create([
                'user_id'        => $user->id,
                'categoria_id'   => $menudel?->id,
                'nombre'         => $data['nombre'],
                'tipo'           => 'interno',
                'descripcion'    => $data['descripcion'],
                'foto'           => $data['foto'] ?? null,
                'precio_min'     => $data['precio_min'],
                'precio_max'     => $data['precio_min'] + 2,
                'punto_entrega'  => $data['punto_entrega'],
                'horario'        => '12:00–13:30',
                'estado'         => 'aprobado',
                'activo'         => true,
                'rating_promedio'=> round(4.0 + ($j * 0.2), 1),
                'total_resenas'  => rand(5, 30),
                'yape'           => '9998000' . str_pad($j, 2, '0', STR_PAD_LEFT),
                'whatsapp'       => '519998000' . str_pad($j, 2, '0', STR_PAD_LEFT),
            ]);

            \App\Models\Producto::create([
                'local_id'           => $local->id,
                'nombre'             => $data['nombre'],
                'descripcion'        => $data['descripcion'],
                'precio'             => $data['precio_min'],
                'cantidad_disponible'=> rand(5, 15),
                'disponible'         => true,
                'es_menu_dia'        => true,
            ]);
        }
    }
}
