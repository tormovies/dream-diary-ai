# Альтернативные способы установки Node.js/npm на сервере Beget

## Проверка существующих файлов профиля

**На сервере выполните:**
```bash
# Проверить, какие файлы профиля есть
ls -la ~ | grep -E "\.(bash|profile|zsh)"

# Проверить текущий shell
echo $SHELL

# Проверить, какие файлы загружаются при входе
cat ~/.bash_profile 2>/dev/null || echo ".bash_profile не найден"
cat ~/.profile 2>/dev/null || echo ".profile не найден"
```

## Вариант 1: Создать .bashrc или использовать .bash_profile

```bash
# Создать .bashrc если его нет
touch ~/.bashrc

# Или использовать .bash_profile
touch ~/.bash_profile

# Установить nvm
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.0/install.sh | bash

# Добавить в ~/.bash_profile (или ~/.bashrc)
echo 'export NVM_DIR="$HOME/.nvm"' >> ~/.bash_profile
echo '[ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh"' >> ~/.bash_profile
echo '[ -s "$NVM_DIR/bash_completion" ] && \. "$NVM_DIR/bash_completion"' >> ~/.bash_profile

# Загрузить в текущей сессии
export NVM_DIR="$HOME/.nvm"
[ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh"

# Установить Node.js
nvm install --lts
nvm use --lts

# Проверить
node --version
npm --version
```

## Вариант 2: Установка Node.js без nvm (прямая установка)

```bash
# Создать директорию для Node.js
mkdir -p ~/nodejs
cd ~/nodejs

# Скачать Node.js LTS (Linux x64)
wget https://nodejs.org/dist/v20.11.0/node-v20.11.0-linux-x64.tar.xz

# Распаковать
tar -xf node-v20.11.0-linux-x64.tar.xz

# Добавить в PATH (создать или обновить ~/.bash_profile)
echo 'export PATH=$HOME/nodejs/node-v20.11.0-linux-x64/bin:$PATH' >> ~/.bash_profile

# Загрузить в текущей сессии
export PATH=$HOME/nodejs/node-v20.11.0-linux-x64/bin:$PATH

# Проверить
node --version
npm --version

# Очистить архив
rm node-v20.11.0-linux-x64.tar.xz
```

## Вариант 3: Использовать готовый бинарник (самый простой)

```bash
# Скачать и установить в ~/bin
mkdir -p ~/bin
cd ~/bin

# Скачать Node.js
wget https://nodejs.org/dist/v20.11.0/node-v20.11.0-linux-x64.tar.xz
tar -xf node-v20.11.0-linux-x64.tar.xz
mv node-v20.11.0-linux-x64 nodejs
rm node-v20.11.0-linux-x64.tar.xz

# Создать симлинки
ln -s ~/bin/nodejs/bin/node ~/bin/node
ln -s ~/bin/nodejs/bin/npm ~/bin/npm

# Добавить ~/bin в PATH
echo 'export PATH=$HOME/bin:$PATH' >> ~/.bash_profile
export PATH=$HOME/bin:$PATH

# Проверить
node --version
npm --version
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

## Если установка не работает

Можно продолжать использовать текущий процесс:
1. Локально: `npm run build`
2. Локально: `scp -r public/build` на сервер
3. На сервере: решить конфликт git pull перед загрузкой build
