<?php

return [
    'tokens' => [
        'dias_advertencia' => env('CLIMA_TOKENS_WARNING_DAYS', 7),
        'dias_riesgo' => env('CLIMA_TOKENS_RISK_DAYS', 14),
    ],
    'otp' => [
        'expiracion_minutos' => env('CLIMA_OTP_EXPIRY_MINUTES', 10),
        'max_intentos' => env('CLIMA_OTP_MAX_ATTEMPTS', 3),
    ],
];
