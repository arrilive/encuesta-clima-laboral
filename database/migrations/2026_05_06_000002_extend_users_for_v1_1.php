<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // MariaDB/MySQL: ampliar el ENUM de role
        // SQLite no soporta MODIFY COLUMN — el campo role es VARCHAR sin restricción de valores
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin','admin_corporativo','admin_empresa','admin_sucursal') NOT NULL DEFAULT 'admin_empresa'");
        }

        // 2. Agregar las nuevas columnas FK
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('corporativo_id')
                ->nullable()
                ->after('empresa_id')
                ->constrained('corporativos')
                ->nullOnDelete();

            $table->foreignId('sucursal_id')
                ->nullable()
                ->after('corporativo_id')
                ->constrained('sucursales')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['sucursal_id']);
            $table->dropForeign(['corporativo_id']);
            $table->dropColumn(['sucursal_id', 'corporativo_id']);
        });

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin','admin_empresa') NOT NULL DEFAULT 'admin_empresa'");
        }
    }
};
