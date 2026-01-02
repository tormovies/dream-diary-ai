# 📚 Руководство по управлению традициями анализа снов

## 📍 Расположение конфигурации

Все традиции теперь централизованно управляются из **одного файла**:

```
config/traditions.php
```

---

## 🎯 Структура конфигурации

Каждая традиция имеет следующие поля:

```php
'freudian' => [
    'enabled' => true,                              // Включена/выключена
    'key' => 'freudian',                           // Ключ традиции (для БД и API)
    'name_short' => 'Фрейдистская',                // Короткое название (для бейджей, мобильной версии)
    'name_full' => 'Фрейдистский анализ',          // Полное название (для форм)
    'deepseek_description' => 'фрейдистской',      // Описание для DeepSeek API (в промпте)
    'icon' => 'fa-couch',                          // FontAwesome иконка (для будущего использования)
],
```

---

## ✏️ Как добавить новую традицию

1. Открой `config/traditions.php`
2. Добавь новый блок в конец массива:

```php
'my_new_tradition' => [
    'enabled' => true,
    'key' => 'my_new_tradition',
    'name_short' => 'Новая',
    'name_full' => 'Новая традиция',
    'deepseek_description' => 'новой традиции',
    'icon' => 'fa-star',
],
```

3. Очисти кеш конфигурации:
```bash
php artisan config:clear
```

**Всё!** Новая традиция автоматически появится:
- ✅ В форме выбора традиций
- ✅ В валидации контроллеров
- ✅ В промптах для DeepSeek API
- ✅ В отображении результатов

---

## 🔧 Как временно отключить традицию

1. Открой `config/traditions.php`
2. Найди нужную традицию
3. Измени `'enabled' => true` на `'enabled' => false`
4. Очисти кеш:
```bash
php artisan config:clear
```

Традиция **исчезнет** из форм, но **останется работать** для старых анализов.

---

## 📝 Как изменить название традиции

### Только для пользователей (UI):
1. Измени `name_short` или `name_full` в `config/traditions.php`
2. Очисти кеш

### Для DeepSeek API (промпт):
1. Измени `deepseek_description` в `config/traditions.php`
2. Очисти кеш

**Важно:** Не меняй поле `key` — это нарушит совместимость со старыми записями в БД!

---

## 🔍 Где используются традиции

### 1. **Конфиг** (`config/traditions.php`)
- Единственный источник правды

### 2. **Helper** (`app/Helpers/TraditionHelper.php`)
- Методы для работы с традициями
- Используется в контроллерах и сервисах

### 3. **Контроллеры**
- `DreamAnalyzerController` — валидация форм
- `ReportController` — анализ отчетов

### 4. **Сервисы**
- `DeepSeekService` — промпты для API

### 5. **Blade-шаблоны**
- `dream-analyzer/create.blade.php` — форма выбора
- `reports/show.blade.php` — модальное окно анализа
- `dream-analyzer/partials/*.blade.php` — отображение результатов

---

## 🛠 Helper-функции (TraditionHelper)

```php
use App\Helpers\TraditionHelper;

// Получить все традиции
TraditionHelper::all();

// Получить только активные
TraditionHelper::enabled();

// Получить строку для валидации
TraditionHelper::validationKeys(); // "freudian,jungian,cognitive..."

// Получить традицию по ключу
TraditionHelper::get('freudian');

// Получить короткое название
TraditionHelper::shortName('freudian'); // "Фрейдистская"

// Получить полное название
TraditionHelper::fullName('freudian'); // "Фрейдистский анализ"

// Получить описание для DeepSeek
TraditionHelper::deepSeekDescription('freudian'); // "фрейдистской"

// Получить иконку
TraditionHelper::icon('freudian'); // "fa-couch"

// Проверить, активна ли традиция
TraditionHelper::isEnabled('freudian'); // true/false

// Получить все описания для DeepSeek (для промпта)
TraditionHelper::deepSeekDescriptions(); // ['freudian' => 'фрейдистской', ...]
```

---

## 🚀 Порядок традиций

Порядок в конфиге = порядок в формах и списках.

Чтобы изменить порядок, просто переставь блоки в `config/traditions.php`.

---

## ⚠️ Важные замечания

1. **НЕ меняй поле `key`** после создания традиции — это сломает совместимость с БД.
2. **Всегда очищай кеш** после изменений: `php artisan config:clear`
3. **На продакшене** обязательно запусти `php artisan config:cache` после изменений.
4. **deepseek_description** используется в родительном падеже: "опытный аналитик [описание] традиции".

---

## 📦 Деплой изменений

После изменения `config/traditions.php`:

### Локально:
```bash
php artisan config:clear
```

### На продакшене:
```bash
ssh user@server
cd /path/to/project
git pull
php artisan config:clear
php artisan config:cache
php artisan view:clear
```

---

## 🎨 Иконки (FontAwesome 6.4)

Текущие иконки:
- `freudian` → `fa-couch` (диван)
- `jungian` → `fa-yin-yang` (инь-янь)
- `cognitive` → `fa-brain` (мозг)
- `symbolic` → `fa-key` (ключ)
- `shamanic` → `fa-feather` (перо)
- `gestalt` → `fa-puzzle-piece` (пазл)
- `lucid_centered` → `fa-eye` (глаз)
- `eclectic` → `fa-layer-group` (слои)

Все иконки из набора **Font Awesome 6.4** (free solid icons).

---

## 📞 Поддержка

Если что-то сломалось:
1. Проверь синтаксис PHP в `config/traditions.php`
2. Запусти `php artisan config:clear`
3. Проверь логи Laravel: `storage/logs/laravel.log`












