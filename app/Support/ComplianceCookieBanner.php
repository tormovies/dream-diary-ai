<?php

namespace App\Support;

class ComplianceCookieBanner
{
    public const MODE_OFF = 'off';

    public const MODE_INFORMATIVE = 'informative';

    public const MODE_CONSENT = 'consent';

    private static ?string $forcedMode = null;

    public static function forceMode(?string $mode): void
    {
        self::$forcedMode = $mode;
    }

    public static function mode(): string
    {
        if (self::$forcedMode !== null) {
            return self::$forcedMode;
        }

        $mode = env('COMPLIANCE_COOKIE_BANNER_MODE');
        if (is_string($mode) && in_array($mode, [self::MODE_OFF, self::MODE_INFORMATIVE, self::MODE_CONSENT], true)) {
            return $mode;
        }

        // Обратная совместимость: true → consent, false → informative (раньше был «баннер выкл»)
        $legacy = env('COMPLIANCE_COOKIE_BANNER');
        if ($legacy !== null) {
            return filter_var($legacy, FILTER_VALIDATE_BOOLEAN) ? self::MODE_CONSENT : self::MODE_INFORMATIVE;
        }

        return self::MODE_INFORMATIVE;
    }

    public static function requiresConsent(): bool
    {
        return self::mode() === self::MODE_CONSENT;
    }

    public static function showsInformativeNotice(): bool
    {
        return self::mode() === self::MODE_INFORMATIVE;
    }
}
