<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('preguntas_abiertas', function (Blueprint $table) {
            $table->id();
            $table->text('texto');
            $table->tinyInteger('orden')->unsigned()->unique();
            $table->smallInteger('limite_caracteres')->unsigned()->default(500);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('preguntas_abiertas');
    }
};
