<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SeoGoneUrl;
use App\Services\SeoGoneRecorder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SeoGoneUrlController extends Controller
{
    public function index(Request $request): View
    {
        $query = SeoGoneUrl::query()->orderByDesc('id');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('path', 'like', "%{$search}%")
                    ->orWhere('note', 'like', "%{$search}%")
                    ->orWhere('source', 'like', "%{$search}%");
            });
        }

        $goneUrls = $query->paginate(25)->withQueryString();

        return view('admin.seo.gone.index', compact('goneUrls'));
    }

    public function create(): View
    {
        return view('admin.seo.gone.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'path' => ['required', 'string', 'max:512'],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        $path = SeoGoneUrl::normalizePath($validated['path']);
        if ($path === '/') {
            return back()->withErrors(['path' => 'Укажите путь страницы (не только «/»).'])->withInput();
        }

        if (SeoGoneUrl::query()->where('path', $path)->exists()) {
            return back()->withErrors(['path' => 'Такой путь уже записан.'])->withInput();
        }

        SeoGoneUrl::query()->create([
            'path' => $path,
            'source' => SeoGoneRecorder::SOURCE_MANUAL,
            'note' => $validated['note'] ?? null,
        ]);

        return redirect()->route('admin.seo.gone.index')
            ->with('success', 'Запись 410 добавлена.');
    }

    public function destroy(SeoGoneUrl $gone): RedirectResponse
    {
        $gone->delete();

        return redirect()->route('admin.seo.gone.index')
            ->with('success', 'Запись удалена.');
    }
}
