<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('preguntas', function (Blueprint $table) {
            $table->boolean('invertida')->default(false)->after('orden');
        });

        $subSeguridadId = DB::table('subdimensiones')->where('nombre', 'Seguridad')->value('id');
        if ($subSeguridadId) {
            DB::table('preguntas')
                ->where('subdimension_id', $subSeguridadId)
                ->whereIn('orden', [2, 5, 6, 7, 8])
                ->update(['invertida' => true]);
        }
    }

    public function down(): void
    {
        Schema::table('preguntas', function (Blueprint $table) {
            $table->dropColumn('invertida');
        });
    }
};
