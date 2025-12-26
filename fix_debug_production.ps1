# Скрипт для отключения Debug Mode на продакшене
Write-Host "Отключаем Debug Mode на продакшене..." -ForegroundColor Yellow

$commands = @"
cd ~/snovidec.ru/public_html
sed -i 's/APP_DEBUG=true/APP_DEBUG=false/' .env
php8.3 artisan config:clear
php8.3 artisan config:cache
echo 'Debug Mode отключен!'
php8.3 artisan about | grep 'Debug Mode'
"@

ssh tormoviw@tormoviw.beget.tech $commands

Write-Host "`nГотово! Debug Mode отключен на продакшене." -ForegroundColor Green



