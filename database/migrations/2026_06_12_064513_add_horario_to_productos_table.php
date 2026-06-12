<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            // Rango de disponibilidad de la oferta (definido por el emprendedor).
            $table->time('hora_inicio')->nullable()->after('es_menu_dia');
            $table->time('hora_fin')->nullable()->after('hora_inicio');
        });
    }

    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropColumn(['hora_inicio', 'hora_fin']);
        });
    }
};
