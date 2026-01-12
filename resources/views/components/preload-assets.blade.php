@php
    // Preload критических ресурсов для оптимизации загрузки
    // Vite автоматически добавляет preload, но мы добавляем явный для лучшей оптимизации
    // В dev режиме Vite сам управляет preload, в production добавляем явно
    if (app()->environment('production')) {
        $manifestPath = public_path('build/manifest.json');
        if (file_exists($manifestPath)) {
            $manifest = json_decode(file_get_contents($manifestPath), true);
            
            // Preload CSS - используем путь из манифеста
            if (isset($manifest['resources/css/app.css']['file'])) {
                $cssPath = '/build/' . $manifest['resources/css/app.css']['file'];
                echo '<link rel="preload" href="' . $cssPath . '" as="style">' . "\n";
            }
            
            // Preload JS - основной файл
            if (isset($manifest['resources/js/app.js']['file'])) {
                $jsPath = '/build/' . $manifest['resources/js/app.js']['file'];
                echo '<link rel="preload" href="' . $jsPath . '" as="script">' . "\n";
            }
        }
    }
@endphp
