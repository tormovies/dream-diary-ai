# Команды для деплоя статей-инструкций на продакшен

## Информация о сервере
- **Хост:** adminfeg@adminfeg.beget.tech
- **Путь к проекту:** ~/snovidec.ru/laravel
- **PHP версия:** php8.3 (не просто php!)
- **Composer:** /home/a/adminfeg/.local/bin/composer

## 1. Подключение к серверу и переход в директорию проекта
```bash
ssh adminfeg@adminfeg.beget.tech
cd ~/snovidec.ru/laravel
```

## 2. Получение последних изменений из репозитория
```bash
git pull origin main
```

## 3. Установка/обновление зависимостей
```bash
php8.3 /home/a/adminfeg/.local/bin/composer install --no-dev --optimize-autoloader
```

## 4. Применение миграций (если есть новые)
```bash
php8.3 artisan migrate --force
```

## 5. Очистка кэша (КРИТИЧЕСКИ ВАЖНО для применения изменений в Blade шаблонах!)
```bash
# Очистка view кэша - ОБЯЗАТЕЛЬНО, иначе изменения в Blade шаблонах не применятся!
php8.3 artisan view:clear
php8.3 artisan cache:clear
php8.3 artisan config:clear
php8.3 artisan route:clear
# Важно: view:clear должен быть первым, так как он очищает кэш скомпилированных Blade шаблонов
# где находятся inline стили из articles/show.blade.php
```

## 6. Создание статей-инструкций (если еще не созданы)
```bash
php8.3 artisan guides:create-all
```

## 7. Обновление контента существующих статей (с новыми стилями и отступами)
```bash
php8.3 artisan guides:update-content
```

## 8. Кэширование для продакшена
```bash
php8.3 artisan config:cache
php8.3 artisan route:cache
php8.3 artisan view:cache
php8.3 artisan optimize
```

## Быстрая команда (одной строкой)
```bash
cd ~/snovidec.ru/laravel && git pull origin main && php8.3 /home/a/adminfeg/.local/bin/composer install --no-dev --optimize-autoloader && php8.3 artisan migrate --force && php8.3 artisan view:clear && php8.3 artisan cache:clear && php8.3 artisan config:clear && php8.3 artisan route:clear && php8.3 artisan guides:create-all && php8.3 artisan guides:update-content && php8.3 artisan config:cache && php8.3 artisan route:cache && php8.3 artisan view:cache && php8.3 artisan optimize
```

**ВАЖНО:** `view:clear` должен быть выполнен ПЕРЕД другими командами очистки кэша, чтобы изменения в Blade шаблонах (включая inline стили) применились!

## Примечания:
- **ВАЖНО:** Все команды используют `php8.3`, а не просто `php`
- Команда `guides:create-all` создаст все 10 категорий инструкций со статусом "черновик"
- Команда `guides:update-content` обновит контент всех существующих статей-инструкций с новыми стилями и отступами
- Если статьи уже созданы, можно пропустить шаг 6 и выполнить только шаг 7
- После обновления контента не забудьте опубликовать статьи в админ-панели, если они должны быть видны пользователям
