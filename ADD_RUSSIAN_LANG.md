# Добавление русской локализации для TinyMCE

## Шаг 1: Скачать русский языковой файл

1. Перейдите на https://www.tiny.cloud/docs/tinymce/6/ui-localization/
2. Или скачайте напрямую: https://cdn.tiny.cloud/1/no-api-key/tinymce/6/langs/ru.js

## Шаг 2: Сохранить файл

Скопируйте файл `ru.js` в папку:
```
C:\Users\torle\snovidec\public\js\tinymce\langs\ru.js
```

## Шаг 3: Включить русский язык

После добавления файла раскомментируйте строки в файлах:
- `resources/views/admin/articles/create.blade.php`
- `resources/views/admin/articles/edit.blade.php`

Найдите:
```javascript
// language: 'ru', // Раскомментировать после добавления ru.js
```

Измените на:
```javascript
language: 'ru',
```

Готово! Интерфейс TinyMCE будет на русском языке.