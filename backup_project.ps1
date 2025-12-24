# Backup script for Laravel project

$sourcePath = "C:\Users\torle\snovidec"
$backupRoot = "d:\diaries 2\backup"
$timestamp = Get-Date -Format 'yyyyMMdd_HHmmss'
$backupZip = Join-Path $backupRoot "snovidec_backup_$timestamp.zip"

# Create backup directory
if (-not (Test-Path $backupRoot)) {
    New-Item -ItemType Directory -Path $backupRoot -Force | Out-Null
}

Write-Host "Creating backup..."
Write-Host "Source: $sourcePath"
Write-Host "Archive: $backupZip"
Write-Host ""

# Change to project directory
Push-Location $sourcePath

# Get all files excluding unnecessary ones
$filesToBackup = Get-ChildItem -Path $sourcePath -Recurse -Force | Where-Object {
    $relativePath = $_.FullName.Substring($sourcePath.Length + 1)
    $exclude = $false
    
    # Exclude directories
    if ($_.PSIsContainer) {
        $excludePaths = @(
            "node_modules",
            "vendor",
            ".git",
            "storage\framework\cache",
            "storage\framework\sessions",
            "storage\framework\views",
            "storage\logs",
            "bootstrap\cache",
            "public\build",
            "public\storage",
            "storage\app\public",
            ".cursor"
        )
        foreach ($excludePath in $excludePaths) {
            if ($relativePath -like "$excludePath*") {
                $exclude = $true
                break
            }
        }
    } else {
        # Exclude files
        $excludePatterns = @("*.log", ".env")
        foreach ($pattern in $excludePatterns) {
            if ($_.Name -like $pattern) {
                $exclude = $true
                break
            }
        }
        # Also exclude files in excluded directories
        if ($relativePath -like "node_modules*" -or 
            $relativePath -like "vendor*" -or 
            $relativePath -like ".git*" -or
            $relativePath -like "storage\framework\cache*" -or
            $relativePath -like "storage\framework\sessions*" -or
            $relativePath -like "storage\framework\views*" -or
            $relativePath -like "storage\logs*" -or
            $relativePath -like "bootstrap\cache*" -or
            $relativePath -like "public\build*" -or
            $relativePath -like "public\storage*" -or
            $relativePath -like ".cursor*") {
            $exclude = $true
        }
    }
    
    return -not $exclude
}

Write-Host "Adding files to archive..."

# Filter out non-existent files and directories
$existingFiles = $filesToBackup | Where-Object {
    try {
        if ($_.PSIsContainer) {
            Test-Path $_.FullName
        } else {
            Test-Path $_.FullName
        }
    } catch {
        $false
    }
}

# Create archive only if we have files
if ($existingFiles) {
    $existingFiles | Compress-Archive -DestinationPath $backupZip -CompressionLevel Optimal -Force -ErrorAction SilentlyContinue
} else {
    Write-Host "No files to backup!" -ForegroundColor Yellow
}

Pop-Location

if (Test-Path $backupZip) {
    $zipSize = (Get-Item $backupZip).Length
    $zipSizeMB = [math]::Round($zipSize / 1MB, 2)
    Write-Host ""
    Write-Host "Backup created successfully!" -ForegroundColor Green
    Write-Host "Path: $backupZip" -ForegroundColor Cyan
    Write-Host "Size: $zipSizeMB MB" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "Excluded: node_modules, vendor, .git, cache, logs, build, public\storage, .cursor" -ForegroundColor Gray
    Write-Host "Excluded files: *.log, .env" -ForegroundColor Gray
} else {
    Write-Host ""
    Write-Host "Error creating backup!" -ForegroundColor Red
}






