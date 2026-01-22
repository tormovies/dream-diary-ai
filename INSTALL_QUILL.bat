@echo off
echo Installing Quill Editor...
echo.

cd /d "%~dp0"

REM Создаем папку
if not exist "public\js\quill" mkdir "public\js\quill"

echo Downloading quill.min.js...
powershell -Command "Invoke-WebRequest -Uri 'https://cdn.quilljs.com/1.3.7/quill.min.js' -OutFile 'public\js\quill\quill.min.js'"

echo Downloading quill.snow.css...
powershell -Command "Invoke-WebRequest -Uri 'https://cdn.quilljs.com/1.3.7/quill.snow.css' -OutFile 'public\js\quill\quill.snow.css'"

echo.
echo Installation complete!
echo Files should be in: public\js\quill\
echo.
pause
