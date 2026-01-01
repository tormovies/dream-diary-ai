# Прогресс переработки системы анализа сновидений

**Дата:** 2026-01-01  
**Статус:** В процессе - формирование запросов к API

---

## 🎯 Цель проекта

Полностью переделать анализ отчетов и толкование сновидений:
1. Унифицировать работу Reports и Dreams (обе через страницу ожидания результата)
2. Унифицировать традиции и JSON-форматы запросов/ответов
3. Создать новые таблицы БД для хранения результатов
4. Поддержка 3 типов мульти-традиционного анализа:
   - **synthetic_comparative** — сравнение традиций
   - **parallel_insights** — параллельные инсайты
   - **integrated** — интегрированный подход

---

## ✅ Что сделано

### 1. Объединены конфиги традиций

**Файл:** `config/traditions.php`

- Объединены `traditions.php` и `traditions_clarifications.php` в один файл
- Добавлены новые поля для каждой традиции:
  - `available_aspects` — допустимые аспекты для анализа
  - `default_analysis_parameters` — параметры анализа по умолчанию
  - `difficulty_level` — уровень сложности (beginner/intermediate/advanced)
  - `emoji_icon` — emoji иконка
- Переименована традиция: `eclectic` → `complex_analysis`
- Синхронизированы названия для `dream_hackers` и `lucid_centered`

**Традиции:**
- freudian, jungian, cognitive, symbolic, shamanic, gestalt
- lucid_centered (отключена)
- dream_hackers
- complex_analysis

---

### 2. Создан шаблон запроса к API

**Файл:** `config/api_request_template.php`

Содержит полную структуру запроса к DeepSeek API:

#### Основные блоки:
1. **system_prompt** — промпт для AI
2. **request_metadata** — метаданные запроса (версия, тип, ID, платформа)
3. **analysis_config** — конфигурация анализа (ДИНАМИЧЕСКАЯ)
4. **user_profile** — профиль пользователя (опционально)
5. **dream_metadata** — запрашиваемые поля для ответа
6. **context_summary** — контекст пользователя
7. **dream_data** — данные сна (текст, сенсорика, нарратив)
8. **unified_schema_request** — запрос унифицированной схемы ответа ✨
9. **analysis_request** — конкретные вопросы (опционально)
10. **integration_preferences** — предпочтения интеграции (опционально)
11. **feedback_loop** — обратная связь (опционально)

#### Ключевой блок: `unified_schema_request`

```php
'unified_schema_request' => [
    'required_sections' => [
        'dream_metadata',
        'core_analysis',
        'symbolic_elements',
        'practical_guidance',
        'recommendations',
    ],
    'optional_sections' => [
        'tradition_specific',
        'lucidity_analysis',
    ],
    
    'sections_configuration' => [
        'dream_metadata' => [
            'required_fields' => [...],
            'optional_fields' => [...],
            'field_guidelines' => [...],
            'dream_detailed_length' => 'long',
        ],
    ],
    
    'symbolic_elements_config' => [...],
    'core_analysis_config' => [...],
    'practical_guidance_config' => [...],
    
    'response_structure' => 'v1.1',
    'language' => 'ru',
    'detail_level' => 'detailed',
]
```

---

### 3. Создан файл с примерами конфигураций

**Файл:** `config/analysis_config_examples.php`

Примеры `analysis_config` для разных типов анализа:
- **single** — одна традиция
- **synthetic_comparative** — сравнение традиций
- **parallel_insights** — параллельные инсайты
- **integrated** — интегрированный подход
- **series_integrated** — серия снов
- **series_integrated_multi** — серия снов + несколько традиций

---

## 📝 Пример запроса к API (Single Tradition)

```json
{
  "model": "deepseek-chat",
  "messages": [
    {
      "role": "system",
      "content": "Ты — универсальный аналитик сновидений. Проанализируй сон с учётом указанного контекста, используя мульти-традиционный подход. Весь ответ на русском языке в унифицированном JSON формате."
    },
    {
      "role": "user",
      "content": {
        "request_metadata": {
          "analysis_version": "2.0",
          "request_type": "dream_analysis",
          "request_id": "550e8400-e29b-41d4-a716-446655440000",
          "client_platform": "web",
          "analysis_depth": "глубокий"
        },
        
        "analysis_config": {
          "mode": "single_tradition",
          
          "tradition": {
            "name": "dream_hackers",
            "display_name": "Хакеры сновидений",
            "tradition_specific_clarification": {...},
            "analysis_parameters": {...},
            "requested_aspects": [...]
          },
          
          "output_format": "unified_json_v2_single_tradition",
          "response_language": "ru"
        },
        
        "user_profile": {...},
        "dream_metadata": {...},
        "context_summary": "...",
        "dream_data": {
          "raw_text": "Описание сна",
          "recall_clarity": 0.9,
          "sensory_details": {...},
          "narrative_annotations": {...}
        },
        
        "unified_schema_request": {
          "required_sections": ["dream_metadata", "core_analysis", "symbolic_elements", "practical_guidance", "recommendations"],
          "optional_sections": ["tradition_specific", "lucidity_analysis"],
          "sections_configuration": {...},
          "symbolic_elements_config": {...},
          "core_analysis_config": {...},
          "practical_guidance_config": {...}
        },
        
        "analysis_request": {...}
      }
    }
  ],
  "temperature": 0.7,
  "max_tokens": 8000,
  "stream": false
}
```

