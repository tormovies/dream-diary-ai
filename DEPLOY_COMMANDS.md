# Команды для деплоя на продакшен

## Подключение к серверу

```bash
ssh adminfeg@adminfeg.beget.tech
# Пароль: fRAxngtck8um
```

## Стандартный процесс деплоя

```bash
# 1. Перейти в директорию проекта
cd ~/snovidec.ru/laravel

# 2. Получить последние изменения из GitHub
git pull origin main

# 3. Установить/обновить зависимости Composer
# ВАЖНО: Composer должен использовать php8.3, а не старую версию PHP
# Вариант 1: Указать php8.3 явно
php8.3 /home/a/adminfeg/.local/bin/composer install --no-dev --optimize-autoloader
# Вариант 2: Если composer в PATH
php8.3 $(which composer) install --no-dev --optimize-autoloader

# 4. Очистить все кэши
php8.3 artisan cache:clear
php8.3 artisan config:clear
php8.3 artisan route:clear
php8.3 artisan view:clear

# 5. Пересоздать кэши (для оптимизации)
php8.3 artisan config:cache
php8.3 artisan route:cache
php8.3 artisan view:cache

# 6. Оптимизировать приложение
php8.3 artisan optimize
```

## Быстрая команда (одной строкой)

```bash
cd ~/snovidec.ru/laravel && git pull origin main && php8.3 /home/a/adminfeg/.local/bin/composer install --no-dev --optimize-autoloader && php8.3 artisan cache:clear && php8.3 artisan config:clear && php8.3 artisan route:clear && php8.3 artisan view:clear && php8.3 artisan config:cache && php8.3 artisan route:cache && php8.3 artisan view:cache && php8.3 artisan optimize
```

## Дополнительные команды (если нужно)

### Проверка версии после деплоя
```bash
cd ~/snovidec.ru/laravel
git log --oneline -1
```

### Проверка статуса Git
```bash
cd ~/snovidec.ru/laravel
git status
```

### Откат изменений (если что-то пошло не так)
```bash
cd ~/snovidec.ru/laravel
git log --oneline -5  # посмотреть последние коммиты
git reset --hard <commit-hash>  # откатиться к нужному коммиту
# Затем повторить команды деплоя
```

## Важные замечания

- **PHP версия:** Используется `php8.3` (не просто `php`)
- **Composer:** Использовать полный путь `/home/a/adminfeg/.local/bin/composer` (или просто `composer` если он в PATH)
- **npm:** Не требуется на продакшене (фронтенд собирается локально)
- **Миграции:** Если есть новые миграции, выполнить: `php8.3 artisan migrate --force`

## Информация о сервере

- **Хост:** adminfeg@adminfeg.beget.tech
- **Пароль:** fRAxngtck8um
- **Путь к проекту:** ~/snovidec.ru/laravel
- **PHP версия:** 8.3
- **Последний успешный деплой:** 2026-01-12 (коммит 9127817 - добавлена возможность удаления толкований в админке)
