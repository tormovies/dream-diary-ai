# Установка Node.js и npm на сервере Beget

## Вариант 1: Установка через nvm (Node Version Manager) - РЕКОМЕНДУЕТСЯ

**На сервере выполните:**
```bash
# Установить nvm
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.0/install.sh | bash

# Перезагрузить профиль
source ~/.bashrc

# Или вручную добавить в ~/.bashrc:
# export NVM_DIR="$HOME/.nvm"
# [ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh"

# Установить Node.js (последняя LTS версия)
nvm install --lts

# Использовать установленную версию
nvm use --lts

# Проверить
node --version
npm --version
```

## Вариант 2: Установка через бинарники Node.js

```bash
# Скачать Node.js
cd ~
wget https://nodejs.org/dist/v20.11.0/node-v20.11.0-linux-x64.tar.xz

# Распаковать
tar -xf node-v20.11.0-linux-x64.tar.xz

# Добавить в PATH (добавить в ~/.bashrc)
export PATH=$HOME/node-v20.11.0-linux-x64/bin:$PATH

# Перезагрузить профиль
source ~/.bashrc

# Проверить
node --version
npm --version
```

## Вариант 3: Использовать системный пакетный менеджер (если есть права)

```bash
# Для Debian/Ubuntu (если есть sudo)
sudo apt-get update
sudo apt-get install -y nodejs npm

# Или для CentOS/RHEL
sudo yum install -y nodejs npm
```

## После установки

Добавить в процесс деплоя:
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

## Проверка

После установки проверьте:
```bash
node --version  # должно показать версию
npm --version   # должно показать версию
```
