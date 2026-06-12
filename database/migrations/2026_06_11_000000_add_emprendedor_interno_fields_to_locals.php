<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('locals', function (Blueprint $table) {
            $table->string('codigo_matricula', 20)->nullable()->after('categoria_id');
            $table->string('ciclo_carrera', 120)->nullable()->after('codigo_matricula');
        });
    }

    public function down(): void
    {
        Schema::table('locals', function (Blueprint $table) {
            $table->dropColumn(['codigo_matricula', 'ciclo_carrera']);
        });
    }
};
