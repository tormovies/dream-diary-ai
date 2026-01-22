# Установка TinyMCE локально

## Шаг 1: Скачать TinyMCE

1. Перейдите на https://www.tiny.cloud/get-tiny/self-hosted/
2. Нажмите "Download" → выберите "Self-hosted"
3. Или скачайте напрямую: https://download.tiny.cloud/tinymce/community/tinymce_6.8.3.zip

## Шаг 2: Распаковать в проект

1. Распакуйте скачанный архив
2. Найдите папку `tinymce` внутри архива
3. Скопируйте ВСЁ содержимое папки `tinymce` в:
   ```
   C:\Users\torle\snovidec\public\js\tinymce\
   ```

## Шаг 3: Проверить структуру

После копирования структура должна быть такой:
```
public/
  js/
    tinymce/
      tinymce.min.js  ← главный файл
      skins/
      themes/
      models/
      icons/
      ... (другие папки и файлы)
```

## Шаг 4: Проверить работу

После установки откройте страницу создания статьи в админке:
- http://127.0.0.1:3000/admin/articles/create

Редактор должен загрузиться без ошибок в консоли браузера.

## Альтернатива (если не хотите устанавливать TinyMCE)

Можно использовать простой textarea или другой локальный WYSIWYG редактор.