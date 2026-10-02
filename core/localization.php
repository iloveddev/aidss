<?php
/**
 * AID Localization
 * Handles multi-language support.
 */

class AID_Localization {
    private static $langs = [
        'es' => [
            'verify_human' => 'Verifica que eres humano',
            'continue' => 'Continuar',
            'error_generic' => 'Algo salió mal. Inténtalo de nuevo.',
            'qr_scan' => 'Escanea este código QR con tu móvil'
        ],
        'en' => [
            'verify_human' => 'Verify you are human',
            'continue' => 'Continue',
            'error_generic' => 'Something went wrong. Please try again.',
            'qr_scan' => 'Scan this QR code with your mobile'
        ]
    ];

    public static function translate($key, $lang = 'en') {
        return self::$langs[$lang][$key] ?? self::$langs['en'][$key] ?? $key;
    }
}
