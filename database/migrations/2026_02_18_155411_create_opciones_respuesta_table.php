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
        Schema::create('opciones_respuesta', function (Blueprint $table) {
            $table->id();
            $table->string('opcion', 50)->unique();
            $table->tinyInteger('valor_numerico')->unsigned()->unique();
            $table->tinyInteger('orden')->unsigned()->unique();
            $table->timestamps();
        });
    }

    /**
     * Revertir la migración.
     */
    public function down(): void
    {
        Schema::dropIfExists('opciones_respuesta');
    }
};
