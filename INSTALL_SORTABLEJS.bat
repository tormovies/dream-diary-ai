@echo off
echo Installing SortableJS locally...
cd /d "%~dp0"
if not exist "public\js\sortablejs" mkdir "public\js\sortablejs"
powershell -Command "Invoke-WebRequest -Uri 'https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js' -OutFile 'public\js\sortablejs\Sortable.min.js'"
if exist "public\js\sortablejs\Sortable.min.js" (
    echo SortableJS installed successfully!
) else (
    echo Failed to install SortableJS. Please download manually from:
    echo https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js
    echo And place it in: public\js\sortablejs\Sortable.min.js
)
pause
