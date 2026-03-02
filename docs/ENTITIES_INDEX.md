# Индекс сущностей толкований (символы, локации, теги)

Таблица `dream_interpretation_entities` хранит выгруженные из JSON поля `key_symbols`, `unified_locations`, `key_tags` по каждому толкованию. Это нужно для:

- страниц «один символ / локация / тег» со списком толкований и статистикой;
- статистики по дням (например, топ символов за вчера) без разбора JSON из БД.

## Миграция

```bash
php artisan migrate
```

## Первичное заполнение (backfill)

Один раз после деплоя или для пересборки всего индекса:

```bash
php artisan interpretations:index-entities
```

Опции:

- `--chunk=300` — размер чанка (по умолчанию 300).
- `--dry-run` — только посчитать, сколько толкований будет обработано, без записи в БД.

Обрабатываются только толкования со статусом `completed` и с существующим `result`. Для каждого толкования старые записи в `dream_interpretation_entities` удаляются, затем вставляются новые (идемпотентность при повторном запуске).

## Инкрементальная индексация (только новые)

Для ежедневного запуска или ручного «дозаполнения» только тех толкований, у которых ещё нет ни одной записи в `dream_interpretation_entities`:

```bash
php artisan interpretations:index-entities --only-new
```

На сервере это уже запланировано раз в день (см. `bootstrap/app.php` → `withSchedule`). Для работы по расписанию на проде должен быть настроен cron:

```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

## Индексация за период

Только толкования, созданные начиная с даты:

```bash
php artisan interpretations:index-entities --since=2026-02-01
```

## Дневная агрегация (dream_entity_daily)

Для быстрой статистики «топ за дату» и сравнения двух дней данные по дням складываются в таблицу `dream_entity_daily`:

```bash
# Агрегировать за вчера (для cron)
php artisan interpretations:aggregate-entity-daily --yesterday

# За конкретную дату
php artisan interpretations:aggregate-entity-daily --date=2026-02-15

# Все даты из dream_interpretation_entities
php artisan interpretations:aggregate-entity-daily --all
```

В расписании (ежедневно в 01:00) запускается `interpretations:aggregate-entity-daily --yesterday`. **«Вчера»** считается по часовому поясу из настроек (Админка → Настройки → Часовой пояс); если не задан — используется `config('app.timezone')`. Подробности деплоя первого запуска и cron — в `DEPLOY_ENTITIES_STATS.md`.

## Примеры запросов

- Уникальные символы с числом упоминаний (для списка страниц):
  `DreamInterpretationEntity::uniqueWithCounts('symbol', 500)`
- Топ символов за вчера:
  `DreamInterpretationEntity::topForDate('symbol', null, 50)` (дата по умолчанию — вчера).
- Топ локаций за конкретную дату:
  `DreamInterpretationEntity::topForDate('location', '2026-02-15', 30)`.
- Толкования, в которых встречается символ с slug `voda`:
  выборка по `type = 'symbol' AND slug = 'voda'` с join к `dream_interpretations` (или через связь `interpretation` у модели `DreamInterpretationEntity`).

## Модель

`App\Models\DreamInterpretationEntity` — поля `type` (`symbol`|`location`|`tag`), `slug`, `name`, `meaning` (только для символов), `interpretation_created_at` (для статистики по дням). Связь `interpretation()` с `DreamInterpretation`.
