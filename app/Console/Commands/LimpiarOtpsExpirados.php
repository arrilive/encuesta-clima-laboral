<?php

namespace App\Console\Commands;

use App\Models\OtpVerificacion;
use Illuminate\Console\Command;

class LimpiarOtpsExpirados extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'otp:limpiar-expirados';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Elimina registros de otp_verificaciones cuya fecha expira_en ya pasó';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $inicio = microtime(true);

        $eliminados = OtpVerificacion::where('expira_en', '<', now())->delete();

        $fin = microtime(true);
        $ms = round(($fin - $inicio) * 1000, 2);
        $ahora = now()->toDateTimeString();

        $this->info("Limpieza completada: {$eliminados} registro(s) eliminado(s) en {$ms}ms — {$ahora}");

        return self::SUCCESS;
    }
}
