# Free port 3000 and start PHP built-in server (router: public/server.php)
$port = 3000

$conn = Get-NetTCPConnection -LocalPort $port -State Listen -ErrorAction SilentlyContinue
if ($conn) {
    $pid = $conn.OwningProcess | Select-Object -First 1
    Write-Host "Port $port in use (PID: $pid), stopping..." -ForegroundColor Yellow
    Stop-Process -Id $pid -Force -ErrorAction SilentlyContinue
    Start-Sleep -Seconds 2
}

Write-Host "Starting PHP at http://127.0.0.1:$port" -ForegroundColor Green
Set-Location $PSScriptRoot
php -S 127.0.0.1:$port -t public public/server.php
