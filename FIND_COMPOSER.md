# Поиск Composer на сервере

## Команды для поиска Composer

```bash
# 1. Найти все файлы composer
which composer
whereis composer
find ~ -name "composer" -type f 2>/dev/null

# 2. Проверить версию (если composer в PATH)
composer --version

# 3. Проверить, есть ли composer в стандартных местах
ls -la ~/.local/bin/composer
ls -la /usr/local/bin/composer
ls -la /usr/bin/composer
ls -la ~/composer
ls -la ~/bin/composer

# 4. Поиск по всей системе (может занять время)
find /home -name "composer" -type f 2>/dev/null | head -10

# 5. Проверить переменную PATH
echo $PATH

# 6. Проверить, может composer установлен через phar
find ~ -name "composer.phar" 2>/dev/null
```

## После того как найдете composer:

### Вариант 1: Если composer в PATH
```bash
composer --version  # проверить
composer install --no-dev --optimize-autoloader
```

### Вариант 2: Если composer по полному пути
```bash
/path/to/composer --version  # проверить
/path/to/composer install --no-dev --optimize-autoloader
```

### Вариант 3: Если composer.phar
```bash
php8.3 /path/to/composer.phar --version
php8.3 /path/to/composer.phar install --no-dev --optimize-autoloader
```

## Обновить команды деплоя

После того как найдете правильный путь, обновите команды в `DEPLOY_COMMANDS.md`