---

## 🔜 Следующие шаги

### 1. Получить примеры JSON-ответов от API

Для каждого типа анализа нужны примеры ответов:
- **Single** (одна традиция)
- **Synthetic Comparative** (сравнение)
- **Parallel Insights** (параллельные инсайты)
- **Integrated** (интегрированный)
- **Series** (серия снов)

### 2. Спроектировать структуру БД

На основе JSON-ответов создать:
- Миграции для новых/обновлённых таблиц
- Модели Laravel
- Адаптеры для парсинга ответов разных версий

### 3. Создать сервисы для формирования запросов

**Новый сервис:** `DreamAnalysisRequestBuilder`
- Метод `buildSingleTraditionRequest()`
- Метод `buildMultiTraditionRequest()`
- Метод `buildSeriesRequest()`

### 4. Обновить контроллеры

- `DreamAnalyzerController` — унифицировать создание запросов
- `ReportController` — добавить анализ через новую страницу ожидания

### 5. Создать Blade-шаблоны для отображения результатов

- Страница ожидания результата (общая для Reports и Dreams)
- Отображение результатов для каждого типа анализа
- Текстовая страница для отладки (с полным JSON для админа)

---

## 📂 Структура файлов конфигурации

```
config/
├── traditions.php                    ✅ Объединённый конфиг традиций
├── api_request_template.php          ✅ Шаблон запроса к API
└── analysis_config_examples.php      ✅ Примеры конфигураций
```

---

## 🗄️ Текущая структура БД (требует обновления)

**Существующие таблицы:**
- `dream_interpretations` — запросы к AI (входящие данные, сырой ответ)
- `dream_interpretation_results` — нормализованные данные (single/series)
- `dream_interpretation_series_dreams` — отдельные сны в серии

**Потребуются изменения:**
- Добавить поля для мульти-традиционного анализа
- Новая таблица для сравнительного анализа (comparative)
- Новая таблица для параллельных инсайтов (parallel)
- Поля для хранения JSON с результатами по каждой традиции

---

## 💡 Ключевые решения

1. **Один конфиг для традиций** — всё в `config/traditions.php`
2. **Унифицированный запрос** — шаблон в `config/api_request_template.php`
3. **Три типа мульти-традиционного анализа:**
   - `synthetic_comparative` — каждая традиция отдельно + сравнение
   - `parallel_insights` — каждая традиция независимо, без сравнения
   - `integrated` — все традиции вместе, единый анализ
4. **Детальная конфигурация ответа** — через `unified_schema_request`
5. **Разные страницы для Reports и Dreams**, но единый принцип работы

---

## 🚀 Текущий этап

**Тестирование запросов к API** ✅

**Дата теста:** 2026-01-01  
**Результат:** Успешно! ✅

### Результаты теста:

1. ✅ **Запрос успешно отправлен** — HTTP 200
2. ✅ **JSON успешно распарсен** — структура валидна
3. ✅ **Все запрошенные секции присутствуют:**
   - dream_metadata
   - core_analysis
   - symbolic_elements
   - tradition_specific
   - practical_guidance
   - recommendations
   - tags_and_categories
   - answers_to_specific_questions
   - concluding_insights

4. ✅ **Токены использованы:** 4989 (1362 prompt + 3627 completion)

### Структура ответа от API:

API возвращает данные в блоке `analysis_report`:
```json
{
  "analysis_report": {
    "dream_metadata": {...},
    "core_analysis": {...},
    "symbolic_elements": {...},
    ...
  }
}
```

**Решение:** Принимаем как есть. Будем извлекать `analysis_report` при парсинге.

### Файлы теста:

- `test_api_request.php` — тестовый скрипт
- `storage/test_request.json` — сохранённый запрос
- `storage/test_response.json` — полный ответ от API
- `storage/test_parsed.json` — распарсенный JSON

---

## 🔜 Следующие шаги:

1. **Создать миграцию** для обновления `dream_interpretation_results`
2. **Создать сервис** для формирования запросов к API по новому шаблону
3. **Создать адаптер** для парсинга ответов и сохранения в БД
4. **Обновить контроллеры** для использования новой системы

---

_Документ создан автоматически для сохранения прогресса работы._

