# Инструкция по деплою обновленных статей-инструкций

## Шаг 1: Экспорт статей из локальной БД (уже выполнено)

Статьи экспортированы в файл `articles_export.json`

## Шаг 2: Деплой кода на продакшн

Подключитесь к серверу и выполните стандартный деплой:

```bash
ssh adminfeg@adminfeg.beget.tech
cd ~/snovidec.ru/laravel
git pull origin main
php8.3 /home/a/adminfeg/.local/bin/composer install --no-dev --optimize-autoloader
php8.3 artisan migrate --force
php8.3 artisan view:clear
php8.3 artisan cache:clear
php8.3 artisan config:clear
php8.3 artisan route:clear
php8.3 artisan config:cache
php8.3 artisan route:cache
php8.3 artisan view:cache
php8.3 artisan optimize
```

## Шаг 3: Загрузка файла экспорта на сервер

Загрузите файл `articles_export.json` на сервер:

```bash
# С локального компьютера (из директории проекта)
scp articles_export.json adminfeg@adminfeg.beget.tech:~/snovidec.ru/laravel/
```

Или используйте FTP/SFTP клиент для загрузки файла в директорию `~/snovidec.ru/laravel/`

## Шаг 4: Импорт статей на продакшн

На сервере выполните:

```bash
ssh adminfeg@adminfeg.beget.tech
cd ~/snovidec.ru/laravel
php8.3 import_articles.php articles_export.json
```

Скрипт обновит существующие статьи или создаст новые, если их нет.

## Шаг 5: Проверка

1. Зайдите в админ-панель: http://snovidec.ru/admin/articles
2. Проверьте, что все 10 статей обновлены
3. Убедитесь, что контент корректный
4. При необходимости опубликуйте статьи (измените статус с "черновик" на "опубликовано")

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

### Шаг 4: Установка/обновление зависимостей Composer
```bash
php8.3 /home/a/adminfeg/.local/bin/composer install --no-dev --optimize-autoloader
```

### Шаг 5: Применение миграций (если есть новые)
```bash
php8.3 artisan migrate --force
```

### Шаг 6: Очистка кэша (КРИТИЧЕСКИ ВАЖНО!)
```bash
php8.3 artisan view:clear
php8.3 artisan cache:clear
php8.3 artisan config:clear
php8.3 artisan route:clear
```

### Шаг 7: Сборка фронтенда (если еще не собрано)
**Выполните на локальном компьютере:**
```bash
cd C:\Users\torle\snovidec
npm run build
```

Это создаст папку `public/build` с собранными файлами, включая `articles.css`.

### Шаг 8: Загрузка файла экспорта и build на сервер
**Выполните на локальном компьютере (из директории проекта):**

**Вариант 1: Загрузка через scp (рекомендуется)**
```bash
# Загрузить файл экспорта
scp articles_export.json adminfeg@adminfeg.beget.tech:~/snovidec.ru/laravel/

# Загрузить папку build (собранный фронтенд)
scp -r public/build adminfeg@adminfeg.beget.tech:~/snovidec.ru/laravel/public/
```

**Вариант 2: Создать архив и загрузить**
```bash
# Создать архив
tar -czf build.tar.gz public/build

# Загрузить архив
scp build.tar.gz adminfeg@adminfeg.beget.tech:~/snovidec.ru/laravel/

# На сервере распаковать (выполнить на сервере)
ssh adminfeg@adminfeg.beget.tech
cd ~/snovidec.ru/laravel
tar -xzf build.tar.gz
rm build.tar.gz
```

Или используйте FTP/SFTP клиент для загрузки файлов.

### Шаг 9: Импорт статей на продакшн
**На сервере выполните:**
```bash
php8.3 import_articles.php articles_export.json
```

### Шаг 10: Кэширование для продакшена
```bash
php8.3 artisan config:cache
php8.3 artisan route:cache
php8.3 artisan view:cache
php8.3 artisan optimize
```

### Шаг 11: Проверка
1. Зайдите в админ-панель: http://snovidec.ru/admin/articles
2. Проверьте, что все 10 статей обновлены
3. Убедитесь, что контент корректный
4. При необходимости опубликуйте статьи (измените статус с "черновик" на "опубликовано")

## Важно

- Файл `articles_export.json` содержит все данные статей, включая контент
- Скрипт импорта обновит существующие статьи по slug
- Если статьи не существуют, они будут созданы
- SEO метаданные также будут обновлены/созданы
- После импорта не забудьте очистить кэш (уже включено в команды выше)
