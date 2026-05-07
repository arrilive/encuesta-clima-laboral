<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('otp_verificaciones', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('numero_e164', 20);
            $table->char('otp_hash', 64);
            $table->unsignedBigInteger('lote_id');
            $table->unsignedBigInteger('empresa_id');
            $table->tinyInteger('intentos')->default(0);
            $table->timestamp('expira_en');
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('lote_id')
                ->references('id')
                ->on('lotes')
                ->onDelete('cascade');

            $table->foreign('empresa_id')
                ->references('id')
                ->on('empresas')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otp_verificaciones');
    }
};
