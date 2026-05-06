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
            $table->dropForeign(['empresa_id']);
            $table->dropIndex(['empresa_id']);
            $table->dropColumn('empresa_id');
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
            $table->foreignId('empresa_id')->nullable()->constrained('empresas');
            $table->index('empresa_id', 'encuestas_empresa_id_index');
            $table->foreign('lote_id')->references('id')->on('token_lotes')->nullOnDelete();
        });
    }
};
