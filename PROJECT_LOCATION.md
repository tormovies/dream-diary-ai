# Расположение проекта "Дневник сновидений"

## Основной проект Laravel
**Путь:** `C:\Users\torle\snovidec\`

Это основная директория проекта. Здесь находится весь код приложения.

### Структура:
- `app/` - контроллеры, модели, политики, команды, хелперы
- `bootstrap/` - файлы загрузки Laravel
- `config/` - конфигурационные файлы
- `database/` - миграции, сидеры, фабрики
- `public/` - публичные файлы (точка входа)
- `resources/` - views, JS, CSS
- `routes/` - файлы маршрутов
- `storage/` - логи, кэш, загруженные файлы
- `tests/` - тесты

## Документация (копия)
**Путь:** `d:\diaries 2\`

Здесь находятся файлы документации:
- `README.md` - описание проекта
- `SETUP.md` - инструкция по установке
- `TECHNICAL_SPECIFICATION.md` - техническое задание
- `PROJECT_STRUCTURE.md` - детальная структура проекта
- `database/migrations/` - копия миграций (для справки)

## Важно!

**Все команды Laravel выполняются из:** `C:\Users\torle\snovidec\`

```bash
cd C:\Users\torle\snovidec
php -S 127.0.0.1:3000 -t public public/server.php
composer install
npm install
```

**ВАЖНО:** Для запуска сервера используется команда `php -S 127.0.0.1:3000 -t public public/server.php` (не `php artisan serve`).
Роутер `server.php` правильно обрабатывает статические файлы (CSS, JS) и маршруты Laravel.
Сервер работает на порту 3000: http://127.0.0.1:3000/

## Быстрый доступ

- **Код проекта:** `C:\Users\torle\snovidec\`
- **Документация:** `d:\diaries 2\`






