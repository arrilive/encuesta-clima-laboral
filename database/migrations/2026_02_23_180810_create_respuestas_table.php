<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('respuestas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('encuesta_id')
                ->constrained('encuestas')
                ->restrictOnDelete();
            $table->foreignId('pregunta_id')
                ->constrained('preguntas')
                ->restrictOnDelete();
            $table->foreignId('opcion_respuesta_id')
                ->constrained('opciones_respuesta')
                ->restrictOnDelete();
            $table->timestamps();

            $table->unique(['encuesta_id', 'pregunta_id']);
            $table->index('encuesta_id');
            $table->index('pregunta_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('respuestas');
    }
};
