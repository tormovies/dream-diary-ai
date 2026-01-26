# Деплой: убраны огромные пробелы на главной (CLS-gaps fix)

**Коммит:** `5c68b9b` — Убрать огромные пробелы на главной: удалён main-grid-reserved-space, min-height для CLS

---

## 1. Подключение к серверу

```bash
ssh adminfeg@adminfeg.beget.tech
```

## 2. Деплой одной командой

```bash
cd ~/snovidec.ru/laravel && git pull origin main && npm install && npm run build && php8.3 /home/a/adminfeg/.local/bin/composer install --no-dev --optimize-autoloader && php8.3 artisan migrate --force && php8.3 artisan view:clear && php8.3 artisan cache:clear && php8.3 artisan config:clear && php8.3 artisan route:clear && php8.3 artisan config:cache && php8.3 artisan route:cache && php8.3 artisan view:cache && php8.3 artisan optimize
```

---

## 3. Пошагово (если нужен контроль)

```bash
cd ~/snovidec.ru/laravel
git pull origin main
npm install
npm run build
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

---

## Проверка после деплоя

- Открыть https://snovidec.ru/ — главная без огромных пробелов между блоками.
- Мобильная вёрстка: карточка «Добро пожаловать», затем «Лента сновидений» без больших пустых областей.
