<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Throwable;
use Twilio\Rest\Client;

class WhatsappService
{
    protected ?Client $client = null;

    protected ?string $from = null;

    protected ?string $templateOtpSid = null;

    protected ?string $templateLinkSid = null;

    protected bool $configured = false;

    public function __construct()
    {
        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $from = config('services.twilio.whatsapp_from');
        $this->templateOtpSid = config('services.twilio.template_otp_sid');
        $this->templateLinkSid = config('services.twilio.template_link_sid');

        if (! empty($sid) && ! empty($token) && ! empty($from)) {
            $this->from = str_starts_with($from, 'whatsapp:') ? $from : 'whatsapp:'.$from;
            $this->configured = true;

            try {
                $this->client = new Client($sid, $token);
            } catch (Throwable $e) {
                Log::error('Error al inicializar Twilio Client: '.$e->getMessage());
                $this->configured = false;
            }
        }
    }

    /**
     * Enviar código OTP de verificación por WhatsApp.
     */
    public function enviarOtp(string $to, string $codigo): bool
    {
        $formattedTo = $this->formatNumber($to);

        if (! $this->configured || app()->environment('testing')) {
            Log::info('[SIMULADO] WhatsApp OTP enviado', [
                'to' => $formattedTo,
                'codigo' => $codigo,
            ]);

            return true;
        }

        try {
            $this->client->messages->create($formattedTo, [
                'from' => $this->from,
                'contentSid' => $this->templateOtpSid,
                'contentVariables' => json_encode(['1' => $codigo]),
            ]);

            Log::info('WhatsApp OTP enviado exitosamente vía Twilio', ['to' => $formattedTo]);

            return true;
        } catch (Throwable $e) {
            Log::error('Error al enviar WhatsApp OTP vía Twilio: '.$e->getMessage(), [
                'to' => $formattedTo,
            ]);

            return false;
        }
    }

    /**
     * Enviar enlace/token de acceso a la encuesta por WhatsApp.
     */
    public function enviarEnlaceAcceso(string $to, string $urlAcceso, string $nombreEntidad): bool
    {
        $formattedTo = $this->formatNumber($to);

        if (! $this->configured || app()->environment('testing')) {
            Log::info('[SIMULADO] WhatsApp Enlace Acceso enviado', [
                'to' => $formattedTo,
                'url' => $urlAcceso,
                'entidad' => $nombreEntidad,
            ]);

            return true;
        }

        try {
            // NOTA: Los índices exactos de contentVariables dependen de la estructura EXACTA de la plantilla aprobada por Meta ('clima_enlace_encuesta'). Se ajustará si se requiere más de una variable.
            $this->client->messages->create($formattedTo, [
                'from' => $this->from,
                'contentSid' => $this->templateLinkSid,
                'contentVariables' => json_encode(['1' => $urlAcceso]),
            ]);

            Log::info('WhatsApp Enlace Acceso enviado exitosamente vía Twilio', ['to' => $formattedTo]);

            return true;
        } catch (Throwable $e) {
            Log::error('Error al enviar WhatsApp Enlace Acceso vía Twilio: '.$e->getMessage(), [
                'to' => $formattedTo,
            ]);

            return false;
        }
    }

    /**
     * Formatear el número destino para asegurar el prefijo 'whatsapp:'.
     */
    protected function formatNumber(string $number): string
    {
        return str_starts_with($number, 'whatsapp:') ? $number : 'whatsapp:'.$number;
    }
}
