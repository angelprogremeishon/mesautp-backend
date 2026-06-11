<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('locals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('categoria_id')->nullable()->constrained('categorias')->onDelete('set null');
            $table->string('nombre');
            $table->enum('tipo', ['externo', 'interno']);
            $table->string('descripcion')->nullable();
            $table->string('foto')->nullable();
            $table->string('direccion')->nullable();
            $table->string('punto_entrega')->nullable();
            $table->decimal('distancia_metros', 6, 0)->nullable();
            $table->string('horario')->nullable();
            $table->decimal('precio_min', 6, 2)->nullable();
            $table->decimal('precio_max', 6, 2)->nullable();
            $table->string('yape')->nullable();
            $table->string('plin')->nullable();
            $table->string('whatsapp')->nullable();
            $table->enum('estado', ['pendiente', 'aprobado', 'rechazado', 'suspendido'])->default('pendiente');
            $table->boolean('activo')->default(true);
            $table->decimal('rating_promedio', 3, 2)->default(0);
            $table->unsignedInteger('total_resenas')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locals');
    }
};
