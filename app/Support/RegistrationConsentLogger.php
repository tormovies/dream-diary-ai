<?php

namespace App\Support;

use App\Models\User;
use App\Models\UserRegistrationConsent;
use Illuminate\Http\Request;

class RegistrationConsentLogger
{
    public static function log(User $user, Request $request): void
    {
        $ip = $request->ip() ?? '';
        $ipHash = hash('sha256', $ip.'|'.config('app.key'));
        $now = now();

        UserRegistrationConsent::query()->create([
            'user_id' => $user->id,
            'policy_version' => (string) config('compliance.policy_version'),
            'personal_data_consent_at' => $now,
            'terms_accepted_at' => $now,
            'ip_hash' => $ipHash,
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 512),
        ]);
    }
}
