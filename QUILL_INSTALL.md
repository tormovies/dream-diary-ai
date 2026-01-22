# Установка Quill Editor

## Шаг 1: Скачайте Quill

1. Перейдите на официальный сайт: https://quilljs.com/download/
2. Или скачайте напрямую с GitHub: https://github.com/quilljs/quill/releases
3. Или используйте прямые ссылки на CDN (для ручной загрузки):
   - https://cdn.quilljs.com/1.3.7/quill.min.js
   - https://cdn.quilljs.com/1.3.7/quill.snow.css

## Шаг 2: Создайте папку

Создайте папку `public/js/quill/` в проекте (если её нет).

## Шаг 3: Скопируйте файлы

Поместите следующие файлы в `public/js/quill/`:
- `quill.min.js` - основной JavaScript файл
- `quill.snow.css` - стили для редактора

## Структура должна быть такой:

```
public/
  js/
    quill/
      quill.min.js
      quill.snow.css
```

## Альтернативный способ (если есть npm):

```bash
npm install quill
```

Затем скопируйте файлы из `node_modules/quill/dist/` в `public/js/quill/`:
- `quill.min.js` → `public/js/quill/quill.min.js`
- `quill.snow.css` → `public/js/quill/quill.snow.css`

## Проверка

После установки откройте страницу создания/редактирования статьи - редактор должен загрузиться автоматически.
