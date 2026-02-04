<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Redirect;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RedirectController extends Controller
{
    public function index(Request $request): View
    {
        $query = Redirect::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('from_path', 'like', "%{$search}%")
                  ->orWhere('to_url', 'like', "%{$search}%");
            });
        }

        if ($request->filled('active')) {
            if ($request->active === '1') {
                $query->where('is_active', true);
            } elseif ($request->active === '0') {
                $query->where('is_active', false);
            }
        }

        $redirects = $query->orderBy('id', 'desc')->paginate(25)->withQueryString();

        return view('admin.seo.redirects.index', compact('redirects'));
    }

    public function create(): View
    {
        return view('admin.seo.redirects.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'from_path' => ['required', 'string', 'max:2048', 'unique:redirects,from_path'],
            'to_url'    => ['required', 'string', 'max:2048'],
            'status_code' => ['nullable', 'integer', 'in:301,302,307,308'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['from_path'] = Redirect::normalizePath($validated['from_path']);
        $validated['status_code'] = (int) ($validated['status_code'] ?? 301);
        $validated['is_active'] = $request->boolean('is_active', true);

        Redirect::create($validated);

        return redirect()->route('admin.seo.redirects.index')
            ->with('success', 'Редирект добавлен.');
    }

    public function edit(Redirect $redirect): View
    {
        return view('admin.seo.redirects.edit', compact('redirect'));
    }

    public function update(Request $request, Redirect $redirect): RedirectResponse
    {
        $validated = $request->validate([
            'from_path'   => ['required', 'string', 'max:2048', 'unique:redirects,from_path,' . $redirect->id],
            'to_url'      => ['required', 'string', 'max:2048'],
            'status_code' => ['nullable', 'integer', 'in:301,302,307,308'],
            'is_active'   => ['nullable', 'boolean'],
        ]);

        $validated['from_path'] = Redirect::normalizePath($validated['from_path']);
        $validated['status_code'] = (int) ($validated['status_code'] ?? 301);
        $validated['is_active'] = $request->boolean('is_active', true);

        $redirect->update($validated);

        return redirect()->route('admin.seo.redirects.index')
            ->with('success', 'Редирект обновлён.');
    }

    public function destroy(Redirect $redirect): RedirectResponse
    {
        $redirect->delete();
        return redirect()->route('admin.seo.redirects.index')
            ->with('success', 'Редирект удалён.');
    }
}
