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
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('local_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('producto_id')->nullable();
            $table->unsignedInteger('cantidad')->default(1);
            $table->decimal('total', 6, 2);
            $table->enum('estado', ['pendiente', 'confirmado', 'listo', 'entregado', 'cancelado'])->default('pendiente');
            $table->text('nota')->nullable();
            $table->timestamp('hora_recojo')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
