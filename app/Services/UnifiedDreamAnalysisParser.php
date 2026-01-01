<?php

namespace App\Services;

use App\Models\DreamInterpretation;
use App\Models\DreamInterpretationResult;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Сервис для парсинга ответов от DeepSeek API и сохранения в БД
 * 
 * Обрабатывает унифицированный формат ответов и сохраняет результаты
 * в таблицу dream_interpretation_results
 */
class UnifiedDreamAnalysisParser
{
    /**
     * Парсинг и сохранение результатов анализа
     * 
     * @param DreamInterpretation $interpretation Запись интерпретации
     * @param array $apiResponse Ответ от API (полный JSON ответ)
     * @param string $analysisMode Режим анализа: single, comparative, parallel, integrated
     * @return array Массив созданных записей DreamInterpretationResult
     */
    public function parseAndSave(DreamInterpretation $interpretation, array $apiResponse, string $analysisMode = 'single'): array
    {
        // Извлекаем analysis_report из ответа
        $analysisReport = $this->extractAnalysisReport($apiResponse);
        
        if (!$analysisReport) {
            // Сохраняем raw ответ для отладки
            $errorDetails = [
                'response_structure' => array_keys($apiResponse),
                'has_choices' => isset($apiResponse['choices']),
                'content_preview' => isset($apiResponse['choices'][0]['message']['content']) 
                    ? substr($apiResponse['choices'][0]['message']['content'], 0, 500) 
                    : 'N/A',
            ];
            
            Log::error('UnifiedDreamAnalysisParser: Не удалось извлечь analysis_report', $errorDetails);
            
            throw new \Exception(
                'Не удалось извлечь analysis_report из ответа API. ' .
                'Проверьте логи для деталей. ' .
                'Структура ответа: ' . implode(', ', array_keys($apiResponse))
            );
        }

        // Сохраняем в зависимости от режима
        switch ($analysisMode) {
            case 'single':
                return $this->saveSingle($interpretation, $analysisReport);
            
            case 'comparative':
                return $this->saveComparative($interpretation, $analysisReport);
            
            case 'parallel':
                return $this->saveParallel($interpretation, $analysisReport);
            
            case 'integrated':
                return $this->saveIntegrated($interpretation, $analysisReport);
            
            default:
                throw new \InvalidArgumentException("Неизвестный режим анализа: {$analysisMode}");
        }
    }

    /**
     * Извлечь analysis_report из ответа API
     */
    private function extractAnalysisReport(array $apiResponse): ?array
    {
        // Логируем структуру ответа для отладки
        Log::info('UnifiedDreamAnalysisParser: Начало извлечения analysis_report', [
            'response_keys' => array_keys($apiResponse),
            'has_choices' => isset($apiResponse['choices']),
        ]);
        
        // Проверяем стандартную структуру ответа
        if (isset($apiResponse['choices'][0]['message']['content'])) {
            $content = $apiResponse['choices'][0]['message']['content'];
            
            Log::info('UnifiedDreamAnalysisParser: Получен content', [
                'content_length' => strlen($content),
                'content_preview' => substr($content, 0, 500),
            ]);
            
            // Пытаемся распарсить JSON
            $jsonData = $this->parseJsonFromContent($content);
            
            if ($jsonData) {
                Log::info('UnifiedDreamAnalysisParser: JSON распарсен', [
                    'json_keys' => array_keys($jsonData),
                    'has_analysis_report' => isset($jsonData['analysis_report']),
                    'first_level_keys' => array_slice(array_keys($jsonData), 0, 10), // Первые 10 ключей для диагностики
                ]);
                
                // Проверяем разные возможные ключи
                if (isset($jsonData['analysis_report'])) {
                    Log::info('UnifiedDreamAnalysisParser: Найден analysis_report');
                    return $jsonData['analysis_report'];
                }
                
                if (isset($jsonData['dream_analysis'])) {
                    Log::info('UnifiedDreamAnalysisParser: Найден dream_analysis');
                    return $jsonData['dream_analysis'];
                }
                
                // Если нет analysis_report/dream_analysis, но есть другие ключи - может быть структура другая
                // Проверяем, может быть данные уже в корне JSON
                $analysisKeys = ['dream_metadata', 'core_analysis', 'symbolic_elements', 'practical_guidance', 'recommendations'];
                $foundKeys = array_intersect($analysisKeys, array_keys($jsonData));
                
                if (!empty($foundKeys)) {
                    Log::info('UnifiedDreamAnalysisParser: Данные анализа найдены в корне JSON', [
                        'found_keys' => $foundKeys,
                    ]);
                    return $jsonData; // Возвращаем весь JSON как analysis_report
                }
                
                // Если JSON распарсен, но нет нужных ключей - логируем для отладки
                Log::warning('UnifiedDreamAnalysisParser: JSON распарсен, но не найдены ожидаемые ключи', [
                    'json_keys' => array_keys($jsonData),
                    'content_preview' => substr($content, 0, 1000),
                ]);
            } else {
                Log::warning('UnifiedDreamAnalysisParser: Не удалось распарсить JSON из content', [
                    'content_preview' => substr($content, 0, 500),
                ]);
            }
        }
        
        // Если структура не стандартная, логируем для отладки
        Log::error('UnifiedDreamAnalysisParser: Не удалось извлечь analysis_report', [
            'response_keys' => array_keys($apiResponse),
            'response_preview' => json_encode(array_slice($apiResponse, 0, 3), JSON_UNESCAPED_UNICODE),
        ]);
        
        return null;
    }

