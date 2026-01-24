<?php

namespace App\Http\Controllers;

use App\Helpers\SeoHelper;
use App\Helpers\TraditionHelper;
use App\Http\Requests\StoreReportRequest;
use App\Http\Requests\UpdateReportRequest;
use App\Services\TextSanitizer;
use App\Models\Report;
use App\Models\Dream;
use App\Models\Tag;
use App\Models\DreamInterpretation;
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
     * Проверяет, является ли описание серией снов (есть разделитель ---)
     */
    private function isDreamSeries(string $text): bool
    {
        // Проверяем наличие разделителя из минусов (3 и более)
        return preg_match('/---+/', $text) === 1;
    }

    /**
     * Разбивает текст на отдельные сны по разделителю ---
     */
    private function splitDreams(string $text): array
    {
        $dreams = [];
        
        // Разделяем по минусам (3 и более)
        $parts = preg_split('/---+/', $text);
        
        foreach ($parts as $part) {
            $part = trim($part);
            // Игнорируем пустые части
            if (!empty($part)) {
                $dreams[] = $part;
            }
        }
        
        return $dreams;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $perPage = $request->get('per_page', 20);
        $query = auth()->user()->reports()->with(['dreams', 'tags', 'analysis']);

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
        $firstReport = $user->reports()->orderBy('report_date')->first();
        if ($firstReport) {
            $monthsDiff = $firstReport->report_date->diffInMonths(now());
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
        
        // Поисковый запрос для отображения контекста
        $searchQuery = $request->filled('search') ? $request->search : null;

        return view('dashboard', compact(
            'reports', 
            'allTags', 
            'dreamTypes',
            'globalStats',
            'userStats',
            'friendsOnline',
            'popularTags',
            'dreamDictionary',
            'seo',
            'searchQuery'
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
        // Учитываем иерархию: diary_privacy (главное) -> access_level
        $query = Report::with(['user', 'dreams', 'tags'])
            ->where('status', 'published');
        
        // Фильтрация по доступу с учетом diary_privacy
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
                // 1. Свои отчеты
                $q->where('user_id', $user->id)
                  // 2. Публичные дневники с правильным access_level
                  ->orWhere(function ($publicQuery) use ($friendIds) {
                      $publicQuery->whereHas('user', function ($uq) {
                          $uq->where('diary_privacy', 'public');
                      })
                      ->where(function ($accessQuery) use ($friendIds) {
                          $accessQuery->where('access_level', 'all')
                                      ->orWhere(function ($friendQuery) use ($friendIds) {
                                          if (!empty($friendIds)) {
                                              $friendQuery->where('access_level', 'friends')
                                                         ->whereIn('user_id', $friendIds);
                                          }
                                      });
                      });
                  })
                  // 3. Дневники друзей (diary_privacy = 'friends')
                  ->orWhere(function ($friendDiaryQuery) use ($friendIds) {
                      if (!empty($friendIds)) {
                          $friendDiaryQuery->whereHas('user', function ($uq) {
                              $uq->where('diary_privacy', 'friends');
                          })
                          ->whereIn('user_id', $friendIds);
                      }
                  });
            });
        } else {
            // Для неавторизованных: только публичные дневники + access_level = 'all'
            $query->whereHas('user', function ($userQuery) {
                $userQuery->where('diary_privacy', 'public');
            })
            ->where('access_level', 'all');
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
                    // Получаем данные снов из формы
                    $dreamsData = $request->dreams;
                    
                    // Обрабатываем каждое окно сна - проверяем на серию
                    $processedDreams = [];
                    $seriesCount = 0;
                    
                    foreach ($dreamsData as $dreamData) {
                        if (!isset($dreamData['description']) || !isset($dreamData['dream_type'])) {
                            continue;
                        }
                        
                        $description = $dreamData['description'];
                        $dreamType = $dreamData['dream_type'];
                        $title = isset($dreamData['title']) ? trim($dreamData['title']) : '';
                        
                        // Проверяем, является ли это серией снов
                        if ($this->isDreamSeries($description)) {
                            // Разбиваем на отдельные сны
                            $splitDreams = $this->splitDreams($description);
                            $seriesCount += count($splitDreams);
                            
                            foreach ($splitDreams as $splitIndex => $splitDescription) {
                                $processedDreams[] = [
                                    'title' => ($splitIndex === 0) ? $title : '', // Название только первому сну серии
                                    'description' => $splitDescription,
                                    'dream_type' => $dreamType,
                                ];
                            }
                        } else {
                            // Обычный сон
                            $processedDreams[] = [
                                'title' => $title,
                                'description' => $description,
                                'dream_type' => $dreamType,
                            ];
                        }
                    }
                    
                    // Проверяем, есть ли хотя бы одно название
                    $hasAnyTitle = false;
                    foreach ($processedDreams as $dreamData) {
                        $title = $dreamData['title'];
                        if ($title !== '' && strtolower($title) !== 'null') {
                            $hasAnyTitle = true;
                            break;
                        }
                    }
                    
                    // Если нет ни одного названия, создаем автоматически для первого сна
                    $autoTitleCreated = false;
                    if (!$hasAnyTitle && !empty($processedDreams)) {
                        $user = auth()->user();
                        $autoTitle = "Отчет {$user->nickname} от " . \Carbon\Carbon::parse($request->report_date)->format('d.m.Y');
                        $processedDreams[0]['title'] = $autoTitle;
                        $autoTitleCreated = true;
                    }
                    
                    // Создаем сны в базе
                    foreach ($processedDreams as $index => $dreamData) {
                        // Обрабатываем title: если пустой, только пробелы или строка "null" - устанавливаем null
                        $title = $dreamData['title'];
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
                    
                    // Уведомления пользователю
                    if ($seriesCount > 0) {
                        session()->flash('info', "Обнаружено и обработано серий снов. Создано {$seriesCount} снов из серий.");
                    }
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
            $firstReport = $user->reports()->orderBy('report_date')->first();
            if ($firstReport) {
                $monthsDiff = $firstReport->report_date->diffInMonths(now());
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

            // Обрабатываем каждое окно сна - проверяем на серию
            $processedDreams = [];
            $seriesCount = 0;
            
            foreach ($dreamsData as $dreamData) {
                if (!isset($dreamData['description']) || !isset($dreamData['dream_type'])) {
                    continue;
                }
                
                $description = $dreamData['description'];
                $dreamType = $dreamData['dream_type'];
                $title = isset($dreamData['title']) ? trim($dreamData['title']) : '';
                
                // Проверяем, является ли это серией снов
                if ($this->isDreamSeries($description)) {
                    // Разбиваем на отдельные сны
                    $splitDreams = $this->splitDreams($description);
                    $seriesCount += count($splitDreams);
                    
                    foreach ($splitDreams as $splitIndex => $splitDescription) {
                        $processedDreams[] = [
                            'title' => ($splitIndex === 0) ? $title : '', // Название только первому сну серии
                            'description' => $splitDescription,
                            'dream_type' => $dreamType,
                        ];
                    }
                } else {
                    // Обычный сон
                    $processedDreams[] = [
                        'title' => $title,
                        'description' => $description,
                        'dream_type' => $dreamType,
                    ];
                }
            }
            
            // Создаем новые сны
            foreach ($processedDreams as $index => $dreamData) {
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
            
            // Уведомляем пользователя, если были разделены сны
            if ($seriesCount > 0) {
                session()->flash('info', "Некоторые описания были автоматически разделены на несколько снов по разделителю (---).");
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

    /**
     * Анализ отчёта через DeepSeek
     */
    public function analyze(Request $request, Report $report): RedirectResponse
    {
        \Log::info('Analyze method called', ['report_id' => $report->id, 'user_id' => auth()->id()]);
        
        // Проверяем права доступа
        if ($report->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            \Log::warning('Access denied for analysis', ['report_user' => $report->user_id, 'current_user' => auth()->id()]);
            abort(403, 'У вас нет прав для анализа этого отчёта');
        }

        // Проверяем, что отчёт опубликован
        if ($report->status !== 'published') {
            \Log::info('Report is not published', ['status' => $report->status]);
            return back()->with('error', 'Можно анализировать только опубликованные отчёты');
        }

        // Проверяем, что у отчёта нет анализа
        if ($report->hasAnalysis()) {
            \Log::info('Report already has analysis', ['analysis_id' => $report->analysis_id]);
            return redirect()->route('reports.analysis', $report)
                ->with('info', 'У этого отчёта уже есть анализ');
        }

        // Валидация традиций
        $validated = $request->validate([
            'traditions' => 'nullable|array',
            'traditions.*' => 'in:' . TraditionHelper::validationKeys(),
        ]);

        // Если традиции не выбраны, используем комплексный анализ
        $traditions = $validated['traditions'] ?? ['eclectic'];

        // Загружаем сны отчёта
        $report->load('dreams');

        // Определяем тип анализа: серия или одиночный
        $dreamsCount = $report->dreams->count();
        
        if ($dreamsCount === 0) {
            return back()->with('error', 'В отчёте нет снов для анализа');
        }

        // Проверяем, что все сны не пустые
        $validDreams = $report->dreams->filter(function ($dream) {
            return !empty(trim($dream->description));
        });

        if ($validDreams->count() === 0) {
            return back()->with('error', 'Все сны в отчёте пустые');
        }

        $isSeries = $dreamsCount > 1;

        // Формируем данные для анализа с санитизацией
        $dreamDescriptions = [];
        foreach ($report->dreams as $dream) {
            if (!empty(trim($dream->description))) {
                $cleanedDescription = TextSanitizer::clean(trim($dream->description));
                if (!empty($cleanedDescription)) {
                    $dreamDescriptions[] = $cleanedDescription;
                }
            }
        }

        // Проверяем, что остались валидные описания после санитизации
        if (empty($dreamDescriptions)) {
            return back()->with('error', 'После очистки текста от недопустимых символов все описания снов оказались пустыми');
        }

        // Для DeepSeek нужен один текст (для логирования и сохранения)
        $dreamDescriptionFull = implode("\n---\n", $dreamDescriptions);
        
        // Определяем тип анализа
        $analysisType = $isSeries ? 'series_integrated' : 'single';

        // Создаём запись анализа через DreamAnalyzerController
        try {
            // Проверяем наличие полей в БД
            $hasColumn = DB::getSchemaBuilder()->hasColumn('reports', 'analysis_id');
            \Log::info('DB column check', ['has_analysis_id' => $hasColumn]);
            
            if (!$hasColumn) {
                \Log::error('Migration not executed - analysis_id column missing');
                return back()->with('error', 'Необходимо выполнить миграцию: php artisan migrate');
            }
            
            \Log::info('Starting analysis', [
                'traditions' => $traditions,
                'dreams_count' => $dreamsCount,
                'is_series' => $isSeries,
                'analysis_type' => $analysisType
            ]);
            
            // Генерируем уникальный хеш
            $hash = DreamInterpretation::generateHash();
            
            // Создаём запись анализа со статусом pending
            $interpretation = DreamInterpretation::create([
                'hash' => $hash,
                'user_id' => auth()->id(),
                'ip_address' => request()->ip(),
                'dream_description' => $dreamDescriptionFull,
                'context' => null,
                'traditions' => $traditions,
                'analysis_type' => $analysisType,
                'processing_status' => 'pending',
            ]);
            
            \Log::info('Created interpretation (async)', [
                'id' => $interpretation->id,
                'hash' => $hash,
                'dreams_count' => $dreamsCount,
                'is_series' => $isSeries
            ]);
            
            // Связываем анализ с отчётом
            DB::transaction(function () use ($report, $interpretation) {
                $report->analysis_id = $interpretation->id;
                $report->analyzed_at = now();
                $report->save();
                
                // Связываем отчёт с анализом (обратная связь)
                $interpretation->report_id = $report->id;
                $interpretation->save();
            });
            
            \Log::info('Report linked to interpretation (async)', [
                'report_id' => $report->id,
                'interpretation_id' => $interpretation->id
            ]);
            
            // Редирект на страницу анализа отчёта (анализ запущен, обрабатывается асинхронно)
            return redirect()->route('reports.analysis', $report);
                
        } catch (\Exception $e) {
            return back()->with('error', 'Ошибка при анализе: ' . $e->getMessage());
        }
    }

    /**
     * Просмотр анализа отчёта
     */
    public function showAnalysis(Report $report): View|RedirectResponse
    {
        // Проверяем права доступа через Policy (учитывает diary_privacy + access_level)
        // Анализ наследует доступ от отчета
        if (!\Illuminate\Support\Facades\Gate::allows('view', $report)) {
            $owner = $report->user;
            
            // Определяем причину отказа
            if (!auth()->check()) {
                session()->flash('access_reason', 'not_authenticated');
            } elseif ($owner->diary_privacy === 'private') {
                session()->flash('access_reason', 'private_diary');
            } elseif ($owner->diary_privacy === 'friends' || $report->access_level === 'friends') {
                session()->flash('access_reason', 'friends_only');
            }
            session()->flash('owner_name', $owner->nickname);
            session()->flash('owner_id', $owner->id);
            
            abort(403);
        }

        // Проверяем наличие анализа
        if (!$report->hasAnalysis()) {
            return redirect()->route('reports.show', $report)
                ->with('error', 'У этого отчёта нет анализа');
        }

        // Загружаем анализ с результатом
        $report->load(['analysis.result', 'dreams']);
        
        $interpretation = $report->analysis;
        
        // НЕ запускаем обработку здесь - страница должна загружаться быстро
        // Обработка запустится автоматически через фоновый AJAX запрос
        
        // Если обработка застряла (более 5 минут в processing) - сбрасываем на pending
        if ($interpretation->processing_status === 'processing' && 
            $interpretation->processing_started_at && 
            $interpretation->processing_started_at->diffInMinutes(now()) > 5) {
            \Log::warning('Analysis processing timeout', [
                'interpretation_id' => $interpretation->id,
                'started_at' => $interpretation->processing_started_at
            ]);
            $interpretation->update(['processing_status' => 'pending', 'processing_started_at' => null]);
        }
        
        // Генерируем SEO данные
        $seo = \App\Helpers\SeoHelper::forReportAnalysis($report, $interpretation);
        
        // Статистика для sidebar (как в DreamAnalyzerController)
        $user = auth()->user();
        $userStats = null;
        $todayReportsCount = 0;
        
        // Общая статистика (с кешированием, как в DreamAnalyzerController)
        $stats = \Illuminate\Support\Facades\Cache::remember('global_statistics', 900, function () {
            $minDate = \Carbon\Carbon::create(2026, 1, 16, 0, 0, 0);
            return [
                'users' => \App\Models\User::count(),
                'reports' => \App\Models\Report::where('status', 'published')->count(),
                'dreams' => \Illuminate\Support\Facades\DB::table('dreams')
                    ->join('reports', 'dreams.report_id', '=', 'reports.id')
                    ->where('reports.status', 'published')
                    ->count(),
                'comments' => \App\Models\Comment::count(),
                'tags' => \App\Models\Tag::count(),
                'interpretations' => \App\Models\DreamInterpretation::where('processing_status', 'completed')
                    ->whereNull('api_error')
                    ->whereHas('result')
                    ->where('created_at', '>=', $minDate)
                    ->count(),
            ];
        });
        
        if ($user) {
            $userReportsCount = $user->reports()->count();
            $userDreamsCount = $user->reports()->withCount('dreams')->get()->sum('dreams_count');
            
            // Друзья - используем friendships
            $friendsCount = $user->friendships()->count();
            
            // Средняя активность
            $monthsSinceRegistration = $user->created_at->diffInMonths(now());
            if ($monthsSinceRegistration < 1) {
                $monthsSinceRegistration = 1;
            }
            $avgDreamsPerMonth = round($userReportsCount / $monthsSinceRegistration, 1);
            
            $userStats = [
                'reports' => $userReportsCount,
                'dreams' => $userDreamsCount,
                'friends' => $friendsCount,
                'avg_per_month' => $avgDreamsPerMonth,
            ];
            
            $todayReportsCount = \App\Models\Report::whereDate('report_date', today())->where('status', 'published')->count();
        }
        
        // Получаем похожие толкования для перелинковки (лимит из настроек)
        $similarInterpretations = \App\Helpers\InterpretationLinkHelper::getSimilarInterpretations($interpretation);
        
        return view('reports.analysis', compact('report', 'interpretation', 'seo', 'userStats', 'todayReportsCount', 'stats', 'similarInterpretations'));
    }

    /**
     * Запустить обработку анализа (вызывается через AJAX)
     */
    public function processAnalysis(Report $report)
    {
        // Проверяем права доступа
        if ($report->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        // Проверяем наличие анализа
        if (!$report->hasAnalysis()) {
            return response()->json(['error' => 'No analysis found'], 404);
        }

        $interpretation = $report->analysis;

        // Проверяем статус
        if ($interpretation->processing_status !== 'pending') {
            return response()->json([
                'status' => $interpretation->processing_status,
                'message' => 'Analysis is already ' . $interpretation->processing_status
            ]);
        }

        // Запускаем обработку
        try {
            $this->processAnalysisAsync($interpretation, $report);
            
            return response()->json([
                'status' => 'completed',
                'message' => 'Analysis completed successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('AJAX analysis processing failed', [
                'interpretation_id' => $interpretation->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'status' => 'failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Отвязать анализ от отчёта (не удаляя сам анализ)
     */
    public function detachAnalysis(Report $report): RedirectResponse
    {
        // Проверяем права доступа
        if ($report->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403, 'У вас нет прав для управления анализом этого отчёта');
        }

        // Проверяем наличие анализа
        if (!$report->hasAnalysis()) {
            return back()->with('error', 'У этого отчёта нет анализа');
        }

        // Отвязываем анализ
        $interpretation = $report->analysis;
        
        $report->analysis_id = null;
        $report->analyzed_at = null;
        $report->save();
        
        // Отвязываем отчёт от анализа (обратная связь)
        if ($interpretation) {
            $interpretation->report_id = null;
            $interpretation->save();
        }

        return redirect()->route('reports.show', $report)
            ->with('success', 'Анализ отвязан от отчёта. Теперь можно создать новый анализ');
    }
    
    /**
     * Асинхронная обработка анализа
     */
    private function processAnalysisAsync(DreamInterpretation $interpretation, Report $report): void
    {
        try {
            // Проверяем, не обрабатывается ли уже
            if ($interpretation->processing_status === 'processing') {
                return;
            }
            
            // Устанавливаем статус processing
            $interpretation->update([
                'processing_status' => 'processing',
                'processing_started_at' => now()
            ]);
            
            \Log::info('Starting async analysis', [
                'interpretation_id' => $interpretation->id,
                'report_id' => $report->id
            ]);
            
            // Получаем данные снов из отчёта
            $dreams = $report->dreams;
            $isSeries = $dreams->count() > 1;
            
            $dreamDescriptions = [];
            foreach ($dreams as $dream) {
                if (!empty(trim($dream->description))) {
                    $dreamDescriptions[] = trim($dream->description);
                }
            }
            
            $dreamDescriptionFull = implode("\n---\n", $dreamDescriptions);
            
            // Выполняем анализ через DeepSeek API
            // Получаем таймаут из настроек
        $phpTimeout = (int) \App\Models\Setting::getValue('deepseek_php_execution_timeout', 660);
        set_time_limit($phpTimeout);
            $deepSeekService = new \App\Services\DeepSeekService();
            
            $result = $deepSeekService->analyzeDream(
                $dreamDescriptionFull,
                null, // context
                $interpretation->traditions ?? [],
                $interpretation->analysis_type,
                $isSeries ? $dreamDescriptions : null
            );
            
            // Обновляем запись с результатами
            $interpretation->update([
                'analysis_data' => $result['analysis_data'] ?? null,
                'raw_api_request' => $result['raw_request'] ?? null,
                'raw_api_response' => $result['raw_response'] ?? null,
                'api_error' => $result['error'] ?? null,
                'processing_status' => !empty($result['analysis_data']) ? 'completed' : 'failed',
            ]);
            
            \Log::info('Async analysis completed', [
                'interpretation_id' => $interpretation->id,
                'has_result' => !empty($result['analysis_data'])
            ]);
            
            // Обрабатываем и сохраняем нормализованные данные
            if (!empty($result['analysis_data'])) {
                $this->saveNormalizedData($interpretation, $result['analysis_data']);
                \Log::info('Normalized data saved (async)', ['interpretation_id' => $interpretation->id]);
            }
            
        } catch (\Exception $e) {
            \Log::error('Async analysis failed', [
                'interpretation_id' => $interpretation->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $interpretation->update([
                'processing_status' => 'failed',
                'api_error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Сохраняет нормализованные данные анализа
     */
    private function saveNormalizedData(DreamInterpretation $interpretation, array $rawAnalysisData): void
    {
        try {
            // Определяем версию формата
            $version = \App\Services\DreamAnalysisAdapters\DreamAnalysisAdapterFactory::detectVersion($rawAnalysisData);
            
            // Получаем адаптер и нормализуем данные
            $adapter = \App\Services\DreamAnalysisAdapters\DreamAnalysisAdapterFactory::getAdapter($version);
            $normalized = $adapter->normalize($rawAnalysisData);

            // Сохраняем нормализованные данные
            $result = \App\Models\DreamInterpretationResult::create([
                'dream_interpretation_id' => $interpretation->id,
                'type' => $normalized['type'],
                'format_version' => $normalized['version'],
                'traditions' => $normalized['traditions'],
                'analysis_type' => $normalized['analysis_type'],
                'recommendations' => $normalized['recommendations'],
            ]);

            if ($normalized['type'] === 'single') {
                // Сохраняем данные для одиночного сна
                $singleAnalysis = $normalized['single_analysis'];
                $result->update([
                    'dream_title' => $singleAnalysis['dream_title'] ?? null,
                    'dream_detailed' => $singleAnalysis['dream_detailed'] ?? null,
                    'dream_type' => $singleAnalysis['dream_type'] ?? null,
                    'key_symbols' => $singleAnalysis['key_symbols'] ?? [],
                    'unified_locations' => $singleAnalysis['unified_locations'] ?? [],
                    'key_tags' => $singleAnalysis['key_tags'] ?? [],
                    'summary_insight' => $singleAnalysis['summary_insight'] ?? null,
                    'emotional_tone' => $singleAnalysis['emotional_tone'] ?? null,
                ]);
            } else {
                // Сохраняем данные для серии снов
                $seriesAnalysis = $normalized['series_analysis'];
                $result->update([
                    'series_title' => $seriesAnalysis['series_title'] ?? null,
                    'overall_theme' => $seriesAnalysis['overall_theme'] ?? null,
                    'emotional_arc' => $seriesAnalysis['emotional_arc'] ?? null,
                    'key_connections' => $seriesAnalysis['key_connections'] ?? [],
                ]);

                // Сохраняем отдельные сны в серии
                foreach ($seriesAnalysis['dreams'] ?? [] as $dreamData) {
                    \App\Models\DreamInterpretationSeriesDream::create([
                        'dream_interpretation_result_id' => $result->id,
                        'dream_number' => $dreamData['dream_number'] ?? 1,
                        'dream_title' => $dreamData['dream_title'] ?? null,
                        'dream_detailed' => $dreamData['dream_detailed'] ?? null,
                        'dream_type' => $dreamData['dream_type'] ?? null,
                        'key_symbols' => $dreamData['key_symbols'] ?? [],
                        'unified_locations' => $dreamData['unified_locations'] ?? [],
                        'key_tags' => $dreamData['key_tags'] ?? [],
                        'summary_insight' => $dreamData['summary_insight'] ?? null,
                        'emotional_tone' => $dreamData['emotional_tone'] ?? null,
                        'connection_to_previous' => $dreamData['connection_to_previous'] ?? null,
                    ]);
                }
            }

            \Log::info('Normalized data saved successfully', [
                'interpretation_id' => $interpretation->id,
                'result_id' => $result->id,
                'type' => $normalized['type']
            ]);
            
            // Сохраняем SEO метаданные, если они есть в ответе
            $this->saveSeoMetadata($interpretation, $rawAnalysisData);
        } catch (\Exception $e) {
            \Log::error('Failed to save normalized data', [
                'interpretation_id' => $interpretation->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            // Не бросаем исключение, чтобы не прерывать процесс создания анализа
        }
    }
    
    /**
     * Сохраняет SEO метаданные из ответа DeepSeek
     */
    private function saveSeoMetadata(DreamInterpretation $interpretation, array $rawAnalysisData): void
    {
        try {
            // Проверяем наличие seo_metadata в ответе
            if (!isset($rawAnalysisData['seo_metadata']) || !is_array($rawAnalysisData['seo_metadata'])) {
                return; // Нет SEO данных - ничего не делаем
            }
            
            $seoData = $rawAnalysisData['seo_metadata'];
            
            // Очищаем все поля от HTML
            $metaTitle = !empty($seoData['meta_title']) ? strip_tags($seoData['meta_title']) : null;
            $metaDescription = !empty($seoData['meta_description']) ? strip_tags($seoData['meta_description']) : null;
            $h1 = !empty($seoData['h1']) ? strip_tags($seoData['h1']) : null;
            $introText = !empty($seoData['intro_text']) ? strip_tags($seoData['intro_text']) : null;
            
            // Если все поля пустые - не создаем запись
            if (empty($metaTitle) && empty($metaDescription) && empty($h1) && empty($introText)) {
                return;
            }
            
            // Ищем существующую SEO запись для этого толкования
            $seoMeta = \App\Models\SeoMeta::where('page_type', 'dream-analyzer-result')
                ->where('page_id', $interpretation->id)
                ->first();
            
            // Подготавливаем данные для сохранения (только непустые поля)
            $seoDataToSave = [
                'page_type' => 'dream-analyzer-result',
                'page_id' => $interpretation->id,
                'is_active' => true,
                'priority' => 0,
            ];
            
            if (!empty($metaTitle)) {
                $seoDataToSave['title'] = $metaTitle;
                $seoDataToSave['og_title'] = $metaTitle; // OG Title из meta_title
            }
            
            if (!empty($metaDescription)) {
                $seoDataToSave['description'] = $metaDescription;
                $seoDataToSave['og_description'] = $metaDescription; // OG Description из meta_description
            }
            
            if (!empty($h1)) {
                $seoDataToSave['h1'] = $h1;
            }
            
            if (!empty($introText)) {
                $seoDataToSave['h1_description'] = $introText;
            }
            
            // Создаем или обновляем запись
            if ($seoMeta) {
                $seoMeta->update($seoDataToSave);
            } else {
                \App\Models\SeoMeta::create($seoDataToSave);
            }
        } catch (\Exception $e) {
            // Логируем ошибку, но не прерываем процесс
            \Log::error('Ошибка при сохранении SEO метаданных', [
                'interpretation_id' => $interpretation->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
