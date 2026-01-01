<?php

namespace App\Services;

use App\Models\DreamInterpretation;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Сервис для работы с унифицированной системой анализа сновидений
 * 
 * Объединяет формирование запроса, отправку к API, парсинг и сохранение результатов
 */
class UnifiedDreamAnalysisService
{
    private DreamAnalysisRequestBuilder $requestBuilder;
    private UnifiedDreamAnalysisParser $parser;
    private string $apiKey;
    private string $baseUrl = 'https://api.deepseek.com';

    public function __construct()
    {
        $this->requestBuilder = new DreamAnalysisRequestBuilder();
        $this->parser = new UnifiedDreamAnalysisParser();
        $this->apiKey = Setting::getValue('deepseek_api_key', '');
    }

    /**
     * Выполнить анализ сна (single tradition)
     * 
     * @param array $params Параметры анализа
     * @return array Результат анализа с данными для сохранения
     */
    public function analyzeSingle(array $params): array
    {
        if (empty($this->apiKey)) {
            throw new \Exception('DeepSeek API ключ не настроен. Обратитесь к администратору.');
        }

        // Формируем запрос
        $apiRequest = $this->requestBuilder->buildSingleRequest($params);
        
        // Отправляем запрос
        $response = $this->sendRequest($apiRequest);
        
        // Сохраняем запрос и ответ
        $rawRequest = json_encode($apiRequest, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $rawResponse = json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        
        return [
            'success' => true,
            'raw_request' => $rawRequest,
            'raw_response' => $rawResponse,
            'api_response' => $response,
            'analysis_mode' => 'single',
        ];
    }

    /**
     * Выполнить анализ сна (multitradition)
     * 
     * @param array $params Параметры анализа
     * @return array Результат анализа с данными для сохранения
     */
    public function analyzeMultiTradition(array $params): array
    {
        if (empty($this->apiKey)) {
            throw new \Exception('DeepSeek API ключ не настроен. Обратитесь к администратору.');
        }

        // Формируем запрос
        $apiRequest = $this->requestBuilder->buildMultiTraditionRequest($params);
        
        // Отправляем запрос
        $response = $this->sendRequest($apiRequest);
        
        // Сохраняем запрос и ответ
        $rawRequest = json_encode($apiRequest, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $rawResponse = json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        
        return [
            'success' => true,
            'raw_request' => $rawRequest,
            'raw_response' => $rawResponse,
            'api_response' => $response,
            'analysis_mode' => $params['mode'] ?? 'comparative',
        ];
    }

    /**
     * Отправить запрос к API
     */
    private function sendRequest(array $requestData): array
    {
        try {
            // Увеличиваем таймаут до 5 минут (300 секунд) для длительных запросов
            // connectTimeout оставляем 30 секунд
            $response = Http::timeout(300)
                ->connectTimeout(30)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post("{$this->baseUrl}/chat/completions", $requestData);

            if ($response->failed()) {
                $errorData = $response->json();
                $errorMessage = $errorData['error']['message'] ?? $errorData['message'] ?? 'Ошибка API: ' . $response->status();
                
                Log::error('UnifiedDreamAnalysisService: API Failed', [
                    'status' => $response->status(),
                    'error' => $errorData,
                ]);
                
                throw new \Exception($errorMessage, $response->status());
            }

            return $response->json();
            
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            // Специальная обработка для таймаутов
            if (str_contains($e->getMessage(), 'Operation timed out')) {
                Log::error('UnifiedDreamAnalysisService: Request Timeout', [
                    'timeout_after' => '300 seconds',
                    'error' => $e->getMessage(),
                ]);
                throw new \Exception(
                    'Превышено время ожидания ответа от API (более 5 минут). ' .
                    'Попробуйте сократить текст сновидения или попробуйте позже.'
                );
            }
            throw $e;
        } catch (\Exception $e) {
            Log::error('UnifiedDreamAnalysisService: Request Exception', [
                'error' => $e->getMessage(),
                'error_class' => get_class($e),
                'trace' => $e->getTraceAsString(),
            ]);
            
            throw $e;
        }
    }

    /**
     * Парсить и сохранить результаты анализа
     * 
     * @param DreamInterpretation $interpretation Запись интерпретации
     * @param array $apiResponse Ответ от API
     * @param string $analysisMode Режим анализа
     * @return array Массив созданных записей DreamInterpretationResult
     */
    public function parseAndSaveResults(DreamInterpretation $interpretation, array $apiResponse, string $analysisMode): array
    {
        return $this->parser->parseAndSave($interpretation, $apiResponse, $analysisMode);
    }
}

