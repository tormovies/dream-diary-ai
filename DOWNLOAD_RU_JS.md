# Скачать русский языковой файл для TinyMCE

## Способ 1: Прямая ссылка (рекомендуется)

1. Откройте в браузере: https://cdn.tiny.cloud/1/no-api-key/tinymce/6/langs/ru.js
2. Сохраните страницу как `ru.js` (Ctrl+S)
3. Скопируйте файл в: `C:\Users\torle\snovidec\public\js\tinymce\langs\ru.js`

## Способ 2: Через командную строку (если есть curl или wget)

```bash
cd C:\Users\torle\snovidec\public\js\tinymce\langs
curl -o ru.js https://cdn.tiny.cloud/1/no-api-key/tinymce/6/langs/ru.js
```

## Проверка

После скачивания файл должен быть по пути:
```
C:\Users\torle\snovidec\public\js\tinymce\langs\ru.js
```

После этого TinyMCE будет на русском языке.