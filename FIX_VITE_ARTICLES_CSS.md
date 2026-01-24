# Исправление ошибки Vite: Unable to locate file in Vite manifest: resources/css/articles.css

## Проблема
На продакшене возникает ошибка:
```
Unable to locate file in Vite manifest: resources/css/articles.css
```

## Причина
Файл `resources/css/articles.css` используется в шаблонах через `@vite()`, но не был добавлен в `vite.config.js`, поэтому не попадает в сборку.

## Решение

### Вариант 1: Сборка фронтенда на продакшене (рекомендуется)

Если на сервере есть Node.js и npm:

```bash
ssh adminfeg@adminfeg.beget.tech
cd ~/snovidec.ru/laravel

# Установка зависимостей (если еще не установлены)
npm install

# Сборка для продакшена
npm run build
```

### Вариант 2: Сборка локально и загрузка на сервер

1. **Локально (на вашем компьютере):**
```bash
cd C:\Users\torle\snovidec
npm run build
```

2. **Загрузите папку `public/build` на сервер:**
```bash
# Создайте архив
cd C:\Users\torle\snovidec
tar -czf build.tar.gz public/build

# Загрузите на сервер
scp build.tar.gz adminfeg@adminfeg.beget.tech:~/snovidec.ru/laravel/

# На сервере распакуйте
ssh adminfeg@adminfeg.beget.tech
cd ~/snovidec.ru/laravel
tar -xzf build.tar.gz
rm build.tar.gz
```

### Вариант 3: Временное решение - подключение CSS напрямую

Если сборка невозможна, можно временно подключить CSS напрямую, изменив шаблоны:

**В файлах:**
- `resources/views/articles/show.blade.php`
- `resources/views/articles/guide/index.blade.php`

**Замените:**
```blade
@vite(['resources/css/app.css', 'resources/css/articles.css', 'resources/js/app.js'])
```

**На:**
```blade
@vite(['resources/css/app.css', 'resources/js/app.js'])
<link rel="stylesheet" href="{{ asset('css/articles.css') }}">
```

И скопируйте файл `resources/css/articles.css` в `public/css/articles.css` на сервере.

## После исправления

Очистите кэш на продакшене:
```bash
ssh adminfeg@adminfeg.beget.tech
cd ~/snovidec.ru/laravel
php8.3 artisan view:clear
php8.3 artisan cache:clear
php8.3 artisan config:clear
php8.3 artisan route:clear
```

## Проверка

Откройте страницу статьи на продакшене:
- http://snovidec.ru/guide/nachalo-raboty

Ошибка должна исчезнуть.
