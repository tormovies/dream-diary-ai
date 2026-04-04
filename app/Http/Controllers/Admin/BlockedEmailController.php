<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlockedEmail;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BlockedEmailController extends Controller
{
    public function index(Request $request): View
    {
        $query = BlockedEmail::query()->orderByDesc('id');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('email', 'like', '%'.$search.'%');
        }

        if ($request->filled('permanent')) {
            if ($request->permanent === '1') {
                $query->where('is_permanent', true);
            } elseif ($request->permanent === '0') {
                $query->where('is_permanent', false);
            }
        }

        $blockedEmails = $query->paginate(30)->withQueryString();

        return view('admin.blocked-emails.index', compact('blockedEmails'));
    }
}
