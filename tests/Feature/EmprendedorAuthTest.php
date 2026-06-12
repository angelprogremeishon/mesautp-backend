<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmprendedorAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_emprendedor_externo_se_registra_con_cualquier_correo(): void
    {
        $response = $this->postJson('/api/auth/emprendedor/register', [
            'tipo'                  => 'externo',
            'name'                  => 'Doña Lucha',
            'email'                 => 'donalucha@gmail.com',
            'password'              => 'secret123',
            'password_confirmation' => 'secret123',
            'nombre'                => 'Doña Lucha Restaurante',
            'direccion'             => 'Jr. Los Claveles 234',
            'whatsapp'              => '999888777',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email', 'role']]);

        $this->assertDatabaseHas('users', ['email' => 'donalucha@gmail.com', 'role' => 'emprendedor']);
        $this->assertDatabaseHas('locals', ['nombre' => 'Doña Lucha Restaurante', 'tipo' => 'externo', 'estado' => 'pendiente']);
    }

    public function test_emprendedor_interno_exige_correo_utp(): void
    {
        $response = $this->postJson('/api/auth/emprendedor/register', [
            'tipo'                  => 'interno',
            'name'                  => 'Camila Ruiz',
            'email'                 => 'camila@gmail.com',
            'password'              => 'secret123',
            'password_confirmation' => 'secret123',
            'nombre'                => 'Las Delicias de Camila',
            'whatsapp'              => '999000111',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['email']);
    }

    public function test_emprendedor_interno_acepta_correo_utp(): void
    {
        $response = $this->postJson('/api/auth/emprendedor/register', [
            'tipo'                  => 'interno',
            'name'                  => 'Camila Ruiz',
            'email'                 => 'u20230045@utp.edu.pe',
            'password'              => 'secret123',
            'password_confirmation' => 'secret123',
            'nombre'                => 'Las Delicias de Camila',
            'codigo_matricula'      => 'U20230045',
            'whatsapp'              => '999000111',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('locals', ['nombre' => 'Las Delicias de Camila', 'tipo' => 'interno']);
    }

    public function test_registro_rechaza_correo_duplicado(): void
    {
        User::factory()->create(['email' => 'repetido@gmail.com']);

        $response = $this->postJson('/api/auth/emprendedor/register', [
            'tipo'                  => 'externo',
            'name'                  => 'X',
            'email'                 => 'repetido@gmail.com',
            'password'              => 'secret123',
            'password_confirmation' => 'secret123',
            'nombre'                => 'Local X',
            'whatsapp'              => '999',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['email']);
    }

    public function test_emprendedor_inicia_sesion_con_credenciales_correctas(): void
    {
        User::factory()->create([
            'email'    => 'log@gmail.com',
            'password' => \Illuminate\Support\Facades\Hash::make('secret123'),
            'role'     => 'emprendedor',
        ]);

        $response = $this->postJson('/api/auth/emprendedor/login', [
            'email'    => 'log@gmail.com',
            'password' => 'secret123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email', 'role']]);
    }

    public function test_login_rechaza_password_incorrecta(): void
    {
        User::factory()->create([
            'email'    => 'log2@gmail.com',
            'password' => \Illuminate\Support\Facades\Hash::make('secret123'),
            'role'     => 'emprendedor',
        ]);

        $response = $this->postJson('/api/auth/emprendedor/login', [
            'email'    => 'log2@gmail.com',
            'password' => 'malísima',
        ]);

        $response->assertStatus(422);
    }

    public function test_login_rechaza_a_un_consumidor(): void
    {
        User::factory()->create([
            'email'    => 'estudiante@utp.edu.pe',
            'password' => \Illuminate\Support\Facades\Hash::make('secret123'),
            'role'     => 'consumidor',
        ]);

        $response = $this->postJson('/api/auth/emprendedor/login', [
            'email'    => 'estudiante@utp.edu.pe',
            'password' => 'secret123',
        ]);

        $response->assertStatus(422);
    }
}
