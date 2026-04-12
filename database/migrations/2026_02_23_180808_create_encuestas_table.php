<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('encuestas', function (Blueprint $table) {
            $table->id();
            $table->string('token', 64)->unique();
            $table->foreignId('empresa_id')
                ->constrained('empresas')
                ->restrictOnDelete();
            $table->enum('estado', ['disponible', 'asignado', 'en_progreso', 'completado'])
                ->default('disponible');
            $table->timestamp('fecha_asignacion')->nullable();
            $table->timestamp('fecha_completada')->nullable();
            $table->timestamps();

            $table->index('empresa_id');
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('encuestas');
    }
};
