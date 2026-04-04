<?php

namespace App\Rules;

use App\Models\BlockedEmail;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class EmailNotBlocked implements ValidationRule
{
    public function __construct(
        private ?User $allowCurrentEmailForUser = null
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        $value = (string) $value;

        if ($this->allowCurrentEmailForUser !== null
            && BlockedEmail::normalizeEmail($value) === BlockedEmail::normalizeEmail($this->allowCurrentEmailForUser->email)) {
            return;
        }

        if (BlockedEmail::isBlocked($value)) {
            $fail('Этот email недоступен для использования.');
        }
    }
}
