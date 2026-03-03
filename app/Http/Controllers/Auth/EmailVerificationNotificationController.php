<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('notifications.index', absolute: false));
        }

        try {
            $request->user()->sendEmailVerificationNotification();
            Log::info('Verification email sent', ['user_id' => $request->user()->id, 'email' => $request->user()->email]);
        } catch (\Throwable $e) {
            Log::error('Verification email failed', [
                'user_id' => $request->user()->id,
                'email' => $request->user()->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Не удалось отправить письмо: ' . $e->getMessage());
        }

        return back()->with('status', 'verification-link-sent');
    }
}
