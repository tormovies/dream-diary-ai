# Команды для деплоя на продакшен

## 1️⃣ Подключение к серверу
```bash
ssh user@your-server.com
# или
ssh user@IP_ADDRESS -p PORT
```

## 2️⃣ Переход в директорию проекта
```bash
cd ~/snovidec.ru/laravel
```

## 3️⃣ Обновление кода из GitHub
```bash
git pull origin main
```

## 4️⃣ Установка/обновление зависимостей PHP
```bash
composer install --no-dev --optimize-autoloader
```

## 5️⃣ Установка/обновление зависимостей Node.js и сборка фронтенда
```bash
npm install
npm run build
```

## 6️⃣ Выполнение миграций БД
```bash
php artisan migrate --force
```

## 7️⃣ Очистка и оптимизация кеша
```bash
# Очистка всех кешей
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Кеширование для продакшена (ускорение)
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 8️⃣ Права доступа (если нужно)
```bash
# Владелец файлов - веб-сервер (nginx/apache)
sudo chown -R www-data:www-data storage bootstrap/cache

# Права на запись
sudo chmod -R 775 storage bootstrap/cache
```

## 9️⃣ Перезапуск очередей (если используются)
```bash
php artisan queue:restart
```

## 🔟 Перезапуск PHP-FPM (опционально, если нужно)
```bash
sudo systemctl reload php8.2-fpm
# или
sudo systemctl restart php8.2-fpm
```

---

## 🚀 Быстрый деплой (все команды одной строкой)
```bash
cd ~/snovidec.ru/laravel && \
git pull origin main && \
composer install --no-dev --optimize-autoloader && \
npm install && npm run build && \
php artisan migrate --force && \
php artisan config:clear && \
php artisan route:clear && \
php artisan view:clear && \
php artisan cache:clear && \
php artisan config:cache && \
php artisan route:cache && \
php artisan view:cache && \
sudo chown -R www-data:www-data storage bootstrap/cache && \
sudo chmod -R 775 storage bootstrap/cache
```

---

## ⚠️ Важные моменты

### Перед деплоем проверьте:
- ✅ `.env` на сервере правильно настроен
- ✅ `APP_ENV=production`
- ✅ `APP_DEBUG=false`
- ✅ База данных доступна
- ✅ DeepSeek API ключ установлен

### Что делает новая миграция:
```bash
2025_12_31_152737_add_is_banned_to_users_table.php
```
Добавляет поля: `is_banned`, `banned_at`, `ban_reason`

### После деплоя:
- Проверьте админ-панель: `/admin/users`
- Проверьте, что блокировка работает
- Проверьте логи: `storage/logs/laravel.log`

---

## 🐛 Если что-то пошло не так

### Откатить миграцию:
```bash
php artisan migrate:rollback --step=1
```

### Откатить код:
```bash
git reset --hard HEAD~1
git pull origin main
```

### Посмотреть логи:
```bash
tail -f storage/logs/laravel.log
```

### Проверить права:
```bash
ls -la storage/
ls -la bootstrap/cache/
```

---

## 📋 Checklist после деплоя

- [ ] Сайт открывается
- [ ] Авторизация работает
- [ ] Админ-панель доступна
- [ ] `/admin/users` показывает пользователей
- [ ] Кнопки "Заблокировать" и "Удалить" видны
- [ ] Анализатор снов работает
- [ ] Нет ошибок в логах

