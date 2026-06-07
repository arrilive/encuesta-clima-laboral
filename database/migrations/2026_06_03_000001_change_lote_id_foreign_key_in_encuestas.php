<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('encuestas', function (Blueprint $table) {
            $table->dropForeign(['lote_id']);
            $table->foreign('lote_id')->references('id')->on('lotes')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('encuestas', function (Blueprint $table) {
            $table->dropForeign(['lote_id']);
            $table->foreign('lote_id')->references('id')->on('lotes')->nullOnDelete();
        });
    }
};
