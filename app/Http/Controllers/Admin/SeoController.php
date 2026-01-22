<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SeoMeta;
use App\Models\Report;
use App\Models\User;
use App\Models\DreamInterpretation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SeoController extends Controller
{
    /**
     * Список всех SEO-записей
     */
    public function index(Request $request): View
    {
        $query = SeoMeta::query();

        if ($request->filled('page_type')) {
            $query->where('page_type', $request->page_type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('h1', 'like', "%{$search}%");
            });
        }

        $seoMetas = $query->orderBy('page_type')->orderBy('priority', 'desc')->paginate(20);

        $pageTypes = [
            'home' => 'Главная',
            'report' => 'Отчет',
            'profile' => 'Профиль',
            'diary' => 'Дневник',
            'search' => 'Поиск',
            'activity' => 'Лента активности',
            'users' => 'Сообщество',
            'dashboard' => 'Мои отчёты',
            'statistics' => 'Статистика',
            'notifications' => 'Уведомления',
            'dream-analyzer' => 'Толкование снов (форма)',
            'dream-analyzer-result' => 'Толкование сна (результат)',
        ];

        return view('admin.seo.index', compact('seoMetas', 'pageTypes'));
    }

    /**
     * Форма создания SEO-записи
     */
    public function create(): View
    {
        $pageTypes = [
            'home' => 'Главная',
            'report' => 'Отчет',
            'profile' => 'Профиль',
            'diary' => 'Дневник',
            'search' => 'Поиск',
            'activity' => 'Лента активности',
            'users' => 'Сообщество',
            'dashboard' => 'Мои отчёты',
            'statistics' => 'Статистика',
            'notifications' => 'Уведомления',
            'dream-analyzer' => 'Толкование снов (форма)',
            'dream-analyzer-result' => 'Толкование сна (результат)',
        ];

        // Для выбора конкретных страниц
        $reports = Report::latest()->limit(50)->get(['id', 'report_date']);
        $users = User::latest()->limit(50)->get(['id', 'nickname', 'name']);
        $interpretations = DreamInterpretation::latest()
            ->limit(50)
            ->get(['id', 'hash', 'dream_description', 'created_at']);

        return view('admin.seo.create', compact('pageTypes', 'reports', 'users', 'interpretations'));
    }

    /**
     * Сохранение новой SEO-записи
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'page_type' => ['required', 'string', 'max:255'],
            'page_id' => ['nullable', 'integer'],
            'route_name' => ['nullable', 'string', 'max:255'],
            'title' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'h1' => ['nullable', 'string'],
            'h1_description' => ['nullable', 'string'],
            'keywords' => ['nullable', 'string'],
            'og_title' => ['nullable', 'string'],
            'og_description' => ['nullable', 'string'],
            'og_image' => ['nullable', 'string'],
            'og_image_file' => ['nullable', 'image', 'max:2048'], // 2MB max
            'is_active' => ['boolean'],
            'priority' => ['integer', 'min:0', 'max:100'],
        ]);

        // Если загружено изображение
        if ($request->hasFile('og_image_file')) {
            $path = $request->file('og_image_file')->store('seo/og-images', 'public');
            $validated['og_image'] = 'storage/' . $path;
        }

        $validated['is_active'] = $request->has('is_active') ? 1 : 0;
        $validated['priority'] = $validated['priority'] ?? 0;

        // Очищаем page_id и route_name, если они пустые
        if (empty($validated['page_id'])) {
            $validated['page_id'] = null;
        }
        if (empty($validated['route_name'])) {
            $validated['route_name'] = null;
        }

        SeoMeta::create($validated);

        return redirect()->route('admin.seo.index')->with('success', 'SEO-запись создана успешно');
    }

    /**
     * Форма редактирования SEO-записи
     */
    public function edit(SeoMeta $seo): View
    {
        $pageTypes = [
            'home' => 'Главная',
            'report' => 'Отчет',
            'profile' => 'Профиль',
            'diary' => 'Дневник',
            'search' => 'Поиск',
            'activity' => 'Лента активности',
            'users' => 'Сообщество',
            'dashboard' => 'Мои отчёты',
            'statistics' => 'Статистика',
            'notifications' => 'Уведомления',
            'dream-analyzer' => 'Толкование снов (форма)',
            'dream-analyzer-result' => 'Толкование сна (результат)',
        ];

        // Для выбора конкретных страниц
        $reports = Report::latest()->limit(50)->get(['id', 'report_date']);
        $users = User::latest()->limit(50)->get(['id', 'nickname', 'name']);
        $interpretations = DreamInterpretation::latest()
            ->limit(50)
            ->get(['id', 'hash', 'dream_description', 'created_at']);

        return view('admin.seo.edit', compact('seo', 'pageTypes', 'reports', 'users', 'interpretations'));
    }

    /**
     * Обновление SEO-записи
     */
    public function update(Request $request, SeoMeta $seo): RedirectResponse
    {
        $validated = $request->validate([
            'page_type' => ['required', 'string', 'max:255'],
            'page_id' => ['nullable', 'integer'],
            'route_name' => ['nullable', 'string', 'max:255'],
            'title' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'h1' => ['nullable', 'string'],
            'h1_description' => ['nullable', 'string'],
            'keywords' => ['nullable', 'string'],
            'og_title' => ['nullable', 'string'],
            'og_description' => ['nullable', 'string'],
            'og_image' => ['nullable', 'string'],
            'og_image_file' => ['nullable', 'image', 'max:2048'],
            'is_active' => ['boolean'],
            'priority' => ['integer', 'min:0', 'max:100'],
        ]);

        // Если загружено новое изображение
        if ($request->hasFile('og_image_file')) {
            // Удаляем старое, если оно было загружено на сервер
            if ($seo->og_image && strpos($seo->og_image, 'storage/') === 0) {
                $oldPath = str_replace('storage/', '', $seo->og_image);
                Storage::disk('public')->delete($oldPath);
            }
            
            $path = $request->file('og_image_file')->store('seo/og-images', 'public');
            $validated['og_image'] = 'storage/' . $path;
        }

        $validated['is_active'] = $request->has('is_active') ? 1 : 0;
        $validated['priority'] = $validated['priority'] ?? 0;

        // Очищаем page_id и route_name, если они пустые
        if (empty($validated['page_id'])) {
            $validated['page_id'] = null;
        }
        if (empty($validated['route_name'])) {
            $validated['route_name'] = null;
        }

        $seo->update($validated);

        return redirect()->route('admin.seo.index')->with('success', 'SEO-запись обновлена успешно');
    }

    /**
     * Удаление SEO-записи
     */
    public function destroy(SeoMeta $seo): RedirectResponse
    {
        // Удаляем изображение, если оно было загружено на сервер
        if ($seo->og_image && strpos($seo->og_image, 'storage/') === 0) {
            $path = str_replace('storage/', '', $seo->og_image);
            Storage::disk('public')->delete($path);
        }

        $seo->delete();

        return redirect()->route('admin.seo.index')->with('success', 'SEO-запись удалена успешно');
    }
}
