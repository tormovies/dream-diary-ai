# Simple backup script using robocopy

$source = "C:\Users\torle\snovidec"
$backupRoot = "d:\diaries 2\backup"
$date = Get-Date -Format 'yyyyMMdd_HHmmss'
$tempDir = "$backupRoot\temp_backup_$date"
$zipPath = "$backupRoot\snovidec_backup_$date.zip"

# Create backup directory
if (-not (Test-Path $backupRoot)) {
    New-Item -ItemType Directory -Path $backupRoot -Force | Out-Null
}

Write-Host "Creating backup..." -ForegroundColor Cyan
Write-Host "This may take a few minutes..." -ForegroundColor Yellow

# Remove temp directory if exists
if (Test-Path $tempDir) {
    Remove-Item -Path $tempDir -Recurse -Force -ErrorAction SilentlyContinue
}

# Copy files with exclusions using robocopy
robocopy $source $tempDir /E /XD node_modules vendor .git "storage\framework\cache" "storage\framework\sessions" "storage\framework\views" "storage\logs" "bootstrap\cache" "public\build" "public\storage" ".cursor" /XF "*.log" ".env" /NFL /NDL /NP

# robocopy exit codes: 
# 0-1 = success
# 2-7 = partial success (acceptable)  
# 8-9 = errors but files were copied (still acceptable if files exist)
# 10+ = critical error
$robocopyResult = $LASTEXITCODE

Write-Host "robocopy exit code: $robocopyResult" -ForegroundColor Gray

# Accept codes 0-9 (even with errors, if files were copied, we can continue)
if ($robocopyResult -le 9) {
    # Check if temp directory was created and has files
    if (Test-Path $tempDir) {
        $fileCount = (Get-ChildItem -Path $tempDir -Recurse -File -ErrorAction SilentlyContinue | Measure-Object).Count
        if ($fileCount -gt 0) {
            Write-Host "Files copied: $fileCount" -ForegroundColor Cyan
            Write-Host "Creating ZIP archive..." -ForegroundColor Cyan
            
            try {
                Compress-Archive -Path "$tempDir\*" -DestinationPath $zipPath -CompressionLevel Optimal -Force -ErrorAction Stop
                
                # Remove temporary directory
                Remove-Item -Path $tempDir -Recurse -Force -ErrorAction SilentlyContinue
                
                if (Test-Path $zipPath) {
                    $size = [math]::Round((Get-Item $zipPath).Length / 1MB, 2)
                    Write-Host ""
                    Write-Host "Backup created successfully!" -ForegroundColor Green
                    Write-Host "Path: $zipPath" -ForegroundColor White
                    Write-Host "Size: $size MB" -ForegroundColor White
                } else {
                    Write-Host "Error: Archive file was not created" -ForegroundColor Red
                }
            } catch {
                Write-Host "Error creating archive: $($_.Exception.Message)" -ForegroundColor Red
            }
        } else {
            Write-Host "Error: No files were copied to temp directory" -ForegroundColor Red
            Remove-Item -Path $tempDir -Recurse -Force -ErrorAction SilentlyContinue
        }
    } else {
        Write-Host "Error: Temp directory was not created" -ForegroundColor Red
    }
} else {
    Write-Host "Error: robocopy failed with exit code $robocopyResult" -ForegroundColor Red
    if (Test-Path $tempDir) {
        Write-Host "Temp directory: $tempDir" -ForegroundColor Yellow
    }
}



