<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('preguntas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subdimension_id')->constrained('subdimensiones')->cascadeOnDelete();
            $table->text('texto');
            $table->tinyInteger('orden')->unsigned();
            $table->timestamps();

            $table->unique(['subdimension_id', 'orden']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('preguntas');
    }
};