    /**
     * Парсинг JSON из контента (может быть обёрнут в markdown блок)
     */
    private function parseJsonFromContent(string $content): ?array
    {
        // Пробуем найти JSON в markdown блоке (```json ... ```)
        if (preg_match('/```json\s*([\s\S]*?)\s*```/', $content, $matches)) {
            $jsonContent = json_decode($matches[1], true);
            if ($jsonContent && json_last_error() === JSON_ERROR_NONE) {
                Log::info('UnifiedDreamAnalysisParser: JSON найден в markdown блоке');
                return $jsonContent;
            } else {
                Log::warning('UnifiedDreamAnalysisParser: Ошибка парсинга JSON из markdown блока', [
                    'json_error' => json_last_error_msg(),
                ]);
            }
        }
        
        // Пробуем найти JSON в markdown блоке без указания языка (``` ... ```)
        if (preg_match('/```\s*([\s\S]*?)\s*```/', $content, $matches)) {
            $jsonContent = json_decode($matches[1], true);
            if ($jsonContent && json_last_error() === JSON_ERROR_NONE) {
                Log::info('UnifiedDreamAnalysisParser: JSON найден в markdown блоке (без языка)');
                return $jsonContent;
            }
        }
        
        // Пробуем найти JSON объект напрямую (начинается с {)
        $jsonStart = strpos($content, '{');
        if ($jsonStart !== false) {
            // Ищем конец JSON объекта
            $braceCount = 0;
            $jsonEnd = $jsonStart;
            for ($i = $jsonStart; $i < strlen($content); $i++) {
                if ($content[$i] === '{') $braceCount++;
                if ($content[$i] === '}') $braceCount--;
                if ($braceCount === 0) {
                    $jsonEnd = $i + 1;
                    break;
                }
            }
            
            $jsonString = substr($content, $jsonStart, $jsonEnd - $jsonStart);
            $jsonContent = json_decode($jsonString, true);
            if ($jsonContent && json_last_error() === JSON_ERROR_NONE) {
                Log::info('UnifiedDreamAnalysisParser: JSON найден напрямую в тексте');
                return $jsonContent;
            }
        }
        
        // Пробуем весь контент как JSON
        $jsonContent = json_decode($content, true);
        if ($jsonContent && json_last_error() === JSON_ERROR_NONE) {
            Log::info('UnifiedDreamAnalysisParser: Весь content является JSON');
            return $jsonContent;
        }
        
        Log::warning('UnifiedDreamAnalysisParser: Не удалось найти JSON в content', [
            'json_error' => json_last_error_msg(),
            'content_preview' => substr($content, 0, 200),
        ]);
        
        return null;
    }

    /**
     * Сохранить результаты для single режима
     */
    private function saveSingle(DreamInterpretation $interpretation, array $analysisReport): array
    {
        // Получаем название традиции из интерпретации
        $traditions = $interpretation->traditions ?? [];
        $traditionName = !empty($traditions) ? $traditions[0] : 'complex_analysis';
        
        // Создаём запись результата
        $result = DreamInterpretationResult::create([
            'dream_interpretation_id' => $interpretation->id,
            'type' => 'single', // Обязательное поле для новой системы
            'tradition_name' => $traditionName,
            'result_type' => 'tradition',
            'analysis_data' => $analysisReport,
        ]);
        
        Log::info('UnifiedDreamAnalysisParser: Сохранён single анализ', [
            'interpretation_id' => $interpretation->id,
            'tradition_name' => $traditionName,
            'result_id' => $result->id,
        ]);
        
        return [$result];
    }

