# Проверка наличия npm на сервере

## Проверка npm

**На сервере выполните:**
```bash
ssh adminfeg@adminfeg.beget.tech
cd ~/snovidec.ru/laravel

# Проверить наличие npm
which npm
npm --version

# Проверить наличие node
which node
node --version

# Проверить, есть ли node_modules
ls -la node_modules 2>/dev/null || echo "node_modules не найдена"
```

## Если npm есть

Можно пересобирать build на сервере после каждого `git pull`:

```bash
cd ~/snovidec.ru/laravel
git pull origin main
npm install  # если нужно обновить зависимости
npm run build
```

## Если npm нет

Можно установить через nvm (Node Version Manager) или напрямую. На Beget обычно можно установить через:

```bash
# Проверить, есть ли nvm
source ~/.nvm/nvm.sh 2>/dev/null || echo "nvm не найден"

# Или установить Node.js через пакетный менеджер (если есть права)
```

## Альтернатива: использовать npx (не требует установки npm глобально)

Если есть Node.js, можно использовать npx напрямую:

```bash
npx vite build
```
