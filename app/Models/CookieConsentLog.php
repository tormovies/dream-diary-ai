<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CookieConsentLog extends Model
{
    protected $table = 'cookie_consent_logs';

    protected $fillable = [
        'client_id',
        'policy_version',
        'necessary',
        'analytics',
        'consent_at',
        'ip_hash',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'necessary' => 'boolean',
            'analytics' => 'boolean',
            'consent_at' => 'datetime',
        ];
    }
}
