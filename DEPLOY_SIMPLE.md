# Простой деплой на продакшн (npm установлен на сервере)

## Команды деплоя (пошагово)

### Шаг 1: Подключение к серверу
```bash
ssh adminfeg@adminfeg.beget.tech
```

### Шаг 2: Переход в директорию проекта
```bash
cd ~/snovidec.ru/laravel
```

### Шаг 3: Получение последних изменений из репозитория
```bash
git pull origin main
```

### Шаг 4: Установка/обновление зависимостей npm и сборка фронтенда
```bash
npm install
npm run build
```

### Шаг 5: Установка/обновление зависимостей Composer
```bash
php8.3 /home/a/adminfeg/.local/bin/composer install --no-dev --optimize-autoloader
```

### Шаг 6: Применение миграций (если есть новые)
```bash
php8.3 artisan migrate --force
```

### Шаг 7: Очистка кэша
```bash
php8.3 artisan view:clear
php8.3 artisan cache:clear
php8.3 artisan config:clear
php8.3 artisan route:clear
```

### Шаг 8: Кэширование для продакшена
```bash
php8.3 artisan config:cache
php8.3 artisan route:cache
php8.3 artisan view:cache
php8.3 artisan optimize
```

## Быстрая команда (одной строкой)

```bash
cd ~/snovidec.ru/laravel && git pull origin main && npm install && npm run build && php8.3 /home/a/adminfeg/.local/bin/composer install --no-dev --optimize-autoloader && php8.3 artisan migrate --force && php8.3 artisan view:clear && php8.3 artisan cache:clear && php8.3 artisan config:clear && php8.3 artisan route:clear && php8.3 artisan config:cache && php8.3 artisan route:cache && php8.3 artisan view:cache && php8.3 artisan optimize
```

## Преимущества

✅ **Нет конфликтов git** - build пересобирается на сервере, файлы всегда актуальные  
✅ **Не нужно загружать build через scp** - все собирается автоматически  
✅ **Файлы одинаковые** - собраны из одного кода на сервере  
✅ **Простой процесс** - одна команда для всего деплоя  

## Важно

- **npm установлен на сервере** (Node.js v20.19.0, npm 10.2.4)
- Build пересобирается автоматически после каждого `git pull`
- Не нужно загружать `public/build` с локальной машины
