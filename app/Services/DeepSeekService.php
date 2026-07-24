<?php

namespace App\Services;

use App\Helpers\TraditionHelper;
use App\Models\Setting;
use App\Support\DeepSeekJsonParser;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DeepSeekService
{
    private const MAX_TOKENS_DEFAULT = 8000;

    private const MAX_TOKENS_SERIES = 24000;

    /** Наследник deepseek-chat (снят 2026-07-24). Альтернатива: deepseek-v4-pro. */
    private const DEFAULT_MODEL = 'deepseek-v4-flash';

    private string $apiKey;

    private string $baseUrl = 'https://api.deepseek.com';

    private bool $lastJsonWasRepaired = false;

    public function __construct()
    {
        $this->apiKey = Setting::getValue('deepseek_api_key', '');
    }

    /**
     * Модель API: из настройки deepseek_model или deepseek-v4-flash.
     * Старые deepseek-chat / deepseek-reasoner больше не принимаются API.
     */
    private function resolveModel(): string
    {
        $model = trim((string) Setting::getValue('deepseek_model', self::DEFAULT_MODEL));
        if ($model === '' || in_array($model, ['deepseek-chat', 'deepseek-reasoner'], true)) {
            return self::DEFAULT_MODEL;
        }

        return $model;
    }

    /**
     * Базовые поля chat/completions: без thinking (как бывший deepseek-chat).
     */
    private function buildChatRequest(string $prompt, int $maxTokens): array
    {
        return [
            'model' => $this->resolveModel(),
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt,
                ],
            ],
            'temperature' => 0.7,
            'max_tokens' => $maxTokens,
            // У V4 thinking включён по умолчанию; для JSON-анализов отключаем.
            'thinking' => ['type' => 'disabled'],
        ];
    }

    /**
     * Анализ сна через DeepSeek API
     */
    public function analyzeDream(string $dreamDescription, ?string $context, array $traditions = [], string $analysisType = 'single', ?array $dreams = null): array
    {
        if (empty($this->apiKey)) {
            Log::error('DeepSeek API Key Missing');
            throw new \Exception('DeepSeek API ключ не настроен. Обратитесь к администратору.');
        }

        // Логируем начало запроса (без ключа)
        Log::info('DeepSeek API Request Started', [
            'api_key_length' => strlen($this->apiKey),
            'api_key_prefix' => substr($this->apiKey, 0, 7),
            'base_url' => $this->baseUrl,
            'endpoint' => "{$this->baseUrl}/chat/completions",
            'dream_length' => strlen($dreamDescription),
            'has_context' => !empty($context),
            'traditions' => $traditions,
        ]);

        // Если традиции не выбраны, используем комплексный анализ
        if (empty($traditions)) {
            $traditionsForPrompt = ['eclectic'];
        } else {
            // Нормализуем традиции в нижний регистр
            $traditionsForPrompt = array_map(function ($t) {
                return strtolower($t);
            }, $traditions);
        }

        // Формируем промпт
        $prompt = $this->buildPrompt($dreamDescription, $context, $traditionsForPrompt, $analysisType, $dreams);

        $maxTokens = $this->resolveMaxTokens($analysisType, $dreams);
        $requestData = $this->buildChatRequest($prompt, $maxTokens);

        // Получаем таймауты из настроек (с дефолтными значениями)
        $phpTimeout = (int) \App\Models\Setting::getValue('deepseek_php_execution_timeout', 660);
        $httpTimeout = (int) \App\Models\Setting::getValue('deepseek_http_timeout', 600);
        
        // Таймаут для установки соединения (SSL handshake) используем тот же что и HTTP таймаут из настроек
        $connectTimeout = $httpTimeout;
        
        set_time_limit($phpTimeout); // Таймаут выполнения PHP скрипта

        // Логируем запрос (без API ключа)
        Log::info('DeepSeek API Request Data', [
            'url' => "{$this->baseUrl}/chat/completions",
            'request_data' => $requestData,
            'prompt_length' => strlen($prompt),
            'prompt_preview' => substr($prompt, 0, 500),
            'http_timeout' => $httpTimeout,
            'connect_timeout' => $connectTimeout,
        ]);

        try {
            $response = Http::timeout($httpTimeout)
                ->connectTimeout($connectTimeout)
                ->retry(2, 1000) // Повторная попытка 2 раза с задержкой 1 секунда
                ->withOptions([
                    CURLOPT_SSL_VERIFYPEER => true,
                    CURLOPT_SSL_VERIFYHOST => 2,
                    CURLOPT_TIMEOUT => $httpTimeout,
                    CURLOPT_CONNECTTIMEOUT => $connectTimeout,
                    CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2, // Используем TLS 1.2+
                    CURLOPT_CAINFO => null, // Используем системные сертификаты
                    CURLOPT_CAPATH => null,
                ])
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post("{$this->baseUrl}/chat/completions", $requestData);

            $rawResponse = $response->body();
            
            // Логируем сырой ответ для отладки
            Log::info('DeepSeek API Raw Response', [
                'status' => $response->status(),
                'body_length' => strlen($rawResponse),
                'body_preview' => substr($rawResponse, 0, 500),
            ]);

            if ($response->failed()) {
                $errorData = $response->json();
                $errorMessage = $errorData['error']['message'] ?? $errorData['message'] ?? 'Ошибка API: ' . $response->status();
                Log::error('DeepSeek API Failed', [
                    'status' => $response->status(),
                    'error' => $errorData,
                ]);
                throw new \Exception($errorMessage, $response->status());
            }

            $responseData = $response->json();
            
            // Проверяем структуру ответа
            if (!isset($responseData['choices']) || !is_array($responseData['choices']) || empty($responseData['choices'])) {
                Log::error('DeepSeek API Invalid Response Structure', [
                    'response_data' => $responseData,
                ]);
                throw new \Exception('Неверная структура ответа API: отсутствует поле choices');
            }

            $content = $responseData['choices'][0]['message']['content'] ?? '';
            $finishReason = $responseData['choices'][0]['finish_reason'] ?? null;
            $completionTokens = $responseData['usage']['completion_tokens'] ?? null;

            if (empty($content)) {
                Log::error('DeepSeek API Empty Content', [
                    'response_data' => $responseData,
                ]);
                throw new \Exception('API вернул пустой ответ');
            }

            // Логируем содержимое для отладки
            Log::info('DeepSeek API Content', [
                'content_length' => strlen($content),
                'content_preview' => substr($content, 0, 500),
            ]);

            // Пытаемся распарсить JSON из ответа
            $this->lastJsonWasRepaired = false;
            $parsed = DeepSeekJsonParser::parseFromAssistantContent($content);
            $analysisData = $parsed['data'] ?? [
                'raw_content' => $parsed['raw_content'] ?? $content,
                'parse_error' => $parsed['error'] ?? 'Не удалось распарсить JSON из ответа API',
                'json_error_code' => $parsed['error_code'] ?? json_last_error(),
            ];
            $this->lastJsonWasRepaired = (bool) ($parsed['repaired'] ?? false);
            
            // Сохраняем ПОЛНЫЙ content в analysis_data
            if (!is_array($analysisData)) {
                $analysisData = [];
            }
            // Сохраняем весь content полностью
            $analysisData['full_content'] = $content;
            if ($finishReason !== null) {
                $analysisData['api_finish_reason'] = $finishReason;
            }
            if ($completionTokens !== null) {
                $analysisData['api_completion_tokens'] = $completionTokens;
            }
            
            // Извлекаем текстовую часть (если есть) - все что до JSON блока
            $textAnalysis = '';
            $jsonStart = strpos($content, '```json');
            if ($jsonStart === false) {
                $jsonStart = strpos($content, '{');
            }
            
            if ($jsonStart !== false && $jsonStart > 0) {
                $textAnalysis = trim(substr($content, 0, $jsonStart));
            }
            
            // Если есть текстовая часть, добавляем её в analysis_data
            if (!empty($textAnalysis)) {
                $analysisData['text_analysis'] = $textAnalysis;
            }
            
            // Проверяем, что парсинг прошел успешно
            if (isset($analysisData['parse_error'])) {
                if ($finishReason === 'length') {
                    $analysisData['parse_error'] .= ' (ответ обрезан по лимиту max_tokens='.$maxTokens.')';
                }
                Log::warning('DeepSeek API JSON Parse Error', [
                    'content' => $content,
                    'parse_error' => $analysisData['parse_error'],
                    'finish_reason' => $finishReason,
                    'completion_tokens' => $completionTokens,
                    'max_tokens' => $maxTokens,
                ]);
            }

            return [
                'success' => true,
                'analysis_data' => $analysisData,
                'raw_request' => json_encode($requestData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                'raw_response' => $rawResponse,
                'json_was_repaired' => $this->lastJsonWasRepaired,
                'finish_reason' => $finishReason,
            ];
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('DeepSeek API Connection Error', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);

            return [
                'success' => false,
                'error' => 'Ошибка подключения к API: ' . $e->getMessage(),
                'raw_request' => json_encode($requestData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                'raw_response' => null,
            ];
        } catch (\Exception $e) {
            Log::error('DeepSeek API Error', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'raw_request' => json_encode($requestData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                'raw_response' => $rawResponse ?? null,
            ];
        }
    }

    /**
     * Запрос к DeepSeek для генерации страницы группы сущностей (символа).
     * Возвращает сырой текст ответа (ожидается JSON-массив).
     *
     * @throws \Exception
     */
    public function requestSymbolPage(\App\Models\EntityGroup $group): string
    {
        if (empty($this->apiKey)) {
            Log::error('DeepSeek API Key Missing');
            throw new \Exception('DeepSeek API ключ не настроен.');
        }

        // В промпт передаём только человекочитаемые имена (не slug)
        $slugsWithoutName = $group->mappings->filter(fn ($m) => trim((string) ($m->entity_name ?? '')) === '')->pluck('entity_slug')->unique()->values()->all();
        $slugToName = [];
        if (!empty($slugsWithoutName)) {
            $slugToName = \App\Models\DreamInterpretationEntity::whereIn('slug', $slugsWithoutName)
                ->selectRaw('slug, MAX(name) as name')
                ->groupBy('slug')
                ->pluck('name', 'slug')
                ->toArray();
        }
        $entityNames = $group->mappings->map(function ($m) use ($slugToName) {
            $name = trim((string) ($m->entity_name ?? ''));
            if ($name !== '') {
                return $name;
            }
            return $slugToName[$m->entity_slug] ?? $m->entity_slug; // fallback на slug только если в справочнике нет имени
        })->filter()->unique()->values()->all();

        if (empty($entityNames)) {
            throw new \Exception('В группе нет сущностей. Добавьте хотя бы одну сущность перед запросом.');
        }

        $template = require base_path('config/prompts/entity_group_symbol.php');
        $prompt = str_replace(
            ['{НАЗВАНИЕ_СИМВОЛА}', '{СПИСОК_ВАРИАЦИЙ}'],
            [$group->name, implode(', ', $entityNames)],
            $template
        );

        $requestData = $this->buildChatRequest($prompt, 8000);

        $phpTimeout = (int) \App\Models\Setting::getValue('deepseek_php_execution_timeout', 660);
        $httpTimeout = (int) \App\Models\Setting::getValue('deepseek_http_timeout', 600);
        $connectTimeout = $httpTimeout;
        set_time_limit($phpTimeout);

        $response = Http::timeout($httpTimeout)
            ->connectTimeout($connectTimeout)
            ->retry(2, 1000)
            ->withOptions([
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_TIMEOUT => $httpTimeout,
                CURLOPT_CONNECTTIMEOUT => $connectTimeout,
                CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
                CURLOPT_CAINFO => null,
                CURLOPT_CAPATH => null,
            ])
            ->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])
            ->post("{$this->baseUrl}/chat/completions", $requestData);

        $rawResponse = $response->body();
        if ($response->failed()) {
            $errorData = $response->json();
            $errorMessage = $errorData['error']['message'] ?? $errorData['message'] ?? 'Ошибка API: ' . $response->status();
            throw new \Exception($errorMessage, $response->status());
        }

        $responseData = $response->json();
        if (!isset($responseData['choices'][0]['message']['content'])) {
            throw new \Exception('Неверная структура ответа API.');
        }

        return (string) $responseData['choices'][0]['message']['content'];
    }

    /**
     * Построение промпта для API
     */
    private function buildPrompt(string $dreamDescription, ?string $context, array $traditions, string $analysisType = 'single', ?array $dreams = null): string
    {
        // Если это серия снов, используем специальный шаблон
        if ($dreams !== null && count($dreams) > 1) {
            return $this->buildSeriesPrompt($dreams, $context, $traditions, $analysisType);
        }
        
        // Обычный шаблон для одного сна
        return $this->buildSinglePrompt($dreamDescription, $context, $traditions, $analysisType);
    }

    /**
     * Построение промпта для одного сна
     */
    private function buildSinglePrompt(string $dreamDescription, ?string $context, array $traditions, string $analysisType): string
    {
        // Получаем описания традиций из конфига
        $traditionNames = TraditionHelper::deepSeekDescriptions();
        
        $traditionsText = [];
        foreach ($traditions as $tradition) {
            $key = strtolower($tradition);
            $traditionsText[] = $traditionNames[$key] ?? $tradition;
        }
        
        // Объединяем описания: если больше одной традиции, используем ", а также "
        if (count($traditionsText) === 1) {
            $traditionsList = $traditionsText[0];
        } else {
            $traditionsList = implode(', а также ', $traditionsText);
        }
        
        // Формируем JSON массив традиций для подстановки в шаблон
        $traditionsJson = json_encode($traditions, JSON_UNESCAPED_UNICODE);
        
        $prompt = "Ты — опытный аналитик {$traditionsList} традиций, специализирующийся на работе со сновидениями. Ты помогаешь расшифровывать сны, опираясь на глубокий контекст длительной внутренней работы.\n";
        
        if ($context) {
            $prompt .= "Контекст пользователя:{$context}\n";
        }
        
        $prompt .= "ИНСТРУКЦИИ ПО АНАЛИЗУ:\n";
        $prompt .= "1. Сначала дай ОБЩИЙ ПОСЫЛ сна — одну-две фразы о ключевой теме.\n";
        $prompt .= "2. Затем сделай ДЕТАЛЬНЫЙ АНАЛИЗ, связывая каждый символ сна с контекстом. Объясняй, как сон продолжает предыдущий контекст.\n";
        $prompt .= "3. Заверши КРАТКИМИ ПРАКТИЧЕСКИМИ РЕКОМЕНДАЦИЯМИ на основе инсайтов.\n";
        $prompt .= "4. Тон: поддерживающий, уверенный, видящий прогресс. Анализ должен быть глубоким, но не академичным.\n";
        
        // Собираем специфические промпты для всех выбранных традиций
        $traditionSpecificPrompts = [];
        $defaultPrompt = "определение основного сюжета и эмоционального тона сна.\n выявление ключевых символов и их возможных значений.\n анализ связи сна с текущей жизненной ситуацией.\n оценка ясности воспоминания и детализации.\n практические рекомендации для осмысления сна.\n простые техники для работы с повторяющимися темами.\n базовые советы для ведения дневника сновидений.\n определение возможных тем для дальнейшего исследования.\n";
        
        foreach ($traditions as $tradition) {
            $traditionKey = strtolower($tradition);
            $traditionPrompt = TraditionHelper::singleTraditionPrompt($traditionKey);
            if ($traditionPrompt !== null) {
                $traditionSpecificPrompts[] = $traditionPrompt;
            }
        }
        
        // Если есть специфические промпты - объединяем их через "\n\n", иначе используем дефолтный
        if (!empty($traditionSpecificPrompts)) {
            $instructionText = implode("\n\n", $traditionSpecificPrompts);
        } else {
            $instructionText = $defaultPrompt;
        }
        $prompt .= "5. {$instructionText} \n";
        $prompt .= "ЯЗЫК ОТВЕТА: RU\n";
        $prompt .= "ТИП АНАЛИЗА: {$analysisType}\n";
        $prompt .= "СОН ДЛЯ АНАЛИЗА: {$dreamDescription}\n\n";
        
        $prompt .= "ВАЖНО: После всего анализа предоставь ответ в формате JSON где все текстовые поля содержат HTML-разметку (только теги: h2, h3, p, ul, li, strong, em). Пример:\n{\n  \"dream_analysis\": {\n    \"dream_title\": \"<h2>Название сна</h2>\",\n    \"dream_detailed\": \"<p>Первый абзац...</p><p>Второй абзац...</p>\",\n    \"key_symbols\": [\n      {\"symbol\": \"<strong>Символ</strong>\", \"meaning\": \"<p>Значение...</p>\"}\n    ]\n  }\n}, только json без лишнего текста, название переменной на английском языке, значение переменной на русском языке, без аббревиатур, сокращений, замена нерусских терминов и слов русскими аналогами (например, 'reality check' -> 'проверка реальности', 'ПР' -> 'проверка реальности', 'lucidity' -> 'осознанность'), Не используй китайские, японские, корейские иероглифы или слова. Используй только русскую кириллицу и стандартные знаки препинания. Пример запрета: '表演' → 'манипуляция'. Внутри значений JSON не используй ASCII-кавычки (\") — только «ёлочки». \n";
        $prompt .= "{\n";
        $prompt .= "  \"dream_analysis\": {\n";
        $prompt .= "    \"traditions\": {$traditionsJson},\n";
        $prompt .= "    \"analysis_type\": \"{$analysisType}\",\n";
        $prompt .= "    \"dream_title\": \"Предложенное название сна на основе его основной темы\",\n";
        $prompt .= "    \"dream_detailed\": \"Детальный анализ сновидения\",\n";
        $prompt .= "    \"dream_type\": \"Тип сна (выбери один: архетипический/бытовой/осознанный/кошмар/пророческий/повторяющийся/исследовательский и т.д.)\",\n";
        $prompt .= "    \"key_symbols\": [\n";
        $prompt .= "      {\"symbol\": \"название символа\", \"meaning\": \"его значение в контексте истории пользователя\"},\n";
        $prompt .= "      {\"symbol\": \"...\", \"meaning\": \"...\"}\n";
        $prompt .= "    ],\n";
        $prompt .= "    \"unified_locations\": [\n";
        $prompt .= "      \"стандартизированное название локации (из известных: Дом, Метро, Поле боя, Офис, Школа, Лес и т.д.)\",\n";
        $prompt .= "      \"...\"\n";
        $prompt .= "    ],\n";
        $prompt .= "    \"key_tags\": [\n";
        $prompt .= "      \"тег1 (например: интеграция, сила, границы, творчество)\",\n";
        $prompt .= "      \"тег2\",\n";
        $prompt .= "      \"...\"\n";
        $prompt .= "    ],\n";
        $prompt .= "    \"summary_insight\": \"ОБЩИЙ ПОСЫЛ сна — одну-две фразы о ключевой теме\",\n";
        $prompt .= "    \"emotional_tone\": \"Эмоциональный тон сна (нейтральный, тревожный, радостный, исследовательский и т.д.)\"\n";
        
        // Используем тот же текст для dream_tradition, что и в инструкциях
        $prompt .= "    \"dream_tradition\": \"{$instructionText}\"\n";
        $prompt .= "  },\n";
        $prompt .= "  \"recommendations\": [\n";
        $prompt .= "    \"Рекомендация 1 на основе анализа\",\n";
        $prompt .= "    \"Рекомендация 2\",\n";
        $prompt .= "    \"...\"\n";
        $prompt .= "  ],\n";
        $prompt .= "  \"seo_metadata\": {\n";
        $prompt .= "    \"meta_title\": \"SEO заголовок для страницы (55-60 символов, должен быть информативным и привлекательным для поисковых систем)\",\n";
        $prompt .= "    \"meta_description\": \"SEO описание для страницы (150-160 символов, краткое описание содержания анализа, привлекающее пользователей)\",\n";
        $prompt .= "    \"h1\": \"Заголовок H1 для страницы анализа (главный заголовок страницы, отражающий суть толкования)\",\n";
        $prompt .= "    \"intro_text\": \"Вступительный текст под H1 (100-200 слов, краткое введение к анализу, подготавливающее читателя к основному содержанию)\"\n";
        $prompt .= "  },\n";
        $prompt .= "  \"context_for_next_analysis\": {\n";
        $prompt .= "    \"format_version\": \"1.0\",\n";
        $prompt .= "    \"key_themes\": [\"тема1\", \"тема2\"],\n";
        $prompt .= "    \"recurring_symbols\": [\n";
        $prompt .= "      {\"symbol\": \"символ\", \"appearances\": 2, \"interpretations\": [\"значение1\"]}\n";
        $prompt .= "    ],\n";
        $prompt .= "    \"emotional_pattern\": \"описание паттерна\",\n";
        $prompt .= "    \"unresolved_questions\": [\"вопрос1\", \"вопрос2\"],\n";
        $prompt .= "    \"recommended_focus\": \"на что обращать внимание\",\n";
        $prompt .= "    \"summary_text\": \"Краткий текст для следующего запроса\"\n";
        $prompt .= "  }\n";
        $prompt .= "}";

        return $prompt;
    }

    /**
     * Построение промпта для серии снов (несколько снов за раз)
     * 
     * Используется когда пользователь вводит несколько снов в одном поле, разделенных:
     * - Разделителем из минусов (---, ----, и т.д.)
     * - Пустыми строками (два и более переноса строки подряд)
     * 
     * @param array $dreams Массив строк с описаниями отдельных снов ['сон1', 'сон2', ...]
     * @param string|null $context Контекст пользователя (опционально)
     * @param array $traditions Массив традиций анализа ['freudian', 'jungian', ...]
     * @param string $analysisType Тип анализа (для серии всегда 'series_integrated')
     * @return string Готовый промпт для отправки в API
     * 
     * Структура JSON ответа:
     * {
     *   "series_analysis": {
     *     "series_title": "...",
     *     "traditions": [...],
     *     "analysis_type": "series_integrated",
     *     "overall_theme": "...",
     *     "emotional_arc": "...",
     *     "key_connections": [...]
     *   },
     *   "dreams": [
     *     {
     *       "dream_number": 1,
     *       "dream_title": "...",
     *       "dream_type": "...",
     *       "key_symbols": [...],
     *       "unified_locations": [...],
     *       "key_tags": [...],
     *       "summary_insight": "...",
     *       "emotional_tone": "...",
     *       "connection_to_previous": "..."
     *     },
     *     ...
     *   ],
     *   "recommendations": [...]
     * }
     */
    private function buildSeriesPrompt(array $dreams, ?string $context, array $traditions, string $analysisType): string
    {
        // Получаем описания традиций из конфига
        $traditionNames = TraditionHelper::deepSeekDescriptions();
        
        $traditionsText = [];
        foreach ($traditions as $tradition) {
            $key = strtolower($tradition);
            $traditionsText[] = $traditionNames[$key] ?? $tradition;
        }
        
        // Объединяем описания: если больше одной традиции, используем ", а также "
        if (count($traditionsText) === 1) {
            $traditionsList = $traditionsText[0];
        } else {
            $traditionsList = implode(', а также ', $traditionsText);
        }
        
        // Формируем JSON для традиций
        $traditionsJson = json_encode($traditions, JSON_UNESCAPED_UNICODE);
        
        $prompt = "Ты — опытный аналитик {$traditionsList} традиций, специализирующийся на работе со сновидениями. Ты помогаешь расшифровывать сны, опираясь на глубокий контекст длительной внутренней работы.\n\n";
        
        if ($context) {
            $prompt .= "[{$context}]\n\n";
        }
        
        $prompt .= "ИНСТРУКЦИИ ПО АНАЛИЗУ:\n";
        $prompt .= "Пользователь описывает несколько снов за одну ночь. Проанализируй КАЖДЫЙ сон отдельно, но покажи связь между ними в общем посыле.\n\n";
        $prompt .= "1. Сначала дай ОБЩИЙ ПОСЫЛ ВСЕЙ СЕРИИ СНОВ — как они связаны между собой и какую общую тему развивают.\n";
        $prompt .= "2. Затем для КАЖДОГО СНА ОТДЕЛЬНО:\n";
        $prompt .= "   - Дать краткое название эпизоду\n";
        $prompt .= "   - Детальный анализ сна\n";
        $prompt .= "   - Связь с предыдущими темами из контекста\n";
        $prompt .= "3. Заверши ОБЩИМИ ПРАКТИЧЕСКИМИ РЕКОМЕНДАЦИЯМИ на основе инсайтов из всей серии.\n";
        $prompt .= "4. Тон: поддерживающий, уверенный, видящий прогресс.\n";
        $prompt .= "5. ОГРАНИЧЕНИЕ ОБЪЁМА (обязательно): overall_theme — до 2500 символов; dream_detailed каждого сна — до 1500 символов; meaning у символа — до 400 символов. Иначе JSON не поместится в ответ.\n";
        
        // Собираем специфические промпты для всех выбранных традиций
        $traditionSpecificPrompts = [];
        $defaultPrompt = "определение основного сюжета и эмоционального тона сна.\n выявление ключевых символов и их возможных значений.\n анализ связи сна с текущей жизненной ситуацией.\n оценка ясности воспоминания и детализации.\n практические рекомендации для осмысления сна.\n простые техники для работы с повторяющимися темами.\n базовые советы для ведения дневника сновидений.\n определение возможных тем для дальнейшего исследования.\n";
        
        foreach ($traditions as $tradition) {
            $traditionKey = strtolower($tradition);
            $traditionPrompt = TraditionHelper::singleTraditionPrompt($traditionKey);
            if ($traditionPrompt !== null) {
                $traditionSpecificPrompts[] = $traditionPrompt;
            }
        }
        
        // Если есть специфические промпты - объединяем их через "\n\n", иначе используем дефолтный
        if (!empty($traditionSpecificPrompts)) {
            $instructionText = implode("\n\n", $traditionSpecificPrompts);
        } else {
            $instructionText = $defaultPrompt;
        }
        $prompt .= "6. {$instructionText} \n\n";
        
        $prompt .= "ВАЖНО: После всего анализа предоставь ответ в формате JSON где все текстовые поля содержат HTML-разметку (только теги: h2, h3, p, ul, li, strong, em). Пример:\n{\n  \"dream_analysis\": {\n    \"dream_title\": \"<h2>Название сна</h2>\",\n    \"dream_detailed\": \"<p>Первый абзац...</p><p>Второй абзац...</p>\",\n    \"key_symbols\": [\n      {\"symbol\": \"<strong>Символ</strong>\", \"meaning\": \"<p>Значение...</p>\"}\n    ]\n  }\n}, только json без лишнего текста, название переменной на английском языке, значение переменной на русском языке, без аббревиатур, сокращений, замена не русских терминов русскими аналогами (например, 'reality check' -> 'проверка реальности', 'ПР' -> 'проверка реальности', 'lucidity' -> 'осознанность'), Не используй китайские, японские, корейские иероглифы или слова. Используй только русскую кириллицу и стандартные знаки препинания. Пример запрета: '表演' → 'манипуляция'. Внутри значений JSON не используй ASCII-кавычки (\") — только «ёлочки». \n";
        $prompt .= "ОСОБОЕ ВНИМАНИЕ к блоку seo_metadata: meta_title должен быть ровно 55-60 символов (включая пробелы), meta_description - 150-160 символов, h1 - краткий и информативный заголовок, intro_text - 100-200 слов вступительного текста. Все поля seo_metadata должны быть БЕЗ HTML-разметки, только чистый текст на русском языке.\n";
        $prompt .= "ОСОБОЕ ВНИМАНИЕ к блоку seo_metadata: meta_title должен быть ровно 55-60 символов (включая пробелы), meta_description - 150-160 символов, h1 - краткий и информативный заголовок, intro_text - 100-200 слов вступительного текста. Все поля seo_metadata должны быть БЕЗ HTML-разметки, только чистый текст на русском языке.\n";
        $prompt .= "{\n";
        $prompt .= "  \"series_analysis\": {\n";
        $prompt .= "    \"series_title\": \"Общее название для серии снов\",\n";
        $prompt .= "    \"traditions\": {$traditionsJson},\n";
        $prompt .= "    \"analysis_type\": \"{$analysisType}\",\n";
        $prompt .= "    \"overall_theme\": \"ОБЩИЙ ПОСЫЛ ВСЕЙ СЕРИИ СНОВ — как они связаны между собой и какую общую тему развивают.\",\n";
        $prompt .= "    \"dream_tradition\": \"{$instructionText}\"\n";
        $prompt .= "  },\n";
        $prompt .= "  \"dreams\": [\n";
        $prompt .= "    {\n";
        $prompt .= "            \"dream_number\": 1,\n";
      $prompt .= "      \"dream_title\": \"Название первого сна\",\n";
      $prompt .= "      \"dream_detailed\": \"Детальный анализ этого сна\",\n";
      $prompt .= "      \"dream_type\": \"Тип сна (архетипический/бытовой/осознанный/кошмар/пророческий/повторяющийся/исследовательский)\",\n";
      $prompt .= "      \"key_symbols\": [\n";
      $prompt .= "        {\"symbol\": \"символ1\", \"meaning\": \"значение\"},\n";
      $prompt .= "        {\"symbol\": \"символ2\", \"meaning\": \"значение\"}\n";
      $prompt .= "      ],\n";
      $prompt .= "      \"unified_locations\": [\"локация1\", \"локация2\"],\n";
      $prompt .= "      \"key_tags\": [\"тег1\", \"тег2\", \"тег3\"],\n";
      $prompt .= "      \"summary_insight\": \"Ключевая мысль этого сна\",\n";
      $prompt .= "      \"emotional_tone\": \"Эмоциональный тон\",\n";
      $prompt .= "      \"connection_to_previous\": \"Как связан с предыдущими снами пользователя\"\n";
      $prompt .= "    },\n";
      $prompt .= "    {\n";
      $prompt .= "      \"dream_number\": 2,\n";
      $prompt .= "      \"dream_title\": \"Название второго сна\",\n";
      $prompt .= "      \"dream_detailed\": \"Детальный анализ этого сна\",\n";
      $prompt .= "      \"dream_type\": \"...\",\n";
      $prompt .= "      \"key_symbols\": [...],\n";
      $prompt .= "      \"unified_locations\": [...],\n";
      $prompt .= "      \"key_tags\": [...],\n";
      $prompt .= "      \"summary_insight\": \"...\",\n";
      $prompt .= "      \"emotional_tone\": \"...\",\n";
      $prompt .= "      \"connection_to_previous\": \"...\"\n";
        $prompt .= "    }\n";
        $prompt .= "    // ... и так для каждого сна\n";
        $prompt .= "  ],\n";
        $prompt .= "  \"recommendations\": [\n";
        $prompt .= "    \"Рекомендация 1 на основе всей серии\",\n";
        $prompt .= "    \"Рекомендация 2\",\n";
        $prompt .= "    \"...\"\n";
        $prompt .= "  ],\n";
        $prompt .= "  \"seo_metadata\": {\n";
        $prompt .= "    \"meta_title\": \"SEO заголовок для страницы (55-60 символов, должен быть информативным и привлекательным для поисковых систем)\",\n";
        $prompt .= "    \"meta_description\": \"SEO описание для страницы (150-160 символов, краткое описание содержания анализа, привлекающее пользователей)\",\n";
        $prompt .= "    \"h1\": \"Заголовок H1 для страницы анализа (главный заголовок страницы, отражающий суть толкования)\",\n";
        $prompt .= "    \"intro_text\": \"Вступительный текст под H1 (100-200 слов, краткое введение к анализу, подготавливающее читателя к основному содержанию)\"\n";
        $prompt .= "  },\n";
        $prompt .= "  \"context_for_next_analysis\": {\n";
        $prompt .= "    \"format_version\": \"1.0\",\n";
        $prompt .= "    \"key_themes\": [\"тема1\", \"тема2\"],\n";
        $prompt .= "    \"recurring_symbols\": [\n";
        $prompt .= "      {\"symbol\": \"символ\", \"appearances\": 2, \"interpretations\": [\"значение1\"]}\n";
        $prompt .= "    ],\n";
        $prompt .= "    \"emotional_pattern\": \"описание паттерна\",\n";
        $prompt .= "    \"unresolved_questions\": [\"вопрос1\", \"вопрос2\"],\n";
        $prompt .= "    \"recommended_focus\": \"на что обращать внимание\",\n";
        $prompt .= "    \"summary_text\": \"Краткий текст для следующего запроса\"\n";
        $prompt .= "  }\n";
        $prompt .= "}\n\n";
        
        $prompt .= "СЕРИЯ СНОВ ДЛЯ АНАЛИЗА:\n";
        foreach ($dreams as $index => $dream) {
            $dreamNumber = $index + 1;
            $prompt .= "- Сон {$dreamNumber}: {$dream}\n";
        }
        
        return $prompt;
    }

    private function resolveMaxTokens(string $analysisType, ?array $dreams): int
    {
        $isSeries = $analysisType === 'series_integrated'
            || str_starts_with($analysisType, 'series_')
            || ($dreams !== null && count($dreams) > 1);

        return $isSeries ? self::MAX_TOKENS_SERIES : self::MAX_TOKENS_DEFAULT;
    }
}


























