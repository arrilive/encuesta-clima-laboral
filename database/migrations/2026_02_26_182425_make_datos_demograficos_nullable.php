<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('datos_demograficos', function (Blueprint $table) {
            $table->foreignId('antiguedad_id')->nullable()->change();
            $table->foreignId('edad_id')->nullable()->change();
            $table->foreignId('lugar_trabajo_id')->nullable()->change();
            $table->foreignId('sexo_id')->nullable()->change();
            $table->foreignId('grado_academico_id')->nullable()->change();
            $table->foreignId('cargo_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('datos_demograficos', function (Blueprint $table) {
            $table->foreignId('antiguedad_id')->nullable(false)->change();
            $table->foreignId('edad_id')->nullable(false)->change();
            $table->foreignId('lugar_trabajo_id')->nullable(false)->change();
            $table->foreignId('sexo_id')->nullable(false)->change();
            $table->foreignId('grado_academico_id')->nullable(false)->change();
            $table->foreignId('cargo_id')->nullable(false)->change();
        });
    }
};