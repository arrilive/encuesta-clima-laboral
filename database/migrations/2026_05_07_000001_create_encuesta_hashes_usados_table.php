<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('encuesta_hashes_usados', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('phone_hash', 64);
            $table->unsignedBigInteger('lote_id');
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('lote_id')
                ->references('id')
                ->on('lotes')
                ->onDelete('cascade');

            $table->unique(['phone_hash', 'lote_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('encuesta_hashes_usados');
    }
};
