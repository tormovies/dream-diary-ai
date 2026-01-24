# Исправление конфликта при git pull на продакшене

## Проблема
При выполнении `git pull` возникает ошибка:
```
error: Ваши локальные изменения в указанных файлах будут перезаписаны при слиянии:  
        public/build/manifest.json
error: Указанные неотслеживаемые файлы в рабочем каталоге будут перезаписаны при слиянии:
        public/build/assets/articles-QJrw4zta.css
```

## Решение

### Вариант 1: Удалить локальные изменения и сделать pull (рекомендуется)

**На сервере выполните:**
```bash
cd ~/snovidec.ru/laravel

# Удалить локальные изменения в build
git checkout -- public/build/manifest.json
rm -f public/build/assets/articles-QJrw4zta.css

# Теперь можно сделать pull
git pull origin main
```

### Вариант 2: Принудительно обновить файлы из репозитория

**На сервере выполните:**
```bash
cd ~/snovidec.ru/laravel

# Принудительно обновить файлы из репозитория
git fetch origin
git reset --hard origin/main
```

### Вариант 3: Сохранить изменения, затем pull

**На сервере выполните:**
```bash
cd ~/snovidec.ru/laravel

# Сохранить изменения в stash
git stash

# Сделать pull
git pull origin main

# Если нужно вернуть изменения (обычно не нужно для build файлов)
# git stash pop
```

## После исправления

Продолжите деплой:
```bash
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

## Примечание

Файлы в `public/build` должны быть одинаковыми на сервере и в репозитории. Если после pull файлы отличаются, загрузите их заново с локального компьютера:

```bash
# С локального компьютера
scp -r C:\Users\torle\snovidec\public\build adminfeg@adminfeg.beget.tech:~/snovidec.ru/laravel/public/
```
