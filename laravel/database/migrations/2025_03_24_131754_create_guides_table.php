<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Relación con el usuario
            $table->string('title'); // Título de la guía
            $table->text('content'); // Contenido de la guía
            $table->string('category')->nullable(); // Categoría (por ejemplo, "Misiones", "Personajes", etc.)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guides');
    }
};