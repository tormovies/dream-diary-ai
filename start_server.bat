@echo off
chcp 65001 >nul
cd /d "%~dp0"

echo Освобождаю порт 3000...
for /f "tokens=5" %%a in ('netstat -ano ^| findstr :3000 ^| findstr LISTENING') do (
    taskkill /PID %%a /F 2>nul
    timeout /t 1 /nobreak >nul
)

echo.
echo Запуск PHP (без Artisan) на http://127.0.0.1:3000
echo Роутер: public/server.php
echo Остановка: Ctrl+C
echo.
php -S 127.0.0.1:3000 -t public public/server.php
