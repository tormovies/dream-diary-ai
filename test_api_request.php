<?php

/**
 * Тестовый скрипт для проверки нового формата запросов к DeepSeek API
 * 
 * Запуск: php test_api_request.php
 */

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\Http;
use App\Models\Setting;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Тест нового формата запроса к DeepSeek API ===\n\n";

// Получаем API ключ
$apiKey = Setting::getValue('deepseek_api_key', '');

if (empty($apiKey)) {
    die("❌ ERROR: DeepSeek API ключ не найден в настройках.\n");
}

echo "✅ API ключ найден\n\n";

// Загружаем конфиг традиций
$traditions = config('traditions');
$dreamHackers = $traditions['dream_hackers'];

echo "=== Формируем запрос ===\n";

// Формируем запрос по новому шаблону
$request = [
    'model' => 'deepseek-chat',
    'messages' => [
        [
            'role' => 'system',
            'content' => 'Ты — универсальный аналитик сновидений. Проанализируй сон с учётом указанного контекста, используя мульти-традиционный подход. Весь ответ на русском языке в унифицированном JSON формате.',
        ],
        [
            'role' => 'user',
            'content' => json_encode([
                'request_metadata' => [
                    'analysis_version' => '2.0',
                    'request_type' => 'dream_analysis',
                    'request_id' => \Illuminate\Support\Str::uuid()->toString(),
                    'client_platform' => 'web',
                    'analysis_depth' => 'глубокий',
                ],
                
                'analysis_config' => [
                    'mode' => 'single_tradition',
                    
                    'tradition' => [
                        'name' => $dreamHackers['key'],
                        'display_name' => $dreamHackers['name_full'],
                        'tradition_specific_clarification' => $dreamHackers['tradition_specific_clarification'],
                        'analysis_parameters' => $dreamHackers['default_analysis_parameters'],
                        'requested_aspects' => $dreamHackers['available_aspects'],
                    ],
                    
                    'output_format' => 'unified_schema_v1.1',
                    'response_language' => 'ru',
                ],
                
                'user_profile' => [
                    'user_id' => 'test_user',
                    'experience_level' => 'практик',
                    'years_of_practice' => 2,
                    'primary_goals' => ['осознанность', 'исследование'],
                    'current_practices' => ['ведёт дневник', 'делает ПР'],
                ],
                
                'context_summary' => 'Практикую ОС уже 2 года, веду дневник снов, делаю ПР 5-10 раз в день.',
                
                'dream_data' => [
                    'raw_text' => 'Я иду по улице и вдруг замечаю, что дома выглядят странно — текстуры размыты, как в старой видеоигре. Понимаю, что это сон! Пытаюсь стабилизировать фазу, начинаю тереть руки, смотрю на детали. Но картинка начинает мерцать и я просыпаюсь.',
                    'recall_clarity' => 0.9,
                    
                    'sensory_details' => [
                        'visual_vividness' => 0.6,
                        'sound_presence' => false,
                        'tactile_sensations' => true,
                        'color_presence' => 'приглушенные',
                        'unusual_perceptions' => ['размытые_текстуры', 'мерцание_картинки'],
                    ],
                ],
                
                'unified_schema_request' => [
                    'analysis_mode' => 'single',
                    'traditions_to_compare' => [],
                    'comparison_depth' => 'medium',
                    
                    'required_sections' => [
                        'dream_metadata',
                        'core_analysis',
                        'symbolic_elements',
                        'practical_guidance',
                        'recommendations',
                        'context_summary',
                    ],
                    'optional_sections' => [
                        'tradition_specific',
                        'lucidity_analysis',
                    ],
                    
                    'include_tags_and_categories' => true,
                    'tag_types' => ['primary_tags', 'emotional_tags', 'theme_tags', 'skill_tags'],
                    
                    'sections_configuration' => [
                        'dream_metadata' => [
                            'required_fields' => [
                                'dream_title',
                                'dream_type',
                                'summary_insight',
                                'emotional_tone',
                                'raw_text',
                                'context_summary',
                            ],
                            'field_guidelines' => [
                                'dream_title' => 'Метафоричное название на основе главного символа/механизма сна',
                                'dream_type' => 'Тип из: обычный, осознанный, кошмар, пограничный, исследовательский, повторяющийся, пророческий',
                                'summary_insight' => '1-2 фразы с ключевым инсайтом о механизме/функции сна',
                                'emotional_tone' => 'Основной эмоциональный фон',
                            ],
                        ],
                    ],
                    
                    'symbolic_elements_config' => [
                        'include_objects' => true,
                        'include_locations' => true,
                        'include_characters' => true,
                        'include_actions' => true,
                        'categorize_by' => ['emotional_charge', 'symbolic_meaning'],
                    ],
                    
                    'response_structure' => 'v1.1',
                    'language' => 'ru',
                    'detail_level' => 'detailed',
                ],
                
                'analysis_request' => [
                    'specific_questions' => [
                        'Какие глюки матрицы я заметил?',
                        'Как можно было лучше стабилизировать фазу?',
                    ],
                ],
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
        ],
    ],
    'temperature' => 0.7,
    'max_tokens' => 8000,
    'stream' => false,
];

echo "✅ Запрос сформирован\n";
echo "📦 Размер запроса: " . strlen(json_encode($request)) . " байт\n\n";

// Сохраняем запрос для проверки
file_put_contents(__DIR__ . '/storage/test_request.json', json_encode($request, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "💾 Запрос сохранён в storage/test_request.json\n\n";

echo "=== Отправляем запрос к API ===\n";

try {
    $response = Http::withHeaders([
        'Authorization' => 'Bearer ' . $apiKey,
        'Content-Type' => 'application/json',
    ])->timeout(120)->post('https://api.deepseek.com/v1/chat/completions', $request);
    
    $statusCode = $response->status();
    echo "📡 HTTP Status: {$statusCode}\n\n";
    
    if ($response->successful()) {
        $data = $response->json();
        
        // Сохраняем полный ответ
        file_put_contents(__DIR__ . '/storage/test_response.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        echo "✅ Ответ получен и сохранён в storage/test_response.json\n\n";
        
        // Показываем основную информацию
        echo "=== Информация об ответе ===\n";
        echo "Model: " . ($data['model'] ?? 'N/A') . "\n";
        echo "ID: " . ($data['id'] ?? 'N/A') . "\n";
        
        if (isset($data['usage'])) {
            echo "Tokens used: " . ($data['usage']['total_tokens'] ?? 'N/A') . "\n";
            echo "  - Prompt: " . ($data['usage']['prompt_tokens'] ?? 'N/A') . "\n";
            echo "  - Completion: " . ($data['usage']['completion_tokens'] ?? 'N/A') . "\n";
        }
        
        echo "\n=== Содержимое ответа ===\n";
        
        if (isset($data['choices'][0]['message']['content'])) {
            $content = $data['choices'][0]['message']['content'];
            
            // Пытаемся распарсить JSON из ответа
            $jsonContent = null;
            
            // Проверяем, есть ли markdown блок с json
            if (preg_match('/```json\s*([\s\S]*?)\s*```/', $content, $matches)) {
                $jsonContent = json_decode($matches[1], true);
            } else {
                // Пробуем напрямую
                $jsonContent = json_decode($content, true);
            }
            
            if ($jsonContent) {
                echo "✅ JSON успешно распарсен!\n\n";
                
                // Показываем структуру
                echo "Структура ответа:\n";
                foreach ($jsonContent as $key => $value) {
                    $type = is_array($value) ? 'array[' . count($value) . ']' : gettype($value);
                    echo "  - {$key}: {$type}\n";
                }
                
                // Сохраняем распарсенный JSON
                file_put_contents(__DIR__ . '/storage/test_parsed.json', json_encode($jsonContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                echo "\n💾 Распарсенный JSON сохранён в storage/test_parsed.json\n";
            } else {
                echo "⚠️  Не удалось распарсить JSON из ответа\n";
                echo "Первые 500 символов:\n";
                echo substr($content, 0, 500) . "...\n";
            }
        } else {
            echo "❌ Ответ не содержит content\n";
        }
        
    } else {
        echo "❌ Ошибка API: {$statusCode}\n";
        echo $response->body() . "\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}

echo "\n=== Тест завершён ===\n";






