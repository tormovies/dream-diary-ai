<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\SeoHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailVerificationPromptController extends Controller
{
    /**
     * Display the email verification prompt.
     */
    public function __invoke(Request $request): RedirectResponse|View
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('notifications.index', absolute: false));
        }
        
        $seo = SeoHelper::get('verify-email');
        return view('auth.verify-email', compact('seo'));
    }
}
