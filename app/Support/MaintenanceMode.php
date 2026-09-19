<?php

namespace App\Support;

use App\Models\Setting;
use Throwable;

class MaintenanceMode
{
    protected static ?bool $enabled = null;

    public static function enabled(): bool
    {
        if (static::$enabled !== null) {
            return static::$enabled;
        }

        try {
            return static::$enabled = (bool) Setting::getValue('maintenance_mode', false);
        } catch (Throwable) {
            return static::$enabled = true;
        }
    }
}
