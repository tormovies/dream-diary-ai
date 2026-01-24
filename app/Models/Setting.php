<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * Получить значение настройки по ключу
     */
    public static function getValue(string $key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        if (!$setting) {
            return $default;
        }

        // Обработка boolean значений
        if ($setting->value === '1' || $setting->value === '0') {
            return (bool) $setting->value;
        }

        // Пытаемся декодировать JSON
        $decoded = json_decode($setting->value, true);
        return $decoded !== null ? $decoded : $setting->value;
    }

    /**
     * Установить значение настройки
     */
    public static function setValue(string $key, $value): void
    {
        // Если значение null, удаляем запись
        if ($value === null) {
            self::where('key', $key)->delete();
            return;
        }
        
        // Если значение - массив или объект, кодируем в JSON
        if (is_array($value) || is_object($value)) {
            $value = json_encode($value);
        } elseif (is_bool($value)) {
            $value = $value ? '1' : '0';
        } else {
            $value = (string) $value;
        }

        self::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }
}
