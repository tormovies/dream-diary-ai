# 🚀 Команды для деплоя (после git push)

## ✅ Push выполнен успешно
Изменения отправлены в репозиторий: `https://github.com/tormovies/dream-diary-ai.git`

---

## 📋 Команды для деплоя на сервер Beget

### Вариант 1: Быстрая команда (одной строкой)

```bash
ssh adminfeg@adminfeg.beget.tech
cd ~/snovidec.ru/laravel && git pull origin main && npm install && npm run build && php8.3 /home/a/adminfeg/.local/bin/composer install --no-dev --optimize-autoloader && php8.3 artisan migrate --force && php8.3 artisan view:clear && php8.3 artisan cache:clear && php8.3 artisan config:clear && php8.3 artisan route:clear && php8.3 artisan config:cache && php8.3 artisan route:cache && php8.3 artisan view:cache && php8.3 artisan optimize
```

### Вариант 2: Пошагово (рекомендуется для первого раза)

```bash
# 1. Подключение к серверу
ssh adminfeg@adminfeg.beget.tech
# Пароль: fRAxngtck8um

# 2. Переход в директорию проекта
cd ~/snovidec.ru/laravel

# 3. Получение последних изменений из GitHub
git pull origin main

# 4. Установка/обновление зависимостей npm и сборка фронтенда
npm install
npm run build

# 5. Установка/обновление зависимостей Composer
php8.3 /home/a/adminfeg/.local/bin/composer install --no-dev --optimize-autoloader

# 6. Применение миграций (если есть новые)
php8.3 artisan migrate --force

# 7. Очистка всех кэшей
php8.3 artisan view:clear
php8.3 artisan cache:clear
php8.3 artisan config:clear
php8.3 artisan route:clear

# 8. Кэширование для продакшена (оптимизация)
php8.3 artisan config:cache
php8.3 artisan route:cache
php8.3 artisan view:cache
php8.3 artisan optimize
```

---

## 🔍 Проверка после деплоя

```bash
# Проверить последний коммит
git log --oneline -1

# Проверить статус Git
git status

# Проверить логи (если что-то не работает)
tail -n 50 storage/logs/laravel.log
```

---

## ⚠️ Важные замечания

- **PHP версия:** Используется `php8.3` (не просто `php`)
- **Composer:** Полный путь `/home/a/adminfeg/.local/bin/composer`
- **Node.js/npm:** Установлены на сервере (Node.js v20.19.0, npm 10.2.4)
- **Build:** Пересобирается на сервере автоматически после `npm run build`

---

## 📝 Информация о сервере

- **Хост:** adminfeg@adminfeg.beget.tech
- **Пароль:** fRAxngtck8um
- **Путь к проекту:** ~/snovidec.ru/laravel
- **PHP версия:** 8.3
- **Node.js версия:** v20.19.0
- **npm версия:** 10.2.4

---

## 🔄 Откат изменений (если нужно)

```bash
cd ~/snovidec.ru/laravel
git log --oneline -5  # посмотреть последние коммиты
git reset --hard <commit-hash>  # откатиться к нужному коммиту
# Затем повторить команды деплоя (npm install, build, composer install, кэши)
```

---

**Дата:** 2026-01-24  
**Последний push:** Успешно выполнен
