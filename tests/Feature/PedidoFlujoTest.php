<?php

namespace Tests\Feature;

use App\Models\Local;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PedidoFlujoTest extends TestCase
{
    use RefreshDatabase;

    private function emprendedorConLocal(): array
    {
        $user  = User::factory()->create(['role' => 'emprendedor']);
        $local = Local::create([
            'user_id' => $user->id, 'nombre' => 'Mi Local', 'tipo' => 'interno',
            'estado' => 'aprobado', 'activo' => true, 'whatsapp' => '999',
        ]);
        return [$user, $local];
    }

    public function test_emprendedor_marca_pedido_entregado(): void
    {
        [$user, $local] = $this->emprendedorConLocal();
        $cliente = User::factory()->create();
        $pedido  = Pedido::create([
            'user_id' => $cliente->id, 'local_id' => $local->id,
            'cantidad' => 1, 'total' => 10, 'estado' => 'listo',
        ]);

        Sanctum::actingAs($user);
        $response = $this->postJson("/api/emprendedor/pedidos/{$pedido->id}/entregar");

        $response->assertStatus(200);
        $this->assertDatabaseHas('pedidos', ['id' => $pedido->id, 'estado' => 'entregado']);
    }

    public function test_otro_emprendedor_no_puede_entregar_pedido_ajeno(): void
    {
        [, $local] = $this->emprendedorConLocal();
        $cliente = User::factory()->create();
        $pedido  = Pedido::create([
            'user_id' => $cliente->id, 'local_id' => $local->id,
            'cantidad' => 1, 'total' => 10, 'estado' => 'listo',
        ]);
        $intruso = User::factory()->create(['role' => 'emprendedor']);

        Sanctum::actingAs($intruso);
        $response = $this->postJson("/api/emprendedor/pedidos/{$pedido->id}/entregar");

        $response->assertStatus(403);
    }

    public function test_publicar_oferta_del_dia_no_duplica(): void
    {
        [$user] = $this->emprendedorConLocal();
        Sanctum::actingAs($user);

        $this->postJson('/api/emprendedor/productos', [
            'nombre' => 'Menú A', 'precio' => 9, 'cantidad_disponible' => 10, 'es_menu_dia' => 1,
        ])->assertStatus(201);

        $this->postJson('/api/emprendedor/productos', [
            'nombre' => 'Menú B', 'precio' => 11, 'cantidad_disponible' => 5, 'es_menu_dia' => 1,
        ])->assertStatus(201);

        $this->assertDatabaseCount('productos', 1);
        $this->assertDatabaseHas('productos', ['nombre' => 'Menú B', 'precio' => 11]);
    }

    public function test_ciclo_completo_revive_ventas_e_ingresos(): void
    {
        [$emp, $local] = $this->emprendedorConLocal();

        // 1. Emprendedor publica la oferta del día (S/ 12).
        Sanctum::actingAs($emp);
        $this->postJson('/api/emprendedor/productos', [
            'nombre' => 'Menú del día', 'precio' => 12, 'cantidad_disponible' => 10, 'es_menu_dia' => 1,
        ])->assertStatus(201);
        $producto = Producto::where('local_id', $local->id)->first();

        // 2. Consumidor reserva 2 porciones (total = 24).
        $cliente = User::factory()->create(['role' => 'consumidor']);
        Sanctum::actingAs($cliente);
        $res = $this->postJson('/api/pedidos', [
            'local_id' => $local->id, 'producto_id' => $producto->id, 'cantidad' => 2,
        ])->assertStatus(201);
        $pedidoId = $res->json('pedido.id');

        // 3. Emprendedor: confirmar -> listo -> entregar.
        Sanctum::actingAs($emp);
        $this->postJson("/api/emprendedor/pedidos/{$pedidoId}/confirmar")->assertStatus(200);
        $this->postJson("/api/emprendedor/pedidos/{$pedidoId}/listo")->assertStatus(200);
        $this->postJson("/api/emprendedor/pedidos/{$pedidoId}/entregar")->assertStatus(200);
        $this->assertDatabaseHas('pedidos', ['id' => $pedidoId, 'estado' => 'entregado']);

        // 4. El dashboard refleja ingresos del día = 24.
        $dash = $this->getJson('/api/emprendedor')->assertStatus(200);
        $this->assertEquals(24, (float) $dash->json('stats.ingresos_hoy'));

        // 5. Consumidor califica -> el rating del local sube.
        Sanctum::actingAs($cliente);
        $this->postJson("/api/pedidos/{$pedidoId}/calificar", [
            'estrellas' => 5, 'comentario' => 'Muy rico',
        ])->assertStatus(200);
        $this->assertEquals(5.0, (float) $local->fresh()->rating_promedio);
    }
}
