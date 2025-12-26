<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DeepSeekService
{
    private string $apiKey;
    private string $baseUrl = 'https://api.deepseek.com';

    public function __construct()
    {
        $this->apiKey = Setting::getValue('deepseek_api_key', '');
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

        // Подготавливаем данные для запроса
        $requestData = [
            'model' => 'deepseek-chat',
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt,
                ],
            ],
            'temperature' => 0.7,
            'max_tokens' => 8000,
        ];

        // Логируем запрос (без API ключа)
        Log::info('DeepSeek API Request Data', [
            'url' => "{$this->baseUrl}/chat/completions",
            'request_data' => $requestData,
            'prompt_length' => strlen($prompt),
            'prompt_preview' => substr($prompt, 0, 500),
        ]);

        try {
            $response = Http::timeout(150)
                ->connectTimeout(30)
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
            $analysisData = $this->parseJsonResponse($content);
            
            // Сохраняем ПОЛНЫЙ content в analysis_data
            if (!is_array($analysisData)) {
                $analysisData = [];
            }
            // Сохраняем весь content полностью
            $analysisData['full_content'] = $content;
            
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
                Log::warning('DeepSeek API JSON Parse Error', [
                    'content' => $content,
                    'parse_error' => $analysisData['parse_error'],
                ]);
            }

            return [
                'success' => true,
                'analysis_data' => $analysisData,
                'raw_request' => json_encode($requestData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                'raw_response' => $rawResponse,
            ];
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('DeepSeek API Connection Error', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);

            return [
                'success' => false,
                'error' => 'Ошибка подключения к API: ' . $e->getMessage(),
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
        // Переводим традиции в читаемый формат
        $traditionNames = [
            'freudian' => 'фрейдистской',
            'jungian' => 'юнгианской',
            'cognitive' => 'когнитивной',
            'symbolic' => 'символической',
            'shamanic' => 'шаманистической',
            'gestalt' => 'гештальт',
            'lucid_centered' => 'практики осознанных сновидений',
            'eclectic' => 'комплексной',
        ];
        
        $traditionsText = [];
        foreach ($traditions as $tradition) {
            $key = strtolower($tradition);
            $traditionsText[] = $traditionNames[$key] ?? $tradition;
        }
        $traditionsList = implode(', ', $traditionsText);
        
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
        $prompt .= "ЯЗЫК ОТВЕТА: RU\n";
        $prompt .= "ТИП АНАЛИЗА: {$analysisType}\n";
        $prompt .= "СОН ДЛЯ АНАЛИЗА: {$dreamDescription}\n\n";
        
        $prompt .= "ВАЖНО: После всего анализа предоставь ответ в формате JSON со следующей структурой, только json без лишнего текста:\n";
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
        $prompt .= "  },\n";
        $prompt .= "  \"recommendations\": [\n";
        $prompt .= "    \"Рекомендация 1 на основе анализа\",\n";
        $prompt .= "    \"Рекомендация 2\",\n";
        $prompt .= "    \"...\"\n";
        $prompt .= "  ]\n";
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
        // Переводим традиции в читаемый формат
        $traditionNames = [
            'freudian' => 'фрейдистской',
            'jungian' => 'юнгианской',
            'cognitive' => 'когнитивной',
            'symbolic' => 'символической',
            'shamanic' => 'шаманистической',
            'gestalt' => 'гештальт',
            'lucid_centered' => 'практики осознанных сновидений',
            'eclectic' => 'комплексной',
        ];
        
        $traditionsText = [];
        foreach ($traditions as $tradition) {
            $key = strtolower($tradition);
            $traditionsText[] = $traditionNames[$key] ?? $tradition;
        }
        $traditionsList = implode(', ', $traditionsText);
        
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
        $prompt .= "4. Тон: поддерживающий, уверенный, видящий прогресс.\n\n";
        
        $prompt .= "ВАЖНО: После всего анализа предоставь ответ в формате JSON со следующей структурой, только json без лишнего текста:\n";
        $prompt .= "{\n";
        $prompt .= "  \"series_analysis\": {\n";
        $prompt .= "    \"series_title\": \"Общее название для серии снов\",\n";
        $prompt .= "    \"traditions\": {$traditionsJson},\n";
        $prompt .= "    \"analysis_type\": \"{$analysisType}\",\n";
        $prompt .= "    \"overall_theme\": \"ОБЩИЙ ПОСЫЛ ВСЕЙ СЕРИИ СНОВ — как они связаны между собой и какую общую тему развивают.\",\n";
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
        $prompt .= "  ]\n";
        $prompt .= "}\n\n";
        
        $prompt .= "СЕРИЯ СНОВ ДЛЯ АНАЛИЗА:\n";
        foreach ($dreams as $index => $dream) {
            $dreamNumber = $index + 1;
            $prompt .= "- Сон {$dreamNumber}: {$dream}\n";
        }
        
        return $prompt;
    }

    /**
     * Парсинг JSON из ответа API
     */
    private function parseJsonResponse(string $content): array
    {
        // Пытаемся найти JSON в ответе (может быть обернут в markdown код)
        $originalContent = $content;
        
        // Исправляем кодировку UTF-8 если нужно
        if (!mb_check_encoding($content, 'UTF-8')) {
            $content = mb_convert_encoding($content, 'UTF-8', 'UTF-8');
        }
        
        $content = trim($content);

        // Сначала пытаемся найти JSON внутри markdown блока ```json ... ```
        // Поддержка обрезанного JSON (может не быть закрывающего ```)
        if (preg_match('/```json\s*\n(.*?)(?:\n```|$)/is', $content, $matches)) {
            $jsonString = trim($matches[1]);
            $decoded = json_decode($jsonString, true, 512, JSON_INVALID_UTF8_SUBSTITUTE);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                Log::info('DeepSeek API JSON Parsed Successfully (from markdown block)');
                return $decoded;
            }
            
            // Если не получилось, пробуем "восстановить" обрезанный JSON
            // Ищем последнюю закрывающую скобку для корневого объекта
            $openBraces = 0;
            $lastValidPos = -1;
            for ($i = 0; $i < strlen($jsonString); $i++) {
                $char = $jsonString[$i];
                // Пропускаем строки в кавычках
                if ($char === '"' && ($i === 0 || $jsonString[$i-1] !== '\\')) {
                    // Находим конец строки
                    $i++;
                    while ($i < strlen($jsonString) && ($jsonString[$i] !== '"' || $jsonString[$i-1] === '\\')) {
                        $i++;
                    }
                    continue;
                }
                if ($char === '{') {
                    $openBraces++;
                } elseif ($char === '}') {
                    $openBraces--;
                    if ($openBraces === 0) {
                        $lastValidPos = $i;
                        break;
                    }
                }
            }
            
            if ($lastValidPos > 0) {
                $jsonString = substr($jsonString, 0, $lastValidPos + 1);
                $decoded = json_decode($jsonString, true, 512, JSON_INVALID_UTF8_SUBSTITUTE);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    Log::info('DeepSeek API JSON Parsed Successfully (from markdown block, repaired)');
                    return $decoded;
                }
            }
        }
        
        // Также пробуем найти JSON после ```json даже без закрывающего ```
        // (на случай обрезанного JSON)
        $jsonBlockStart = strpos($content, '```json');
        if ($jsonBlockStart !== false) {
            $jsonLineStart = strpos($content, "\n", $jsonBlockStart);
            if ($jsonLineStart !== false) {
                $jsonString = substr($content, $jsonLineStart + 1);
                // Пытаемся найти начало JSON объекта
                $braceStart = strpos($jsonString, '{');
                if ($braceStart !== false) {
                    $jsonString = substr($jsonString, $braceStart);
                    
                    // Пытаемся найти последнюю закрывающую скобку для верхнего уровня
                    // Это поможет обработать обрезанный JSON
                    $openBraces = 0;
                    $lastValidBrace = -1;
                    for ($i = 0; $i < strlen($jsonString); $i++) {
                        if ($jsonString[$i] === '{') {
                            $openBraces++;
                        } elseif ($jsonString[$i] === '}') {
                            $openBraces--;
                            if ($openBraces === 0) {
                                $lastValidBrace = $i;
                                break; // Нашли закрывающую скобку верхнего уровня
                            }
                        }
                    }
                    
                    if ($lastValidBrace > 0) {
                        $jsonString = substr($jsonString, 0, $lastValidBrace + 1);
                    }
                    
                    // Пытаемся распарсить
                    $decoded = json_decode($jsonString, true, 512, JSON_INVALID_UTF8_SUBSTITUTE);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        Log::info('DeepSeek API JSON Parsed Successfully (from markdown block, potentially truncated)');
                        return $decoded;
                    }
                }
            }
        }

        // Если не нашли в markdown блоке, убираем markdown код блоки, если есть
        $content = preg_replace('/```json\s*/i', '', $content);
        $content = preg_replace('/```\s*/', '', $content);
        $content = trim($content);

        // Пытаемся распарсить весь контент как JSON
        $decoded = json_decode($content, true, 512, JSON_INVALID_UTF8_SUBSTITUTE);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            Log::info('DeepSeek API JSON Parsed Successfully (direct)');
            return $decoded;
        }

        // Если не получилось, пытаемся найти JSON объект в тексте
        // Ищем начало JSON (может быть после текста)
        $jsonStart = strpos($content, '{');
        
        if ($jsonStart !== false) {
            // Пытаемся найти конец JSON
            $jsonEnd = strrpos($content, '}');
            
            if ($jsonEnd !== false && $jsonEnd > $jsonStart) {
                // Пробуем распарсить с закрывающей скобкой
                $jsonString = substr($content, $jsonStart, $jsonEnd - $jsonStart + 1);
                $decoded = json_decode($jsonString, true, 512, JSON_INVALID_UTF8_SUBSTITUTE);

                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    Log::info('DeepSeek API JSON Parsed Successfully (extracted)');
                    return $decoded;
                }
            }
            
            // Если не получилось, пробуем извлечь JSON до конца строки
            // (на случай обрезанного JSON)
            $jsonString = substr($content, $jsonStart);
            // Убираем текст после последней закрывающей скобки (если есть незакрытые структуры)
            // Попробуем найти последнюю валидную структуру
            $lastBrace = strrpos($jsonString, '}');
            if ($lastBrace !== false) {
                $jsonString = substr($jsonString, 0, $lastBrace + 1);
                $decoded = json_decode($jsonString, true, 512, JSON_INVALID_UTF8_SUBSTITUTE);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    Log::info('DeepSeek API JSON Parsed Successfully (extracted, potentially truncated)');
                    return $decoded;
                }
            }
            
            Log::warning('DeepSeek API JSON Parse Error', [
                'json_error' => json_last_error_msg(),
                'json_start' => $jsonStart,
                'json_end' => $jsonEnd ?? 'not found',
                'content_preview' => substr($content, $jsonStart, 500),
            ]);
        }

        // Если не удалось распарсить, логируем и возвращаем как есть
        Log::error('DeepSeek API JSON Parse Failed', [
            'content_length' => strlen($content),
            'content_preview' => substr($content, 0, 500),
            'json_error' => json_last_error_msg(),
        ]);

        return [
            'raw_content' => $originalContent,
            'parse_error' => 'Не удалось распарсить JSON из ответа API: ' . json_last_error_msg(),
            'json_error_code' => json_last_error(),
        ];
    }
}




















