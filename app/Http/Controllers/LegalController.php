<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class LegalController extends Controller
{
    public function personalData(): View
    {
        return view('legal.personal-data', [
            'operator' => config('compliance.operator'),
            'policyVersion' => config('compliance.policy_version'),
            'effectiveDate' => config('compliance.documents_effective_date'),
        ]);
    }

    public function cookies(): View
    {
        return view('legal.cookie-policy', [
            'operator' => config('compliance.operator'),
            'policyVersion' => config('compliance.policy_version'),
            'effectiveDate' => config('compliance.documents_effective_date'),
        ]);
    }
}
