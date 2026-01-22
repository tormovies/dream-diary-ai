<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\SeoMeta;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ArticleController extends Controller
{
    /**
     * Список всех статей
     */
    public function index(Request $request): View
    {
        $query = Article::query();

        // Фильтрация по типу
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Фильтрация по статусу
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Поиск
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        // Сортировка
        // Для guide - по умолчанию сортировка по order по возрастанию
        // Для article - по дате создания по убыванию
        if ($request->filled('type') && $request->type === 'guide') {
            $sortBy = $request->get('sort_by', 'order');
            $sortOrder = $request->get('sort_order', 'asc');
        } else {
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
        }
        $query->orderBy($sortBy, $sortOrder);

        $articles = $query->with('author')->paginate(20);

        return view('admin.articles.index', [
            'articles' => $articles,
            'filters' => $request->only(['type', 'status', 'search', 'sort_by', 'sort_order']),
        ]);
    }

    /**
     * Форма создания статьи
     */
    public function create(): View
    {
        return view('admin.articles.create');
    }

    /**
     * Сохранение новой статьи
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:articles,slug',
            'content' => 'required|string',
            'type' => 'required|in:guide,article',
            'status' => 'required|in:draft,published',
            'order' => 'nullable|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            // SEO поля
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:500',
            'seo_h1' => 'nullable|string|max:255',
            'seo_h1_description' => 'nullable|string',
        ]);

        // Обработка изображения
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('articles', 'public');
        }

        // Установка published_at если статус published
        if ($validated['status'] === 'published' && !$request->has('published_at')) {
            $validated['published_at'] = now();
        }

        // Создание статьи
        $article = Article::create([
            'title' => $validated['title'],
            'slug' => $validated['slug'] ?? null, // Автогенерация через mutator если пусто
            'content' => $validated['content'],
            'type' => $validated['type'],
            'status' => $validated['status'],
            'order' => $validated['order'] ?? 0,
            'author_id' => auth()->id(),
            'image' => $validated['image'] ?? null,
            'published_at' => $validated['published_at'] ?? null,
        ]);

        // Сохранение SEO
        $this->saveSeoMeta($article, $validated);

        return redirect()->route('admin.articles.index')
            ->with('success', 'Статья успешно создана');
    }

    /**
     * Форма редактирования статьи
     */
    public function edit(Article $article): View
    {
        $seoMeta = $article->seoMeta();
        
        return view('admin.articles.edit', [
            'article' => $article,
            'seoMeta' => $seoMeta,
        ]);
    }

    /**
     * Обновление статьи
     */
    public function update(Request $request, Article $article): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:articles,slug,' . $article->id,
            'content' => 'required|string',
            'type' => 'required|in:guide,article',
            'status' => 'required|in:draft,published',
            'order' => 'nullable|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            // SEO поля
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:500',
            'seo_h1' => 'nullable|string|max:255',
            'seo_h1_description' => 'nullable|string',
        ]);

        // Обработка изображения
        if ($request->hasFile('image')) {
            // Удаляем старое изображение
            if ($article->image) {
                Storage::disk('public')->delete($article->image);
            }
            $validated['image'] = $request->file('image')->store('articles', 'public');
        }

        // Установка published_at если статус изменился на published
        if ($validated['status'] === 'published' && $article->status !== 'published') {
            $validated['published_at'] = now();
        }

        // Обновление статьи
        $article->update([
            'title' => $validated['title'],
            'slug' => $validated['slug'],
            'content' => $validated['content'],
            'type' => $validated['type'],
            'status' => $validated['status'],
            'order' => $validated['order'] ?? $article->order,
            'image' => $validated['image'] ?? $article->image,
            'published_at' => $validated['published_at'] ?? $article->published_at,
        ]);

        // Сохранение SEO
        $this->saveSeoMeta($article, $validated);

        return redirect()->route('admin.articles.index')
            ->with('success', 'Статья успешно обновлена');
    }

    /**
     * Удаление статьи
     */
    public function destroy(Article $article): RedirectResponse
    {
        // Удаляем изображение
        if ($article->image) {
            Storage::disk('public')->delete($article->image);
        }

        // Удаляем SEO
        SeoMeta::where('page_type', $article->type)
            ->where('page_id', $article->id)
            ->delete();

        // Удаляем статью
        $article->delete();

        return redirect()->route('admin.articles.index')
            ->with('success', 'Статья успешно удалена');
    }

    /**
     * Публикация статьи
     */
    public function publish(Article $article): RedirectResponse
    {
        $article->update([
            'status' => 'published',
            'published_at' => $article->published_at ?? now(),
        ]);

        return redirect()->back()->with('success', 'Статья опубликована');
    }

    /**
     * Перевод в черновик
     */
    public function unpublish(Article $article): RedirectResponse
    {
        $article->update(['status' => 'draft']);

        return redirect()->back()->with('success', 'Статья переведена в черновик');
    }

    /**
     * Обновление порядка статей (для drag-and-drop)
     */
    public function updateOrder(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:articles,id',
            'items.*.order' => 'required|integer',
        ]);

        foreach ($request->items as $item) {
            Article::where('id', $item['id'])->update(['order' => $item['order']]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Загрузка изображения для TinyMCE редактора
     */
    public function uploadImage(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'file' => 'required|image|max:2048', // 2MB max
        ]);

        $path = $request->file('file')->store('articles/content', 'public');
        $url = Storage::url($path);

        return response()->json([
            'location' => $url
        ]);
    }

    /**
     * Сохранение SEO метаданных
     */
    private function saveSeoMeta(Article $article, array $validated): void
    {
        $pageType = $article->type === 'guide' ? 'guide' : 'article';
        
        // Проверяем, есть ли уже SEO запись
        $seoMeta = SeoMeta::where('page_type', $pageType)
            ->where('page_id', $article->id)
            ->first();

        $seoData = [
            'page_type' => $pageType,
            'page_id' => $article->id,
            'title' => $validated['seo_title'] ?? null,
            'description' => $validated['seo_description'] ?? null,
            'h1' => $validated['seo_h1'] ?? null,
            'h1_description' => $validated['seo_h1_description'] ?? null,
            'og_title' => $validated['seo_og_title'] ?? $validated['seo_title'] ?? null,
            'og_description' => $validated['seo_og_description'] ?? $validated['seo_description'] ?? null,
            'og_image' => $article->image ? asset('storage/' . $article->image) : null,
            'is_active' => true,
            'priority' => 0,
        ];

        // Удаляем пустые значения
        $seoData = array_filter($seoData, function ($value) {
            return $value !== null && $value !== '';
        });

        if (empty($seoData)) {
            return; // Нет данных для сохранения
        }

        if ($seoMeta) {
            $seoMeta->update($seoData);
        } else {
            SeoMeta::create($seoData);
        }
    }
}
