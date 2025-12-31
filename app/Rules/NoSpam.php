<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NoSpam implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value)) {
            return;
        }

        // Проверка на наличие URL
        if ($this->containsUrl($value)) {
            $fail('Поле :attribute не может содержать ссылки.');
            return;
        }

        // Проверка на подозрительные паттерны
        if ($this->containsSuspiciousPatterns($value)) {
            $fail('Поле :attribute содержит недопустимые символы или паттерны.');
            return;
        }

        // Проверка на спам-слова
        if ($this->containsSpamWords($value)) {
            $fail('Поле :attribute содержит запрещенные слова.');
            return;
        }

        // Проверка на чрезмерное количество специальных символов
        if ($this->hasExcessiveSpecialChars($value)) {
            $fail('Поле :attribute содержит слишком много специальных символов.');
            return;
        }
    }

    /**
     * Проверка на наличие URL
     */
    private function containsUrl(string $value): bool
    {
        // Проверка на http://, https://, www., ftp://, и домены с .com, .ru, и т.д.
        $patterns = [
            '/https?:\/\//i',           // http:// или https://
            '/www\./i',                 // www.
            '/ftp:\/\//i',              // ftp://
            '/\w+\.(com|net|org|ru|info|biz|co|io|me|cc|xyz|top|site|online|club|tk|ml|ga|cf|gq)/i', // домены
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $value)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Проверка на подозрительные паттерны
     */
    private function containsSuspiciousPatterns(string $value): bool
    {
        $patterns = [
            '/\$\d+[,.]?\d*/i',                    // Деньги: $100, $1,000
            '/\d+\s*(dollars?|usd|bitcoin|btc|eth|crypto|credit)/i', // Финансовые термины
            '/hs=[\da-f]{32}/i',                   // Хеш-паттерны типа hs=xxxxx
            '/[*]{3,}/i',                           // 3 или больше звездочек подряд
            '/click\s+here|confirm.*transaction|claim.*prize|verify.*account/i', // Спам-фразы
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $value)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Проверка на спам-слова
     */
    private function containsSpamWords(string $value): bool
    {
        $spamWords = [
            'casino', 'viagra', 'cialis', 'pharmacy', 'lottery', 
            'winner', 'prize', 'jackpot', 'forex', 'crypto',
            'bitcoin', 'investment', 'profit', 'earn money',
            'make money', 'get rich', 'million dollars',
            'click here', 'buy now', 'order now', 'limited offer',
            'act now', 'apply now', 'call now', 'free money',
            'nigerian prince', 'inheritance', 'paypal', 'western union',
        ];

        $lowerValue = mb_strtolower($value, 'UTF-8');

        foreach ($spamWords as $word) {
            if (str_contains($lowerValue, $word)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Проверка на чрезмерное количество специальных символов
     */
    private function hasExcessiveSpecialChars(string $value): bool
    {
        // Подсчет специальных символов (не буквы, не цифры, не пробелы, не базовая пунктуация)
        $specialCharsCount = preg_match_all('/[^a-zA-Zа-яА-ЯёЁ0-9\s.,:;!?\-]/', $value);
        $totalLength = mb_strlen($value, 'UTF-8');

        // Если больше 30% специальных символов - подозрительно
        if ($totalLength > 0 && ($specialCharsCount / $totalLength) > 0.3) {
            return true;
        }

        return false;
    }
}
