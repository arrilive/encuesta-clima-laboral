<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $subSeguridadId = DB::table('subdimensiones')->where('nombre', 'Seguridad')->value('id');
        if ($subSeguridadId) {
            DB::table('preguntas')
                ->where('subdimension_id', $subSeguridadId)
                ->whereIn('orden', [3, 4])
                ->update(['invertida' => false]);
        }
    }

    public function down(): void
    {
        $subSeguridadId = DB::table('subdimensiones')->where('nombre', 'Seguridad')->value('id');
        if ($subSeguridadId) {
            DB::table('preguntas')
                ->where('subdimension_id', $subSeguridadId)
                ->whereIn('orden', [3, 4])
                ->update(['invertida' => true]);
        }
    }
};
