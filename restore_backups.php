<?php
$viewsDir = __DIR__ . '/resources/views';

function restoreBackups($dir) {
    $restored = 0;
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    
    foreach ($iterator as $file) {
        if ($file->isFile() && str_ends_with($file->getFilename(), '.blade.php.bak')) {
            $backupPath = $file->getPathname();
            $originalPath = preg_replace('/\.bak$/', '', $backupPath);
            
            if (file_exists($originalPath)) {
                copy($backupPath, $originalPath);
                echo "✓ Восстановлен: " . basename($originalPath) . "\n";
                $restored++;
            }
        }
    }
    
    return $restored;
}

$count = restoreBackups($viewsDir);
echo "\nВсего восстановлено: $count файлов\n";
















