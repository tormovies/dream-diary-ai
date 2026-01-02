<?php

/**
 * Скрипт для демонстрации запроса к API DeepSeek для single tradition (Хакеры сновидений)
 * 
 * Запуск: php show_dream_hackers_request.php
 */

require __DIR__ . '/vendor/autoload.php';

use App\Services\DreamAnalysisRequestBuilder;

// Инициализируем Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Создаем builder
$builder = new DreamAnalysisRequestBuilder();

// Пример параметров для Хакеров сновидений (single tradition)
$params = [
    'tradition' => 'dream_hackers', // Хакеры сновидений
    'dream_text' => 'Я летал над городом, видел знакомые места, но они были немного другими. Чувствовал легкость и свободу. Потом проснулся с ощущением радости.',
    'context' => 'Последние дни много работы, чувствую усталость. Хочется больше свободы и легкости в жизни.',
    'user_id' => 1,
    'user_profile' => [
        'experience_level' => 'практик',
        'years_of_practice' => 2,
        'primary_goals' => ['осознанность', 'исследование'],
        'current_practices' => ['ведёт дневник'],
    ],
    'recall_clarity' => 0.9,
];

// Формируем запрос
$apiRequest = $builder->buildSingleRequest($params);

// Выводим результат в удобном формате для копирования
echo "=== ЗАПРОС К API DEEPSEEK (SINGLE TRADITION - ХАКЕРЫ СНОВИДЕНИЙ) ===\n\n";
echo "URL: https://api.deepseek.com/chat/completions\n";
echo "Method: POST\n";
echo "Headers:\n";
echo "  Content-Type: application/json\n";
echo "  Authorization: Bearer YOUR_API_KEY\n\n";
echo "Body (JSON):\n";
echo json_encode($apiRequest, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
echo "\n\n";

// Также показываем содержимое user сообщения (основной запрос)
if (isset($apiRequest['messages'][1]['content'])) {
    echo "=== СОДЕРЖИМОЕ USER MESSAGE (основной запрос в JSON) ===\n\n";
    $userContent = json_decode($apiRequest['messages'][1]['content'], true);
    echo json_encode($userContent, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    echo "\n\n";
}

echo "=== КОНЕЦ ===\n";

