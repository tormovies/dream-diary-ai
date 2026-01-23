# Исправление стилей на продакшене

## Проблема
Стили из файла `resources/views/articles/show.blade.php` не применяются на продакшене.

## Причина
Laravel кэширует скомпилированные Blade шаблоны. Если не очистить кэш view, изменения в шаблонах не применятся.

## Решение

### Вариант 1: Полная очистка и обновление
```bash
ssh adminfeg@adminfeg.beget.tech
cd ~/snovidec.ru/laravel

# 1. Получить последние изменения
git pull origin main

# 2. Очистить ВСЕ кэши (особенно view:clear!)
php8.3 artisan view:clear
php8.3 artisan cache:clear
php8.3 artisan config:clear
php8.3 artisan route:clear

# 3. Обновить контент статей (если нужно)
php8.3 artisan guides:update-content

# 4. Пересоздать кэши
php8.3 artisan config:cache
php8.3 artisan route:cache
php8.3 artisan view:cache
php8.3 artisan optimize
```

### Вариант 2: Только очистка view кэша (быстро)
```bash
ssh adminfeg@adminfeg.beget.tech
cd ~/snovidec.ru/laravel
php8.3 artisan view:clear
```

## Проверка
После выполнения команд проверьте:
1. Откройте любую статью-инструкцию на продакшене
2. Проверьте в DevTools (F12), что inline стили из `<style>` блока присутствуют в HTML
3. Убедитесь, что стили применяются (отступы h2, цвета для светлой темы и т.д.)

## Важные замечания
- **view:clear** - критически важен для применения изменений в Blade шаблонах
- Стили находятся в inline `<style>` блоках внутри Blade шаблона, поэтому они не требуют сборки через npm/vite
- После очистки view кэша изменения должны примениться немедленно
- Если стили все еще не применяются, проверьте, что файл `resources/views/articles/show.blade.php` обновлен на сервере: `git log --oneline -1 -- resources/views/articles/show.blade.php`
