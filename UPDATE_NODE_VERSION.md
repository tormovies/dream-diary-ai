# Обновление Node.js на сервере

## Текущая ситуация

- **Установлено:** Node.js v20.11.0
- **Требуется:** Node.js ^20.19.0 или >=22.12.0
- **Статус:** Работает, но есть предупреждения

## Обновление Node.js

### Если использовали прямую установку (вариант 2)

**На сервере:**
```bash
cd ~/nodejs

# Удалить старую версию
rm -rf node-v20.11.0-linux-x64

# Скачать новую версию (20.19.0 или 22.12.0)
wget https://nodejs.org/dist/v20.19.0/node-v20.19.0-linux-x64.tar.xz

# Распаковать
tar -xf node-v20.19.0-linux-x64.tar.xz

# Обновить PATH в ~/.bash_profile
# Заменить строку с node-v20.11.0 на node-v20.19.0
sed -i 's/node-v20.11.0/node-v20.19.0/g' ~/.bash_profile

# Загрузить в текущей сессии
export PATH=$HOME/nodejs/node-v20.19.0-linux-x64/bin:$PATH

# Проверить
node --version
npm --version
```

### Если использовали nvm

**На сервере:**
```bash
# Загрузить nvm
export NVM_DIR="$HOME/.nvm"
[ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh"

# Установить новую версию
nvm install 20.19.0
nvm use 20.19.0
nvm alias default 20.19.0

# Проверить
node --version
npm --version
```

## Альтернатива: Обновить до LTS версии 22.x

```bash
# Для прямой установки
wget https://nodejs.org/dist/v22.12.0/node-v22.12.0-linux-x64.tar.xz
tar -xf node-v22.12.0-linux-x64.tar.xz
# Обновить PATH аналогично

# Для nvm
nvm install 22.12.0
nvm use 22.12.0
```

## После обновления

Переустановить зависимости:
```bash
cd ~/snovidec.ru/laravel
rm -rf node_modules package-lock.json
npm install
npm run build
```

## Важно

- Предупреждения не критичны - все работает
- Но лучше обновить для совместимости с новыми версиями пакетов
- После обновления предупреждения исчезнут
