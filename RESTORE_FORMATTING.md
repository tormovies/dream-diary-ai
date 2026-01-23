# Восстановление форматирования статей на продакшене

## Проблема
При редактировании статей в Quill редакторе форматирование терялось, так как Quill не сохранял Tailwind классы и сложную структуру HTML.

## Решение
1. Исправлена загрузка HTML в Quill - теперь используется `innerHTML` напрямую
2. Обновлен контент всех статей локально командой `guides:update-content`

## Команды для восстановления на продакшене:

```bash
# 1. Подключитесь к серверу
ssh adminfeg@adminfeg.beget.tech
cd ~/snovidec.ru/laravel

# 2. Получите последние изменения
git pull origin main

# 3. Установите зависимости
php8.3 /home/a/adminfeg/.local/bin/composer install --no-dev --optimize-autoloader

# 4. Очистите кэш (КРИТИЧЕСКИ ВАЖНО!)
php8.3 artisan view:clear
php8.3 artisan cache:clear
php8.3 artisan config:clear
php8.3 artisan route:clear

# 5. Восстановите форматирование всех статей-инструкций
php8.3 artisan guides:update-content

# 6. Пересоздайте кэши
php8.3 artisan config:cache
php8.3 artisan route:cache
php8.3 artisan view:cache
php8.3 artisan optimize
```

## Важно:
- Команда `guides:update-content` восстановит форматирование всех 10 статей-инструкций
- После этого форматирование будет сохранено и при редактировании в Quill
- Теперь Quill использует `innerHTML` для загрузки, что сохраняет все Tailwind классы
