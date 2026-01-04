<?php

namespace App\Http\Controllers;

use App\Helpers\TraditionHelper;
use App\Models\Comment;
use App\Models\DreamInterpretation;
use App\Models\DreamInterpretationResult;
use App\Models\DreamInterpretationSeriesDream;
use App\Models\Report;
use App\Models\Tag;
use App\Models\User;
use App\Services\DeepSeekService;
use App\Services\DreamAnalysisAdapters\DreamAnalysisAdapterFactory;
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
            
            $firstReport = $user->reports()->orderBy('report_date')->first();
            $monthsDiff = $firstReport ? $firstReport->report_date->diffInMonths(now()) : 0;
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
        $validated = $request->validate([
            'dream_description' => 'required|string|min:10|max:10000',
            'context' => 'nullable|string|max:2000',
            'traditions' => 'nullable|array',
            'traditions.*' => 'in:' . TraditionHelper::validationKeys(),
            'analysis_type' => [
                'nullable',
                'in:integrated,comparative,parallel',
                function ($attribute, $value, $fail) use ($request) {
                    $traditionsCount = count($request->input('traditions', []));
                    if ($traditionsCount > 1 && empty($value)) {
                        $fail('Тип анализа обязателен при выборе нескольких традиций.');
                    }
                },
            ],
            'force_series' => 'nullable|boolean', // Явно указать, что это серия снов
        ]);

        // Проверяем, является ли описание серией снов
        $dreamDescription = $validated['dream_description'];
        
        // Явное указание на серию имеет приоритет над автоопределением
        if (isset($validated['force_series'])) {
            // Если параметр передан (true или false), используем его значение
            $isSeries = (bool) $validated['force_series'];
            $dreams = [];
            
            if ($isSeries) {
                // Для явно указанной серии разбиваем по разделителям
                $dreams = $this->splitDreams($dreamDescription);
            }
        } else {
            // Автоопределение по разделителям (только если force_series не указан)
            $isSeries = $this->isDreamSeries($dreamDescription);
            $dreams = [];
            
            if ($isSeries) {
                // Разбиваем на отдельные сны
                $dreams = $this->splitDreams($dreamDescription);
            }
        }

        // Определяем тип анализа
        // Если это серия снов, используем старую систему (пока не реализовано в новой)
        if ($isSeries) {
            $analysisType = 'series_integrated';
            
            // Старая система для серий (пока оставляем как есть)
            $hash = DreamInterpretation::generateHash();
            $interpretation = DreamInterpretation::create([
                'hash' => $hash,
                'user_id' => auth()->id(),
                'ip_address' => $request->ip(),
                'dream_description' => $dreamDescription,
                'context' => $validated['context'] ?? null,
                'traditions' => $validated['traditions'] ?? [],
                'analysis_type' => $analysisType,
            ]);

            $deepSeekService = new DeepSeekService();
            $result = $deepSeekService->analyzeDream(
                $dreamDescription,
                $validated['context'] ?? null,
                $validated['traditions'] ?? [],
                $analysisType,
                $dreams
            );

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

            if (!empty($result['analysis_data'])) {
                $this->saveNormalizedData($interpretation, $result['analysis_data']);
            }

            return redirect()->route('dream-analyzer.show', $hash);
        }

        // НОВАЯ СИСТЕМА для single/comparative/parallel/integrated
        $traditions = $validated['traditions'] ?? [];
        $traditionsCount = count($traditions);
        
        // Определяем режим анализа
        if ($traditionsCount <= 1) {
            $analysisMode = 'single';
            $tradition = !empty($traditions) ? $traditions[0] : 'complex_analysis';
        } else {
            $analysisMode = $validated['analysis_type'] ?? 'integrated'; // comparative | parallel | integrated
        }

        // Генерируем уникальный хеш
        $hash = DreamInterpretation::generateHash();

        // Создаем запись со статусом pending (анализ запустится асинхронно на странице результата)
        $interpretation = DreamInterpretation::create([
            'hash' => $hash,
            'user_id' => auth()->id(),
            'ip_address' => $request->ip(),
            'dream_description' => $dreamDescription,
            'context' => $validated['context'] ?? null,
            'traditions' => $traditions,
            'analysis_type' => $analysisMode,
            'processing_status' => 'pending',
        ]);

        // Сразу редиректим на страницу результата (анализ запустится там асинхронно)
        return redirect()->route('dream-analyzer.show', $hash);
    }

    /**
     * Просмотр результата анализа
     */
    public function show(Request $request, string $hash): View
    {
        // Сначала проверяем, нужны ли нам JSON-поля (они большие и редко используются)
        $needsJson = false;
        
        // 1. Проверяем наличие ошибки API и статус обработки (минимальный запрос)
        $preview = DreamInterpretation::where('hash', $hash)
            ->select(['id', 'api_error', 'processing_status', 'processing_started_at'])
            ->first();
        
        if (!$preview) {
            abort(404);
        }
        
        // Если есть ошибка API, нужны JSON-поля для отладки
        if ($preview->api_error) {
            $needsJson = true;
        }
        
        // 2. Админ явно запросил отладочную информацию через ?debug=1
        if (auth()->check() && auth()->user()->isAdmin() && $request->has('debug')) {
            $needsJson = true;
        }
        
        // Проверяем наличие нормализованных данных (нужно для определения, загружать ли analysis_data)
        $result = null;
        $hasNormalizedData = false;
        if (!$needsJson) {
            $result = \App\Models\DreamInterpretationResult::where('dream_interpretation_id', $preview->id)->first();
            
            // Проверяем, есть ли реальные данные в result
            // Новая система: проверяем analysis_data
            // Старая система: проверяем отдельные поля
            if ($result) {
                // Проверяем новую систему (analysis_data)
                if (!empty($result->analysis_data) && is_array($result->analysis_data)) {
                    $hasNormalizedData = true;
                }
                // Проверяем старую систему (отдельные поля)
                elseif (
                    (is_array($result->general_interpretation ?? null) && count($result->general_interpretation) > 0) ||
                    (is_array($result->key_symbols ?? null) && count($result->key_symbols) > 0) ||
                    (is_array($result->emotional_state ?? null) && count($result->emotional_state) > 0) ||
                    (is_array($result->practical_recommendations ?? null) && count($result->practical_recommendations) > 0)
                ) {
                    $hasNormalizedData = true;
                }
            }
            
            // Если нормализованных данных нет, загружаем analysis_data (может быть parse_error)
            if (!$hasNormalizedData) {
                $needsJson = true;
            }
        }
        
        // Формируем основной запрос
        $query = DreamInterpretation::where('hash', $hash);
        
        // Если JSON не нужен, загружаем только необходимые поля (экономия ~20-50 KB на запрос)
        // Но если есть нормализованные данные, нужно загрузить analysis_data для dream_tradition
        // Или если debug=1, загружаем все JSON поля
        if (!$needsJson) {
            $fields = [
                'id', 
                'hash', 
                'dream_description', 
                'context', 
                'traditions', 
                'api_error',
                'processing_status',
                'processing_started_at',
                'created_at', 
                'updated_at'
            ];
            
            // Если есть нормализованные данные, добавляем analysis_data для dream_tradition
            // (может быть в старой системе в interpretation->analysis_data)
            if ($hasNormalizedData) {
                $fields[] = 'analysis_data';
            }
            
            // Если админ запросил debug, добавляем raw_api_request и raw_api_response
            if (auth()->check() && auth()->user()->isAdmin() && $request->has('debug')) {
                $fields[] = 'raw_api_request';
                $fields[] = 'raw_api_response';
            }
            
            $query->select($fields);
        }
        // При debug=1 ($needsJson = true) не используем select(), 
        // чтобы загрузить все поля, включая raw_api_request и raw_api_response
        // Это означает, что все поля будут загружены автоматически
        
        $interpretation = $query->firstOrFail();
        
        // Загружаем результаты отдельно (если используем select(), связи могут не работать)
        if (method_exists($interpretation, 'results')) {
            $interpretation->load(['results' => function($q) {
                $q->orderBy('id');
            }]);
        }
        
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
        
        // Для совместимости со старым view, создаем виртуальное свойство result
        // из первого результата новой системы
        $results = $interpretation->relationLoaded('results') ? $interpretation->results : collect();
        if ($results->count() > 0 && !$interpretation->relationLoaded('result')) {
            // Создаем виртуальный результат для совместимости
            $firstResult = $results->first();
            $interpretation->setRelation('result', $firstResult);
        } else {
            // Загружаем старый результат если нет новых
            if (!$interpretation->relationLoaded('result')) {
                $interpretation->load('result.seriesDreams');
            }
        }
        
        $layoutData = $this->getLayoutData();
        $seo = \App\Helpers\SeoHelper::forDreamAnalyzerResult($interpretation);

        return view('dream-analyzer.show', array_merge(compact('interpretation', 'request'), $layoutData, compact('seo')));
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

    /**
     * Построить профиль пользователя для запроса к API
     */
    private function buildUserProfile($user): array
    {
        if (!$user) {
            return [];
        }

        return [
            'user_id' => (string) $user->id,
            'experience_level' => 'практик', // TODO: можно добавить поле в таблицу users
            'years_of_practice' => 0, // TODO: можно вычислить из первого отчёта
            'primary_goals' => ['осознанность', 'исследование'], // TODO: можно добавить в профиль
            'current_practices' => [], // TODO: можно добавить в профиль
            'tradition_background' => [
                'familiar_with' => [],
                'preferred_concepts' => [],
                'learning_goals' => [],
            ],
        ];
    }

    /**
     * Сохраняет нормализованные данные анализа в новую таблицу
     */
    private function saveNormalizedData(DreamInterpretation $interpretation, array $rawAnalysisData): void
    {
        try {
            // Определяем версию формата
            $version = DreamAnalysisAdapterFactory::detectVersion($rawAnalysisData);
            
            // Получаем адаптер и нормализуем данные
            $adapter = DreamAnalysisAdapterFactory::getAdapter($version);
            $normalized = $adapter->normalize($rawAnalysisData);

            // Сохраняем нормализованные данные
            $result = DreamInterpretationResult::create([
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
                    DreamInterpretationSeriesDream::create([
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
                        'order' => $dreamData['dream_number'] ?? 1,
                    ]);
                }
            }
        } catch (\Exception $e) {
            // Логируем ошибку, но не прерываем процесс
            \Log::error('Ошибка при сохранении нормализованных данных анализа', [
                'interpretation_id' => $interpretation->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Запуск обработки анализа через AJAX (аналог ReportController::processAnalysis)
     */
    public function processAnalysis(Request $request, string $hash)
    {
        $interpretation = DreamInterpretation::where('hash', $hash)->firstOrFail();
        
        // Проверяем права доступа
        // Если user_id NULL - это неавторизованный пользователь, разрешаем обработку
        // Если user_id установлен - проверяем, что это владелец или админ
        if ($interpretation->user_id !== null) {
            if (!auth()->check()) {
                return response()->json([
                    'status' => 'error',
                    'error' => 'Требуется авторизация'
                ], 401);
            }
            
            if ($interpretation->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
                return response()->json([
                    'status' => 'error',
                    'error' => 'У вас нет прав для обработки этого анализа'
                ], 403);
            }
        }
        
        // Проверяем, не обрабатывается ли уже
        if ($interpretation->processing_status === 'processing') {
            return response()->json([
                'status' => 'processing',
                'message' => 'Analysis is already being processed'
            ]);
        }
        
        // Запускаем обработку синхронно (но с увеличенным таймаутом)
        // В реальном приложении лучше использовать очереди, но для простоты делаем синхронно
        try {
            // Увеличиваем лимит времени выполнения
            set_time_limit(660); // 11 минут (10 минут на запрос + 1 минута на обработку)
            
            $this->processAnalysisAsync($interpretation);
            
            return response()->json([
                'status' => 'completed',
                'message' => 'Analysis processing completed'
            ]);
        } catch (\Exception $e) {
            \Log::error('AJAX analysis processing failed', [
                'interpretation_id' => $interpretation->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Асинхронная обработка анализа (аналог ReportController::processAnalysisAsync)
     */
    private function processAnalysisAsync(DreamInterpretation $interpretation): void
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
                'hash' => $interpretation->hash
            ]);
            
            // Загружаем пользователя
            $interpretation->load('user');
            
            // Получаем данные для анализа
            $dreamDescription = $interpretation->dream_description;
            $context = $interpretation->context;
            $traditions = $interpretation->traditions ?? [];
            $analysisMode = $interpretation->analysis_type;
            
            // Используем старую систему DeepSeekService
            set_time_limit(660); // 11 минут (10 минут на запрос + 1 минута на обработку)
            $deepSeekService = new DeepSeekService();
            
            $result = $deepSeekService->analyzeDream(
                $dreamDescription,
                $context,
                $traditions,
                $analysisMode,
                null // dreams для серии
            );
            
            // Логируем что получили от DeepSeekService
            \Log::info('DeepSeekService result keys', [
                'interpretation_id' => $interpretation->id,
                'result_keys' => array_keys($result),
                'has_raw_request' => isset($result['raw_request']),
                'has_raw_response' => isset($result['raw_response']),
                'raw_request_length' => isset($result['raw_request']) ? strlen($result['raw_request']) : 0,
                'raw_response_length' => isset($result['raw_response']) ? strlen($result['raw_response']) : 0,
            ]);
            
            // Обновляем запись с результатами
            $updateData = [
                'analysis_data' => $result['analysis_data'] ?? null,
                'raw_api_request' => $result['raw_request'] ?? null,
                'raw_api_response' => $result['raw_response'] ?? null,
                'api_error' => $result['success'] ? null : ($result['error'] ?? 'Неизвестная ошибка'),
                'processing_status' => $result['success'] ? 'completed' : 'failed',
            ];
            
            \Log::info('Updating interpretation with data', [
                'interpretation_id' => $interpretation->id,
                'has_raw_api_request' => !empty($updateData['raw_api_request']),
                'has_raw_api_response' => !empty($updateData['raw_api_response']),
            ]);
            
            $interpretation->update($updateData);
            
            // Проверяем что сохранилось
            $interpretation->refresh();
            \Log::info('Interpretation after update', [
                'interpretation_id' => $interpretation->id,
                'has_raw_api_request' => !empty($interpretation->raw_api_request),
                'has_raw_api_response' => !empty($interpretation->raw_api_response),
                'raw_api_request_length' => $interpretation->raw_api_request ? strlen($interpretation->raw_api_request) : 0,
                'raw_api_response_length' => $interpretation->raw_api_response ? strlen($interpretation->raw_api_response) : 0,
            ]);
            
            // Сохраняем нормализованные данные если есть
            if ($result['success'] && !empty($result['analysis_data'])) {
                $this->saveNormalizedData($interpretation, $result['analysis_data']);
            }
            
            \Log::info('Async analysis completed', [
                'interpretation_id' => $interpretation->id,
                'success' => $result['success'] ?? false
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Async analysis failed', [
                'interpretation_id' => $interpretation->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Ограничиваем длину сообщения об ошибке
            $errorMessage = $e->getMessage();
            if (strlen($errorMessage) > 500) {
                $errorMessage = substr($errorMessage, 0, 497) . '...';
            }
            
            $interpretation->update([
                'processing_status' => 'failed',
                'api_error' => $errorMessage
            ]);
        }
    }
}

