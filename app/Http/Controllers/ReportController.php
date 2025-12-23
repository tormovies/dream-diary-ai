<?php

namespace App\Http\Controllers;

use App\Helpers\SeoHelper;
use App\Http\Requests\StoreReportRequest;
use App\Http\Requests\UpdateReportRequest;
use App\Models\Report;
use App\Models\Dream;
use App\Models\Tag;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ReportController extends Controller
{
    /**
     * Капитализирует первую букву строки (поддерживает UTF-8 для русского и английского)
     */
    private function capitalizeFirstLetter(?string $string): ?string
    {
        if (empty($string)) {
            return $string;
        }
        
        $trimmed = trim($string);
        if ($trimmed === '') {
            return null;
        }
        
        // Капитализируем первую букву с поддержкой UTF-8 (русский и английский)
        $firstChar = mb_substr($trimmed, 0, 1, 'UTF-8');
        $rest = mb_substr($trimmed, 1, null, 'UTF-8');
        return mb_strtoupper($firstChar, 'UTF-8') . $rest;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $perPage = $request->get('per_page', 20);
        $query = auth()->user()->reports()->with(['dreams', 'tags']);

        // Поиск по тексту
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('dreams', function ($dreamQuery) use ($search) {
                    $dreamQuery->where('title', 'like', "%{$search}%")
                               ->orWhere('description', 'like', "%{$search}%");
                });
            });
        }

        // Фильтр по тегам
        if ($request->filled('tags')) {
            $tags = is_array($request->tags) ? $request->tags : explode(',', $request->tags);
            $query->whereHas('tags', function ($tagQuery) use ($tags) {
                $tagQuery->whereIn('tags.id', $tags);
            });
        }

        // Фильтр по типу сна
        if ($request->filled('dream_type')) {
            $query->whereHas('dreams', function ($dreamQuery) use ($request) {
                $dreamQuery->where('dream_type', $request->dream_type);
            });
        }

        // Фильтр по дате (от)
        if ($request->filled('date_from')) {
            $query->where('report_date', '>=', $request->date_from);
        }

        // Фильтр по дате (до)
        if ($request->filled('date_to')) {
            $query->where('report_date', '<=', $request->date_to);
        }

        // Фильтр по статусу
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Сортировка
        $sortBy = $request->get('sort_by', 'report_date');
        $sortOrder = $request->get('sort_order', 'desc');
        
        if ($sortBy === 'report_date') {
            $query->orderBy('report_date', $sortOrder);
        } elseif ($sortBy === 'created_at') {
            $query->orderBy('created_at', $sortOrder);
        }

        $reports = $query->paginate($perPage)->withQueryString();

        // Получаем все теги пользователя для фильтра
        $allTags = Tag::whereHas('reports', function ($q) {
            $q->where('user_id', auth()->id());
        })->get();

        $dreamTypes = [
            'Яркий сон',
            'Бледный сон',
            'Пограничное состояние',
            'Паралич',
            'ВТО',
            'Осознанное сновидение',
            'Глюк',
            'Транс / Гипноз'
        ];

        $user = auth()->user();

        // Статистика проекта (с кэшированием на 15 минут)
        $globalStats = \Illuminate\Support\Facades\Cache::remember('global_statistics', 900, function () {
            return [
                'users' => \App\Models\User::count(),
                'reports' => Report::where('status', 'published')->count(),
                'dreams' => DB::table('dreams')
                    ->join('reports', 'dreams.report_id', '=', 'reports.id')
                    ->where('reports.status', 'published')
                    ->count(),
                'comments' => \App\Models\Comment::count(),
                'tags' => Tag::count(),
                'avg_dreams_per_report' => Report::where('status', 'published')
                    ->withCount('dreams')
                    ->get()
                    ->avg('dreams_count') ?: 0,
            ];
        });

        // Статистика пользователя
        $userReportsCount = $user->reports()->count();
        $userDreamsCount = $user->reports()->withCount('dreams')->get()->sum('dreams_count');
        
        // Подсчет друзей
        $allFriendships = \App\Models\Friendship::where(function ($query) use ($user) {
            $query->where('user_id', $user->id)
                ->where('status', 'accepted');
        })->orWhere(function ($query) use ($user) {
            $query->where('friend_id', $user->id)
                ->where('status', 'accepted');
        })->get();

        $allFriendIds = $allFriendships->map(function ($friendship) use ($user) {
            return $friendship->user_id === $user->id ? $friendship->friend_id : $friendship->user_id;
        })->toArray();
        
        $friendsCount = count($allFriendIds);
        
        // Друзья онлайн
        $friendsOnline = collect();
        if (!empty($allFriendIds)) {
            $friendsOnline = \App\Models\User::whereIn('id', $allFriendIds)
                ->whereHas('reports', function ($q) {
                    $q->whereDate('created_at', '>=', now()->subDays(7));
                })
                ->with(['reports' => function ($q) {
                    $q->whereDate('created_at', '>=', now()->subDays(7))->latest()->limit(1);
                }])
                ->limit(5)
                ->get();
        }
        
        // Среднее количество снов в месяц
        $firstReport = $user->reports()->orderBy('created_at')->first();
        if ($firstReport) {
            $monthsDiff = $firstReport->created_at->diffInMonths(now());
            $avgDreamsPerMonth = $monthsDiff > 0 ? round($userDreamsCount / max($monthsDiff, 1), 1) : $userDreamsCount;
        } else {
            $avgDreamsPerMonth = 0;
        }
        
        $userStats = [
            'reports' => $userReportsCount,
            'dreams' => $userDreamsCount,
            'friends' => $friendsCount,
            'avg_per_month' => $avgDreamsPerMonth,
        ];

        // Популярные теги (топ 6)
        $popularTags = Tag::withCount('reports')
            ->orderByDesc('reports_count')
            ->limit(6)
            ->get();

        // Сонник (статичные данные)
        $dreamDictionary = [
            ['symbol' => 'Летать', 'meaning' => 'Символизирует свободу, стремление к независимости, преодоление препятствий. Часто снится в периоды важных жизненных изменений.'],
            ['symbol' => 'Вода', 'meaning' => 'Олицетворяет эмоции, подсознание, очищение и перерождение. Чистая вода — к душевному покою, мутная — к внутренним конфликтам.'],
            ['symbol' => 'Дом', 'meaning' => 'Отражение вашего внутреннего мира. Исследование дома во сне означает самопознание и внутренний рост.'],
            ['symbol' => 'Потеряться', 'meaning' => 'Указывает на чувство растерянности в реальной жизни, поиск своего пути или необходимость принятия важного решения.'],
        ];

        // SEO данные
        $seo = SeoHelper::get('dashboard');

        return view('dashboard', compact(
            'reports', 
            'allTags', 
            'dreamTypes',
            'globalStats',
            'userStats',
            'friendsOnline',
            'popularTags',
            'dreamDictionary',
            'seo'
        ));
    }

    /**
     * Поиск отчетов (публичная страница поиска)
     */
    public function search(Request $request): View
    {
        $perPage = $request->get('per_page', 20);
        $user = auth()->user();
        
        // Базовый запрос - только опубликованные отчеты
        $query = Report::with(['user', 'dreams', 'tags'])
            ->where('status', 'published')
            ->where('access_level', '!=', 'none');
        
        // Фильтрация по доступу
        if ($user) {
            // Получаем ID друзей
            $friendIds = [];
            $friendships = \App\Models\Friendship::where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->where('status', 'accepted');
            })->orWhere(function ($q) use ($user) {
                $q->where('friend_id', $user->id)
                    ->where('status', 'accepted');
            })->get();
            
            $friendIds = $friendships->map(function ($friendship) use ($user) {
                return $friendship->user_id === $user->id ? $friendship->friend_id : $friendship->user_id;
            })->toArray();
            
            $query->where(function ($q) use ($user, $friendIds) {
                $q->where('access_level', 'all')
                  ->orWhere(function ($subQ) use ($friendIds) {
                      if (!empty($friendIds)) {
                          $subQ->whereIn('user_id', $friendIds)
                               ->where('access_level', 'friends');
                      }
                  })
                  ->orWhere('user_id', $user->id);
            });
        } else {
            $query->where('access_level', 'all');
        }

        // Поиск по тексту
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('dreams', function ($dreamQuery) use ($search) {
                    $dreamQuery->where('title', 'like', "%{$search}%")
                               ->orWhere('description', 'like', "%{$search}%");
                })
                ->orWhereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('nickname', 'like', "%{$search}%")
                              ->orWhere('name', 'like', "%{$search}%");
                });
            });
        }

        // Фильтр по тегам
        if ($request->filled('tags')) {
            $tags = is_array($request->tags) ? $request->tags : explode(',', $request->tags);
            $query->whereHas('tags', function ($tagQuery) use ($tags) {
                $tagQuery->whereIn('tags.id', $tags);
            });
        }

        // Фильтр по типу сна
        if ($request->filled('dream_type')) {
            $query->whereHas('dreams', function ($dreamQuery) use ($request) {
                $dreamQuery->where('dream_type', $request->dream_type);
            });
        }

        // Фильтр по дате (от)
        if ($request->filled('date_from')) {
            $query->where('report_date', '>=', $request->date_from);
        }

        // Фильтр по дате (до)
        if ($request->filled('date_to')) {
            $query->where('report_date', '<=', $request->date_to);
        }

        // Сортировка
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        if ($sortBy === 'report_date') {
            $query->orderBy('report_date', $sortOrder);
        } elseif ($sortBy === 'created_at') {
            $query->orderBy('created_at', $sortOrder);
        }

        $reports = $query->paginate($perPage)->withQueryString();

        // Получаем все теги для фильтра (только из опубликованных отчетов)
        $allTags = Tag::whereHas('reports', function ($q) {
            $q->where('status', 'published')
              ->where('access_level', '!=', 'none');
        })->get();

        $dreamTypes = [
            'Яркий сон',
            'Бледный сон',
            'Пограничное состояние',
            'Паралич',
            'ВТО',
            'Осознанное сновидение',
            'Глюк',
            'Транс / Гипноз'
        ];

        // SEO данные
        $seo = SeoHelper::get('search');

        return view('search', compact('reports', 'allTags', 'dreamTypes', 'seo'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $dreamTypes = [
            'Яркий сон',
            'Бледный сон',
            'Пограничное состояние',
            'Паралич',
            'ВТО',
            'Осознанное сновидение',
            'Глюк',
            'Транс / Гипноз'
        ];

        return view('reports.create', compact('dreamTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreReportRequest $request): RedirectResponse
    {
        try {
            DB::transaction(function () use ($request) {
                $report = Report::create([
                    'user_id' => auth()->id(),
                    'report_date' => $request->report_date,
                    'access_level' => $request->access_level,
                    'status' => $request->input('status', 'draft'), // По умолчанию черновик
                ]);

                // Создаем сны
                if ($request->has('dreams') && is_array($request->dreams)) {
                    // Получаем данные снов
                    $dreamsData = $request->dreams;
                    
                    // Проверяем, есть ли хотя бы одно название
                    $hasAnyTitle = false;
                    foreach ($dreamsData as $dreamData) {
                        $title = isset($dreamData['title']) ? trim($dreamData['title']) : '';
                        // Игнорируем пустые строки и строку "null"
                        if ($title !== '' && strtolower($title) !== 'null') {
                            $hasAnyTitle = true;
                            break;
                        }
                    }
                    
                    // Если нет ни одного названия, создаем автоматически для первого сна
                    $autoTitleCreated = false;
                    if (!$hasAnyTitle && !empty($dreamsData)) {
                        $user = auth()->user();
                        $autoTitle = "Отчет {$user->nickname} от " . \Carbon\Carbon::parse($request->report_date)->format('d.m.Y');
                        $dreamsData[0]['title'] = $autoTitle;
                        $autoTitleCreated = true;
                    }
                    
                    foreach ($dreamsData as $index => $dreamData) {
                        if (isset($dreamData['description']) && isset($dreamData['dream_type'])) {
                            // Обрабатываем title: если пустой, только пробелы или строка "null" - устанавливаем null
                            $title = isset($dreamData['title']) ? trim($dreamData['title']) : '';
                            // Проверяем, что это не строка "null"
                            if ($title === '' || strtolower($title) === 'null') {
                                $title = null;
                            } else {
                                // Капитализируем первую букву названия
                                $title = $this->capitalizeFirstLetter($title);
                            }
                            
                            Dream::create([
                                'report_id' => $report->id,
                                'title' => $title,
                                'description' => $dreamData['description'],
                                'dream_type' => $dreamData['dream_type'],
                                'order' => $index,
                            ]);
                        }
                    }
                    
                    // Если создали автоматическое название, уведомляем пользователя
                    if ($autoTitleCreated) {
                        session()->flash('info', 'Для первого сна автоматически создано название, так как ни у одного сна не было указано название.');
                    }
                }

                // Обрабатываем теги (опционально)
                $tagsInput = $request->input('tags', []);
                if (is_string($tagsInput)) {
                    $tagsInput = json_decode($tagsInput, true);
                }
                if (is_array($tagsInput) && !empty($tagsInput)) {
                    foreach ($tagsInput as $tagName) {
                        if (!empty($tagName) && is_string($tagName)) {
                            $tag = Tag::firstOrCreate(
                                ['slug' => Str::slug($tagName)],
                                ['name' => $tagName]
                            );
                            $report->tags()->attach($tag->id);
                        }
                    }
                }
            });

            return redirect()->route('dashboard')
                ->with('success', 'Отчет успешно создан');
        } catch (\Exception $e) {
            \Log::error('Ошибка при создании отчета: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Произошла ошибка при создании отчета. Пожалуйста, попробуйте снова.']);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Report $report): View
    {
        // Проверяем доступ (может быть неавторизованным)
        if (!Gate::allows('view', $report)) {
            abort(403, 'У вас нет доступа к этому отчету.');
        }

        $report->load(['dreams', 'tags', 'user', 'comments.user', 'comments.replies.user']);

        $user = auth()->user();

        // Статистика проекта (с кэшированием на 15 минут)
        $globalStats = \Illuminate\Support\Facades\Cache::remember('global_statistics', 900, function () {
            return [
                'users' => \App\Models\User::count(),
                'reports' => Report::where('status', 'published')->count(),
                'dreams' => DB::table('dreams')
                    ->join('reports', 'dreams.report_id', '=', 'reports.id')
                    ->where('reports.status', 'published')
                    ->count(),
                'comments' => \App\Models\Comment::count(),
                'tags' => Tag::count(),
                'avg_dreams_per_report' => Report::where('status', 'published')
                    ->withCount('dreams')
                    ->get()
                    ->avg('dreams_count') ?: 0,
            ];
        });

        // Статистика пользователя (только для авторизованных)
        $userStats = null;
        $friendsOnline = collect();
        if ($user) {
            $userReportsCount = $user->reports()->count();
            $userDreamsCount = $user->reports()->withCount('dreams')->get()->sum('dreams_count');
            
            // Подсчет друзей
            $allFriendships = \App\Models\Friendship::where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->where('status', 'accepted');
            })->orWhere(function ($query) use ($user) {
                $query->where('friend_id', $user->id)
                    ->where('status', 'accepted');
            })->get();

            $allFriendIds = $allFriendships->map(function ($friendship) use ($user) {
                return $friendship->user_id === $user->id ? $friendship->friend_id : $friendship->user_id;
            })->toArray();
            
            $friendsCount = count($allFriendIds);
            
            // Друзья онлайн
            if (!empty($allFriendIds)) {
                $friendsOnline = \App\Models\User::whereIn('id', $allFriendIds)
                    ->whereHas('reports', function ($q) {
                        $q->whereDate('created_at', '>=', now()->subDays(7));
                    })
                    ->with(['reports' => function ($q) {
                        $q->whereDate('created_at', '>=', now()->subDays(7))->latest()->limit(1);
                    }])
                    ->limit(5)
                    ->get();
            }
            
            // Среднее количество снов в месяц
            $firstReport = $user->reports()->orderBy('created_at')->first();
            if ($firstReport) {
                $monthsDiff = $firstReport->created_at->diffInMonths(now());
                $avgDreamsPerMonth = $monthsDiff > 0 ? round($userDreamsCount / max($monthsDiff, 1), 1) : $userDreamsCount;
            } else {
                $avgDreamsPerMonth = 0;
            }
            
            $userStats = [
                'reports' => $userReportsCount,
                'dreams' => $userDreamsCount,
                'friends' => $friendsCount,
                'avg_per_month' => $avgDreamsPerMonth,
            ];
        }

        // Популярные теги (топ 6)
        $popularTags = Tag::withCount('reports')
            ->orderByDesc('reports_count')
            ->limit(6)
            ->get();

        // Сонник (статичные данные)
        $dreamDictionary = [
            ['symbol' => 'Летать', 'meaning' => 'Символизирует свободу, стремление к независимости, преодоление препятствий. Часто снится в периоды важных жизненных изменений.'],
            ['symbol' => 'Вода', 'meaning' => 'Олицетворяет эмоции, подсознание, очищение и перерождение. Чистая вода — к душевному покою, мутная — к внутренним конфликтам.'],
            ['symbol' => 'Дом', 'meaning' => 'Отражение вашего внутреннего мира. Исследование дома во сне означает самопознание и внутренний рост.'],
            ['symbol' => 'Потеряться', 'meaning' => 'Указывает на чувство растерянности в реальной жизни, поиск своего пути или необходимость принятия важного решения.'],
        ];

        // SEO данные
        $seo = SeoHelper::forReport($report);

        return view('reports.show', compact(
            'report',
            'globalStats',
            'userStats',
            'friendsOnline',
            'popularTags',
            'dreamDictionary',
            'seo'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Report $report): View
    {
        $this->authorize('update', $report);

        $report->load(['dreams', 'tags']);
        
        $dreamTypes = [
            'Яркий сон',
            'Бледный сон',
            'Пограничное состояние',
            'Паралич',
            'ВТО',
            'Осознанное сновидение',
            'Глюк',
            'Транс / Гипноз'
        ];

        return view('reports.edit', compact('report', 'dreamTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateReportRequest $request, Report $report): RedirectResponse
    {
        $this->authorize('update', $report);

        DB::transaction(function () use ($request, $report) {
            $report->update([
                'report_date' => $request->report_date,
                'access_level' => $request->access_level,
                'status' => $request->input('status', 'draft'), // Используем переданный статус или draft по умолчанию
            ]);

            // Удаляем старые сны
            $report->dreams()->delete();

            // Получаем данные снов
            $dreamsData = $request->dreams;
            
            // Проверяем, есть ли хотя бы одно название
            $hasAnyTitle = false;
            foreach ($dreamsData as $dreamData) {
                $title = isset($dreamData['title']) ? trim($dreamData['title']) : '';
                // Игнорируем пустые строки и строку "null"
                if ($title !== '' && strtolower($title) !== 'null') {
                    $hasAnyTitle = true;
                    break;
                }
            }
            
            // Если нет ни одного названия, создаем автоматически для первого сна
            $autoTitleCreated = false;
            if (!$hasAnyTitle && !empty($dreamsData)) {
                $user = auth()->user();
                $autoTitle = "Отчет {$user->nickname} от " . \Carbon\Carbon::parse($request->report_date)->format('d.m.Y');
                $dreamsData[0]['title'] = $autoTitle;
                $autoTitleCreated = true;
            }

            // Создаем новые сны
            foreach ($dreamsData as $index => $dreamData) {
                // Обрабатываем title: если пустой, только пробелы или строка "null" - устанавливаем null
                $title = isset($dreamData['title']) ? trim($dreamData['title']) : '';
                // Проверяем, что это не строка "null"
                if ($title === '' || strtolower($title) === 'null') {
                    $title = null;
                } else {
                    // Капитализируем первую букву названия
                    $title = $this->capitalizeFirstLetter($title);
                }
                
                Dream::create([
                    'report_id' => $report->id,
                    'title' => $title,
                    'description' => $dreamData['description'],
                    'dream_type' => $dreamData['dream_type'],
                    'order' => $index,
                ]);
            }
            
            // Если создали автоматическое название, уведомляем пользователя
            if ($autoTitleCreated) {
                session()->flash('info', 'Для первого сна автоматически создано название, так как ни у одного сна не было указано название.');
            }

            // Обновляем теги (опционально)
            $report->tags()->detach();
            $tagsInput = $request->input('tags', []);
            if (is_string($tagsInput)) {
                $tagsInput = json_decode($tagsInput, true);
            }
            if (is_array($tagsInput) && !empty($tagsInput)) {
                foreach ($tagsInput as $tagName) {
                    if (!empty($tagName) && is_string($tagName)) {
                        $tag = Tag::firstOrCreate(
                            ['slug' => Str::slug($tagName)],
                            ['name' => $tagName]
                        );
                        $report->tags()->attach($tag->id);
                    }
                }
            }
        });

        return redirect()->route('reports.show', $report)
            ->with('success', 'Отчет успешно обновлен');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Report $report): RedirectResponse
    {
        $this->authorize('delete', $report);

        $report->delete();

        return redirect()->route('dashboard')
            ->with('success', 'Отчет успешно удален');
    }

    /**
     * Опубликовать отчет
     */
    public function publish(Report $report): RedirectResponse
    {
        $this->authorize('update', $report);

        $report->update(['status' => 'published']);

        return redirect()->route('dashboard')
            ->with('success', 'Отчет опубликован');
    }

    /**
     * Снять отчет с публикации (перевести в черновик)
     */
    public function unpublish(Report $report): RedirectResponse
    {
        $this->authorize('update', $report);

        $report->update(['status' => 'draft']);

        return redirect()->route('dashboard')
            ->with('success', 'Отчет снят с публикации');
    }
}