    /**
     * Сохранить результаты для comparative режима
     */
    private function saveComparative(DreamInterpretation $interpretation, array $analysisReport): array
    {
        $results = [];
        
        // Проверяем структуру ответа
        if (!isset($analysisReport['traditions_results'])) {
            throw new \Exception('Для comparative режима ожидается traditions_results в ответе');
        }
        
        $traditionsResults = $analysisReport['traditions_results'];
        
        // Сохраняем результаты по каждой традиции
        foreach ($traditionsResults as $traditionName => $traditionData) {
            $result = DreamInterpretationResult::create([
                'dream_interpretation_id' => $interpretation->id,
                'type' => 'single', // Обязательное поле
                'tradition_name' => $traditionName,
                'result_type' => 'tradition',
                'analysis_data' => $traditionData,
            ]);
            $results[] = $result;
        }
        
        // Сохраняем comparison если есть
        if (isset($analysisReport['comparison'])) {
            $result = DreamInterpretationResult::create([
                'dream_interpretation_id' => $interpretation->id,
                'type' => 'single', // Обязательное поле
                'tradition_name' => 'comparison',
                'result_type' => 'comparison',
                'analysis_data' => $analysisReport['comparison'],
            ]);
            $results[] = $result;
        }
        
        // Сохраняем synthesis если есть
        if (isset($analysisReport['synthesis'])) {
            $result = DreamInterpretationResult::create([
                'dream_interpretation_id' => $interpretation->id,
                'type' => 'single', // Обязательное поле
                'tradition_name' => 'synthesis',
                'result_type' => 'synthesis',
                'analysis_data' => $analysisReport['synthesis'],
            ]);
            $results[] = $result;
        }
        
        Log::info('UnifiedDreamAnalysisParser: Сохранён comparative анализ', [
            'interpretation_id' => $interpretation->id,
            'results_count' => count($results),
        ]);
        
        return $results;
    }

    /**
     * Сохранить результаты для parallel режима
     */
    private function saveParallel(DreamInterpretation $interpretation, array $analysisReport): array
    {
        $results = [];
        
        // Проверяем структуру ответа
        if (!isset($analysisReport['traditions_results'])) {
            throw new \Exception('Для parallel режима ожидается traditions_results в ответе');
        }
        
        $traditionsResults = $analysisReport['traditions_results'];
        
        // Сохраняем результаты по каждой традиции (БЕЗ comparison и synthesis)
        foreach ($traditionsResults as $traditionName => $traditionData) {
            $result = DreamInterpretationResult::create([
                'dream_interpretation_id' => $interpretation->id,
                'type' => 'single', // Обязательное поле
                'tradition_name' => $traditionName,
                'result_type' => 'tradition',
                'analysis_data' => $traditionData,
            ]);
            $results[] = $result;
        }
        
        Log::info('UnifiedDreamAnalysisParser: Сохранён parallel анализ', [
            'interpretation_id' => $interpretation->id,
            'results_count' => count($results),
        ]);
        
        return $results;
    }

    /**
     * Сохранить результаты для integrated режима
     */
    private function saveIntegrated(DreamInterpretation $interpretation, array $analysisReport): array
    {
        // Для integrated сохраняем unified_analysis как одну запись
        if (!isset($analysisReport['unified_analysis'])) {
            throw new \Exception('Для integrated режима ожидается unified_analysis в ответе');
        }
        
        $result = DreamInterpretationResult::create([
            'dream_interpretation_id' => $interpretation->id,
            'type' => 'single', // Обязательное поле
            'tradition_name' => 'integrated',
            'result_type' => 'integrated',
            'analysis_data' => $analysisReport['unified_analysis'],
        ]);
        
        Log::info('UnifiedDreamAnalysisParser: Сохранён integrated анализ', [
            'interpretation_id' => $interpretation->id,
            'result_id' => $result->id,
        ]);
        
        return [$result];
    }
}

