<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Throwable;
use Twilio\Rest\Client;

class SmsService
{
    protected ?Client $client = null;

    protected ?string $from = null;

    protected bool $configured = false;

    public function __construct()
    {
        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $from = config('services.twilio.sms_from');

        if (! empty($sid) && ! empty($token) && ! empty($from)) {
            $this->from = $from;
            $this->configured = true;

            try {
                $this->client = new Client($sid, $token);
            } catch (Throwable $e) {
                Log::error('Error al inicializar Twilio Client (SMS): '.$e->getMessage());
                $this->configured = false;
            }
        }
    }

    /**
     * Enviar código OTP de verificación por SMS.
     */
    public function enviarOtp(string $to, string $codigo): bool
    {
        if (! $this->configured || app()->environment('testing')) {
            Log::info('[SIMULADO] SMS OTP enviado', [
                'to' => $to,
            ]);

            return true;
        }

        try {
            $mensaje = "Tu codigo de verificacion para la encuesta de clima laboral es: {$codigo}. Vence en 10 minutos. Por tu seguridad, no lo compartas con nadie.";

            $this->client->messages->create($to, [
                'from' => $this->from,
                'body' => $mensaje,
                'contentRetention' => 'discard',
                'addressRetention' => 'obfuscate',
            ]);

            Log::info('SMS OTP enviado exitosamente vía Twilio', ['to' => $to]);

            return true;
        } catch (Throwable $e) {
            Log::error('Error al enviar SMS OTP vía Twilio: '.$e->getMessage(), [
                'to' => $to,
            ]);

            return false;
        }
    }

    /**
     * Enviar enlace/token de acceso a la encuesta por SMS.
     */
    public function enviarEnlaceAcceso(string $to, string $urlAcceso, string $nombreEntidad): bool
    {
        if (! $this->configured || app()->environment('testing')) {
            Log::info('[SIMULADO] SMS Enlace Acceso enviado', [
                'to' => $to,
                'entidad' => $nombreEntidad,
            ]);

            return true;
        }

        try {
            $mensaje = "Hola, te invitamos a participar en la encuesta de clima laboral de {$nombreEntidad}. Ingresa aqui: {$urlAcceso}";

            $this->client->messages->create($to, [
                'from' => $this->from,
                'body' => $mensaje,
                'contentRetention' => 'discard',
                'addressRetention' => 'obfuscate',
            ]);

            Log::info('SMS Enlace Acceso enviado exitosamente vía Twilio', ['to' => $to]);

            return true;
        } catch (Throwable $e) {
            Log::error('Error al enviar SMS Enlace Acceso vía Twilio: '.$e->getMessage(), [
                'to' => $to,
            ]);

            return false;
        }
    }
}
