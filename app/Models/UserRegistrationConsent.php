<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserRegistrationConsent extends Model
{
    protected $fillable = [
        'user_id',
        'policy_version',
        'personal_data_consent_at',
        'terms_accepted_at',
        'ip_hash',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'personal_data_consent_at' => 'datetime',
            'terms_accepted_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
