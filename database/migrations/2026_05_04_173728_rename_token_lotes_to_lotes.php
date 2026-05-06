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
        Schema::table('encuestas', function (Blueprint $table) {
            $table->dropForeign(['lote_id']);
        });

        Schema::rename('token_lotes', 'lotes');

        Schema::table('lotes', function (Blueprint $table) {
            $table->renameColumn('cantidad', 'tokens_total');
            $table->foreignId('sucursal_id')->nullable()->after('empresa_id')->constrained('sucursales')->nullOnDelete();
            $table->date('fecha_inicio')->nullable()->after('nombre');
            $table->date('fecha_fin')->nullable()->after('fecha_inicio');
            $table->boolean('activo')->default(true)->after('fecha_fin');
        });

        Schema::table('encuestas', function (Blueprint $table) {
            $table->foreign('lote_id')->references('id')->on('lotes')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('encuestas', function (Blueprint $table) {
            $table->dropForeign(['lote_id']);
        });

        Schema::table('lotes', function (Blueprint $table) {
            $table->dropForeign(['sucursal_id']);
            $table->dropColumn(['sucursal_id', 'fecha_inicio', 'fecha_fin', 'activo']);
            $table->renameColumn('tokens_total', 'cantidad');
        });

        Schema::rename('lotes', 'token_lotes');

        Schema::table('encuestas', function (Blueprint $table) {
            $table->foreign('lote_id')->references('id')->on('token_lotes')->nullOnDelete();
        });
    }
};
