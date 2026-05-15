<?php

namespace App\Http\Controllers;

use App\Models\CookieConsentLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CookieConsentController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $expectedVersion = (string) config('compliance.policy_version');

        $validator = Validator::make($request->all(), [
            'client_id' => ['required', 'uuid'],
            'policy_version' => ['required', 'string', 'max:32', 'in:'.$expectedVersion],
            'analytics' => ['required', 'boolean'],
            'necessary' => ['required', 'boolean'],
        ]);

        $validator->validate();

        $clientId = $request->string('client_id')->toString();
        $analytics = $request->boolean('analytics');
        $necessary = $request->boolean('necessary');

        $last = CookieConsentLog::query()
            ->where('client_id', $clientId)
            ->orderByDesc('id')
            ->first();

        if (
            $last
            && $last->policy_version === $expectedVersion
            && (bool) $last->analytics === $analytics
            && (bool) $last->necessary === $necessary
        ) {
            return response()->json(['ok' => true, 'deduplicated' => true]);
        }

        $ip = $request->ip() ?? '';
        $ipHash = hash('sha256', $ip.'|'.config('app.key'));

        CookieConsentLog::query()->create([
            'client_id' => $clientId,
            'policy_version' => $expectedVersion,
            'necessary' => $necessary,
            'analytics' => $analytics,
            'consent_at' => now(),
            'ip_hash' => $ipHash,
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 512),
        ]);

        return response()->json(['ok' => true, 'deduplicated' => false]);
    }
}
