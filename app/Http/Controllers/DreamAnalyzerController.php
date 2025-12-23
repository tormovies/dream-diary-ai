<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\DreamInterpretation;
use App\Models\Report;
use App\Models\Tag;
use App\Models\User;
use App\Services\DeepSeekService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DreamAnalyzerController extends Controller
{
    /**
     * Получить данные для layout (как на главной странице)
     */
    private function getLayoutData(): array
    {
        $user = auth()->user();
        
        // Статистика проекта
        $stats = Cache::remember('global_statistics', 900, function () {
            return [
                'users' => User::count(),
                'reports' => Report::where('status', 'published')->count(),
                'dreams' => DB::table('dreams')
                    ->join('reports', 'dreams.report_id', '=', 'reports.id')
                    ->where('reports.status', 'published')
                    ->count(),
                'comments' => Comment::count(),
                'tags' => Tag::count(),
            ];
        });
        
        $userStats = null;
        $todayReportsCount = 0;
        
        if ($user) {
            $userReportsCount = $user->reports()->count();
            $userDreamsCount = $user->reports()->withCount('dreams')->get()->sum('dreams_count');
            
            $friendships = \App\Models\Friendship::where(function ($query) use ($user) {
                $query->where('user_id', $user->id)->where('status', 'accepted');
            })->orWhere(function ($query) use ($user) {
                $query->where('friend_id', $user->id)->where('status', 'accepted');
            })->get();

            $friendIds = $friendships->map(function ($friendship) use ($user) {
                return $friendship->user_id === $user->id ? $friendship->friend_id : $friendship->user_id;
            })->toArray();
            
            $friendsCount = count($friendIds);
            
            $firstReport = $user->reports()->orderBy('created_at')->first();
            $monthsDiff = $firstReport ? $firstReport->created_at->diffInMonths(now()) : 0;
            $avgDreamsPerMonth = $monthsDiff > 0 ? round($userDreamsCount / max($monthsDiff, 1), 1) : $userDreamsCount;
            
            $userStats = [
                'reports' => $userReportsCount,
                'dreams' => $userDreamsCount,
                'friends' => $friendsCount,
                'avg_per_month' => $avgDreamsPerMonth,
            ];
            
            $todayReportsCount = Report::where('status', 'published')
                ->where('access_level', 'all')
                ->whereDate('created_at', today())
                ->where('user_id', '!=', $user->id)
                ->count();
        }
        
        return compact('stats', 'userStats', 'todayReportsCount');
    }

    /**
     * Форма для анализа сна
     */
    public function create(): View
    {
        $layoutData = $this->getLayoutData();
        $seo = \App\Helpers\SeoHelper::forDreamAnalyzer();
        return view('dream-analyzer.create', array_merge($layoutData, compact('seo')));
    }

    /**
     * Обработка анализа сна
     */
    public function store(Request $request): RedirectResponse
    {
        // Увеличиваем время выполнения для длительных запросов к API
        set_time_limit(180); // 3 минуты

        $validated = $request->validate([
            'dream_description' => 'required|string|min:10|max:10000',
            'context' => 'nullable|string|max:2000',
            'traditions' => 'nullable|array',
            'traditions.*' => 'in:freudian,jungian,cognitive,symbolic,shamanic,gestalt,eclectic',
            'analysis_type' => [
                'nullable',
                'in:integrated,comparative',
                function ($attribute, $value, $fail) use ($request) {
                    $traditionsCount = count($request->input('traditions', []));
                    if ($traditionsCount > 1 && empty($value)) {
                        $fail('Тип анализа обязателен при выборе нескольких традиций.');
                    }
                },
            ],
        ]);

        // Проверяем, является ли описание серией снов
        $dreamDescription = $validated['dream_description'];
        $isSeries = $this->isDreamSeries($dreamDescription);
        $dreams = [];
        
        if ($isSeries) {
            // Разбиваем на отдельные сны
            $dreams = $this->splitDreams($dreamDescription);
        }

        // Определяем тип анализа
        // Если это серия снов, всегда используем SERIES_INTEGRATED
        if ($isSeries) {
            $analysisType = 'series_integrated';
        } else {
            $traditionsCount = count($validated['traditions'] ?? []);
            if ($traditionsCount <= 1) {
                $analysisType = 'single';
            } else {
                $analysisType = $validated['analysis_type'] ?? 'integrated';
            }
        }

        // Генерируем уникальный хеш
        $hash = DreamInterpretation::generateHash();

        // Создаем запись
        $interpretation = DreamInterpretation::create([
            'hash' => $hash,
            'user_id' => auth()->id(),
            'ip_address' => $request->ip(),
            'dream_description' => $dreamDescription,
            'context' => $validated['context'] ?? null,
            'traditions' => $validated['traditions'] ?? [],
            'analysis_type' => $analysisType,
        ]);

        // Выполняем анализ через API
        $deepSeekService = new DeepSeekService();
        $result = $deepSeekService->analyzeDream(
            $dreamDescription,
            $validated['context'] ?? null,
            $validated['traditions'] ?? [],
            $analysisType,
            $isSeries ? $dreams : null
        );

        // Обновляем запись с результатами
        $interpretation->update([
            'analysis_data' => $result['analysis_data'] ?? null,
            'raw_api_request' => $result['raw_request'] ?? null,
            'raw_api_response' => $result['raw_response'] ?? null,
            'api_error' => $result['success'] ? null : ($result['error'] ?? 'Неизвестная ошибка'),
        ]);

        if (!$result['success']) {
            return redirect()
                ->route('dream-analyzer.create')
                ->with('error', 'Ошибка при анализе: ' . ($result['error'] ?? 'Неизвестная ошибка'))
                ->withInput();
        }

        return redirect()->route('dream-analyzer.show', $hash);
    }

    /**
     * Просмотр результата анализа
     */
    public function show(string $hash): View
    {
        $interpretation = DreamInterpretation::where('hash', $hash)->firstOrFail();
        $layoutData = $this->getLayoutData();
        $seo = \App\Helpers\SeoHelper::forDreamAnalyzerResult($interpretation);

        return view('dream-analyzer.show', array_merge(compact('interpretation'), $layoutData, compact('seo')));
    }

    /**
     * Проверяет, является ли описание серией снов
     */
    private function isDreamSeries(string $text): bool
    {
        // Проверяем наличие разделителя из минусов (3 и более)
        // Может быть на отдельной строке или между переносами строк
        if (preg_match('/(?:^|\n)\s*---{2,}\s*(?:\n|$)/m', $text)) {
            return true;
        }
        
        // Проверяем наличие одной или более пустых строк между блоками текста
        // Это означает минимум два переноса строки подряд (\n\n) - одна пустая строка
        // Или больше (\n\n\n, \n\n\n\n и т.д.) - две и более пустых строк
        // Учитываем возможные пробелы/табы в пустых строках
        // Ищем паттерн: текст -> пустая строка(и) -> текст
        if (preg_match('/[^\n]\s*\n\s*\n\s*[^\n]/', $text)) {
            return true;
        }
        
        return false;
    }

    /**
     * Разбивает текст на отдельные сны
     */
    private function splitDreams(string $text): array
    {
        $dreams = [];
        
        // Сначала пробуем разделить по минусам (3 и более)
        // Может быть на отдельной строке или между переносами строк
        if (preg_match('/(?:^|\n)\s*---{2,}\s*(?:\n|$)/m', $text)) {
            $parts = preg_split('/(?:^|\n)\s*---{2,}\s*(?:\n|$)/m', $text);
            foreach ($parts as $part) {
                $part = trim($part);
                if (!empty($part)) {
                    $dreams[] = $part;
                }
            }
        } 
        // Если минусов нет, разделяем по одной или более пустым строкам
        // Это означает два или более переноса строки подряд (\n\n, \n\n\n и т.д.)
        else if (preg_match('/[^\n]\s*\n\s*\n\s*[^\n]/', $text)) {
            // Разбиваем по двум или более переносам строки подряд (одна или более пустых строк)
            // Учитываем возможные пробелы/табы в пустых строках
            $parts = preg_split('/\n\s*\n+/', $text);
            foreach ($parts as $part) {
                $part = trim($part);
                if (!empty($part)) {
                    $dreams[] = $part;
                }
            }
        }
        
        return $dreams;
    }
}

