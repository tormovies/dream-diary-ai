@echo off
echo Удаление старой версии Font Awesome...
echo.

if exist "public\fonts\fontawesome" (
    echo Найдена папка public\fonts\fontawesome
    rmdir /s /q "public\fonts\fontawesome"
    echo Папка удалена.
) else (
    echo Папка public\fonts\fontawesome не найдена.
)

if exist "public\fonts" (
    dir "public\fonts" /b >nul 2>&1
    if errorlevel 1 (
        echo Папка public\fonts пуста, удаляем...
        rmdir "public\fonts"
    ) else (
        echo В папке public\fonts остались другие файлы.
    )
)

echo.
echo Готово!
pause
