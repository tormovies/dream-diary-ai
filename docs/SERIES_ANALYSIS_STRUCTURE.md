# Анализ серии снов (несколько снов сразу)

## Функция для анализа нескольких снов

### Основная функция
**Метод**: `DeepSeekService::analyzeDream()`

**Сигнатура**:
```php
public function analyzeDream(
    string $dreamDescription, 
    ?string $context, 
    array $traditions = [], 
    string $analysisType = 'single', 
    ?array $dreams = null
): array
```

### Параметры

1. **`$dreamDescription`** (string)
   - Полное описание всех снов (может содержать несколько снов, разделенных разделителями)
   - Используется для определения, является ли это серией снов

2. **`$context`** (string|null)
   - Контекст пользователя (опционально)
   - Передается в промпт как `[контекст]`

3. **`$traditions`** (array)
   - Массив традиций анализа: `['freudian', 'jungian', 'cognitive', 'symbolic', 'shamanic', 'gestalt', 'eclectic']`
   - Если пустой массив, автоматически используется `['eclectic']`

4. **`$analysisType`** (string)
   - Для серии снов всегда: `'series_integrated'`
   - Автоматически устанавливается в `DreamAnalyzerController`, если обнаружена серия

5. **`$dreams`** (array|null)
   - Массив строк с описаниями отдельных снов
   - Передается только если это серия снов (не null и count > 1)
   - Формат: `['описание первого сна', 'описание второго сна', ...]`

### Определение серии снов

**Функция определения**: `DreamAnalyzerController::isDreamSeries()`

Серия снов определяется по следующим признакам:

1. **Разделитель из минусов**: 3 и более минуса (`---`, `----`, и т.д.)
   - Может быть на отдельной строке или между переносами строк
   - Регулярное выражение: `/(?:^|\n)\s*---{2,}\s*(?:\n|$)/m`

2. **Пустые строки**: два или более подряд идущих переноса строки
   - Регулярное выражение: `/[^\n]\s*\n\s*\n\s*[^\n]/`

### Разбиение на отдельные сны

**Функция разбиения**: `DreamAnalyzerController::splitDreams()`

Сны разделяются по:
1. **Разделителю из минусов**: `---` или более минусов подряд
2. **Пустым строкам**: два или более подряд идущих переноса строки

### Функция построения промпта

**Метод**: `DeepSeekService::buildSeriesPrompt()`

**Сигнатура**:
```php
private function buildSeriesPrompt(
    array $dreams, 
    ?string $context, 
    array $traditions, 
    string $analysisType
): string
```

**Параметры**:
- `$dreams` (array) - Массив строк с описаниями снов
- `$context` (string|null) - Контекст пользователя
- `$traditions` (array) - Массив традиций
- `$analysisType` (string) - Тип анализа (для серии всегда `series_integrated`)

**Что делает**:
1. Формирует промпт для API с инструкциями по анализу серии снов
2. Создает JSON структуру для ответа
3. Добавляет все сны из массива `$dreams` в промпт с номерами

### Структура JSON ответа для серии снов

```json
{
  "series_analysis": {
    "series_title": "Общее название для серии снов",
    "traditions": ["gestalt", "jungian"],
    "analysis_type": "series_integrated",
    "overall_theme": "Основная тема, связывающая все сны",
    "emotional_arc": "Эмоциональная динамика от первого к последнему сну",
    "key_connections": ["связь1 с предыдущими темами", "связь2", "..."]
  },
  "dreams": [
    {
      "dream_number": 1,
      "dream_title": "Название первого сна",
      "dream_type": "архетипический/бытовой/осознанный/кошмар/пророческий/повторяющийся/исследовательский",
      "key_symbols": [
        {"symbol": "символ1", "meaning": "значение"},
        {"symbol": "символ2", "meaning": "значение"}
      ],
      "unified_locations": ["локация1", "локация2"],
      "key_tags": ["тег1", "тег2", "тег3"],
      "summary_insight": "Ключевая мысль этого сна",
      "emotional_tone": "Эмоциональный тон",
      "connection_to_previous": "Как связан с предыдущими снами пользователя"
    },
    {
      "dream_number": 2,
      "dream_title": "Название второго сна",
      "dream_type": "...",
      "key_symbols": [...],
      "unified_locations": [...],
      "key_tags": [...],
      "summary_insight": "...",
      "emotional_tone": "...",
      "connection_to_previous": "..."
    }
    // ... и так для каждого сна
  ],
  "recommendations": [
    "Рекомендация 1 на основе всей серии",
    "Рекомендация 2",
    "..."
  ]
}
```

### Пример использования

```php
// В контроллере DreamAnalyzerController::store()

// Проверяем, является ли описание серией снов
$dreamDescription = $validated['dream_description'];
$isSeries = $this->isDreamSeries($dreamDescription);
$dreams = [];

if ($isSeries) {
    // Разбиваем на отдельные сны
    $dreams = $this->splitDreams($dreamDescription);
    $analysisType = 'series_integrated';
}

// Выполняем анализ через API
$deepSeekService = new DeepSeekService();
$result = $deepSeekService->analyzeDream(
    $dreamDescription,              // Полное описание
    $validated['context'] ?? null,  // Контекст
    $validated['traditions'] ?? [], // Традиции
    $analysisType,                  // 'series_integrated' для серии
    $isSeries ? $dreams : null      // Массив снов или null
);
```

### Логика выбора функции

В методе `DeepSeekService::buildPrompt()`:

```php
private function buildPrompt(
    string $dreamDescription, 
    ?string $context, 
    array $traditions, 
    string $analysisType = 'single', 
    ?array $dreams = null
): string 
{
    // Если это серия снов, используем специальный шаблон
    if ($dreams !== null && count($dreams) > 1) {
        return $this->buildSeriesPrompt($dreams, $context, $traditions, $analysisType);
    }
    
    // Обычный шаблон для одного сна
    return $this->buildSinglePrompt($dreamDescription, $context, $traditions, $analysisType);
}
```

### Формат промпта для API

Промпт формируется в `buildSeriesPrompt()` и включает:

1. Вступление с традициями анализа
2. Контекст пользователя (если указан)
3. Инструкции по анализу серии снов:
   - Общий посыл всей серии
   - Анализ каждого сна отдельно
   - Общие практические рекомендации
4. JSON структуру для ответа
5. Список всех снов с номерами:
   ```
   СЕРИЯ СНОВ ДЛЯ АНАЛИЗА:
   - Сон 1: [описание первого сна]
   - Сон 2: [описание второго сна]
   - ...
   ```

### Отображение результатов

Результаты серии снов отображаются в:
`resources/views/dream-analyzer/partials/series-analysis.blade.php`

Ключевые блоки:
- `series_analysis` - общая информация о серии
- `dreams` - массив с детальным анализом каждого сна
- `recommendations` - общие рекомендации для всей серии
























