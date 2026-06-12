<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('apellido')->nullable()->after('name');
            $table->string('dni', 8)->nullable()->unique()->after('apellido');
            // PIN hasheado (bcrypt). Reemplaza al magic link como método de ingreso.
            $table->string('pin')->nullable()->after('password');
            // Marca si el usuario ya completó su registro (nombre/apellido/dni/pin).
            $table->boolean('registro_completo')->default(false)->after('pin');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['dni']);
            $table->dropColumn(['apellido', 'dni', 'pin', 'registro_completo']);
        });
    }
};
