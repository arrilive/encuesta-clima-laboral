<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subdimensiones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dimension_id')->constrained('dimensiones')->cascadeOnDelete();
            $table->string('nombre', 100);
            $table->tinyInteger('orden')->unsigned();
            $table->timestamps();

            $table->unique(['dimension_id', 'orden']);
            $table->unique(['dimension_id', 'nombre']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subdimensiones');
    }
};
