# Все упоминания ссылок на Telegram в проекте

## Основная ссылка
**https://t.me/snovidec_ru**

## Места использования:

### 1. Команды Artisan (создание/обновление статей-инструкций)
- **`app/Console/Commands/CreateGuideArticles.php`** (строка 105)
  - Переменная: `$telegramLink = 'https://t.me/snovidec_ru';`
  - Используется в методах:
    - `getCategory2Content()` - категория "Толкование снов"
    - `getCategory9Content()` - категория "Техническая поддержка"
  - Также хардкод в методах:
    - `getCategory10Content()` - строка 1280 (восстановление пароля)
    - `getCategory10Content()` - строка 1303 (удаление аккаунта)

- **`app/Console/Commands/UpdateGuideArticlesContent.php`** (строка 40)
  - Хардкод: `'https://t.me/snovidec_ru'`

- **`app/Console/Commands/UpdateGuideArticlesFormat.php`** (строка 49)
  - Переменная: `$telegramLink = 'https://t.me/snovidec_ru';`

### 2. Blade шаблоны (компоненты и страницы)

#### Компоненты меню:
- **`resources/views/components/auth-sidebar-menu.blade.php`** (строка 28)
  - Ссылка "Служба поддержки" в меню для авторизованных пользователей

- **`resources/views/components/guest-quick-actions.blade.php`** (строка 22)
  - Ссылка "Служба поддержки" в меню для неавторизованных пользователей

#### Страницы:
- **`resources/views/dream-analyzer/show.blade.php`**
  - Строка 195: Кнопка "Служба поддержки" при ошибке анализа
  - Строка 227: Текст с ссылкой на поддержку
  - Строка 979: JavaScript функция `shareToTelegram()` для шаринга

- **`resources/views/reports/analysis.blade.php`** (строка 573)
  - JavaScript функция `shareToTelegram()` для шаринга результатов анализа

- **`resources/views/dream-analyzer/partials/single-analysis.blade.php`** (строка 333)
  - Кнопка "Telegram" для шаринга

- **`resources/views/dream-analyzer/partials/single-analysis-normalized.blade.php`** (строка 194)
  - Кнопка "Telegram" для шаринга

- **`resources/views/dream-analyzer/partials/series-analysis.blade.php`** (строка 223)
  - Кнопка "Telegram" для шаринга

- **`resources/views/dream-analyzer/partials/series-analysis-normalized.blade.php`** (строка 208)
  - Кнопка "Telegram" для шаринга

### 3. Документация (markdown файлы)
- `GUIDE_CREATED_CONFIRMATION.md`
- `GUIDE_FIELDS_FINAL.md`
- `GUIDE_FIELDS_CONFIRMATION.md`
- `GUIDE_CREATION_FINAL_ANALYSIS.md`
- `GUIDE_STRUCTURE_10_CATEGORIES.md`
- `Предлагаемая структура FAQинструкци.txt`

## Типы использования:

1. **Прямые ссылки на поддержку** - `https://t.me/snovidec_ru`
   - В меню (auth-sidebar-menu, guest-quick-actions)
   - На страницах ошибок (dream-analyzer/show)
   - В статьях-инструкциях (CreateGuideArticles)

2. **JavaScript функции для шаринга** - `https://t.me/share/url?url=...&text=...`
   - В dream-analyzer/show.blade.php
   - В reports/analysis.blade.php

## Рекомендации:

Если нужно изменить ссылку на Telegram, нужно обновить:
1. Переменную `$telegramLink` в `CreateGuideArticles.php`
2. Хардкоды в `getCategory10Content()` (строки 1280, 1303)
3. Все упоминания в Blade шаблонах (компоненты и страницы)
4. JavaScript функции для шаринга (если нужно изменить канал)
