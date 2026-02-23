<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('datos_demograficos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('encuesta_id')
                ->unique()
                ->constrained('encuestas')
                ->restrictOnDelete();
            $table->foreignId('antiguedad_id')
                ->constrained('antiguedades')
                ->restrictOnDelete();
            $table->foreignId('edad_id')
                ->constrained('edades')
                ->restrictOnDelete();
            $table->foreignId('lugar_trabajo_id')
                ->constrained('lugares_trabajo')
                ->restrictOnDelete();
            $table->foreignId('sexo_id')
                ->constrained('sexos')
                ->restrictOnDelete();
            $table->foreignId('grado_academico_id')
                ->constrained('grados_academicos')
                ->restrictOnDelete();
            $table->foreignId('cargo_id')
                ->constrained('cargos')
                ->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('datos_demograficos');
    }
};
