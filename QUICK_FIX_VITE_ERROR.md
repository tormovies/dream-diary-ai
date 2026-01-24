# Быстрое исправление ошибки Vite на продакшене

## Проблема
```
Unable to locate file in Vite manifest: resources/css/articles.css
```

## Быстрое решение (3 шага)

### Шаг 1: Загрузить собранный фронтенд на сервер

**На локальном компьютере (из директории `C:\Users\torle\snovidec`):**
```bash
scp -r public/build adminfeg@adminfeg.beget.tech:~/snovidec.ru/laravel/public/
```

### Шаг 2: Обновить код на сервере

**На сервере:**
```bash
ssh adminfeg@adminfeg.beget.tech
cd ~/snovidec.ru/laravel
git pull origin main
php8.3 /home/a/adminfeg/.local/bin/composer install --no-dev --optimize-autoloader
```

### Шаг 3: Очистить кэш

**На сервере:**
```bash
php8.3 artisan view:clear
php8.3 artisan cache:clear
php8.3 artisan config:clear
php8.3 artisan route:clear
php8.3 artisan config:cache
php8.3 artisan route:cache
php8.3 artisan view:cache
php8.3 artisan optimize
```

## Готово!

Откройте страницу: http://snovidec.ru/guide/nachalo-raboty

Ошибка должна исчезнуть.
