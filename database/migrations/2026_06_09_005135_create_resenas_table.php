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
        Schema::create('resenas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('local_id')->constrained()->onDelete('cascade');
            $table->foreignId('pedido_id')->nullable()->constrained()->onDelete('set null');
            $table->unsignedTinyInteger('estrellas'); // 1-5
            $table->text('comentario')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'pedido_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resenas');
    }
};
