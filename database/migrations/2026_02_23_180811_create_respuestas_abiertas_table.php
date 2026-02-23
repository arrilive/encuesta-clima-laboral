<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('respuestas_abiertas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('encuesta_id')
                ->constrained('encuestas')
                ->restrictOnDelete();
            $table->foreignId('pregunta_abierta_id')
                ->constrained('preguntas_abiertas')
                ->restrictOnDelete();
            $table->text('texto')->nullable();
            $table->timestamps();

            $table->unique(['encuesta_id', 'pregunta_abierta_id']);
            $table->index('encuesta_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('respuestas_abiertas');
    }
};
