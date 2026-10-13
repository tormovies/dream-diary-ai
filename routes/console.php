<?php

use App\Services\YandexSeoImportService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('seo:yandex-sync', function (YandexSeoImportService $import) {
    $import->tick();
    $this->info('Цикл загрузки Яндекс SEO завершён.');
})->purpose('Загрузить очередной чанк страниц и поисковой статистики Яндекса');

Artisan::command('seo:yandex-recover {task : ID задачи} {--new-export : Подтвердить новую выгрузку с возможным повторным расходованием квоты}', function (YandexSeoImportService $import) {
    $task = filter_var($this->argument('task'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($task === false) {
        $this->error('Укажите положительный ID задачи.');

        return 1;
    }
    $import->recoverTask($task, (bool) $this->option('new-export'));
    $this->info('Задача восстановлена. Следующий цикл продолжит загрузку.');

    return 0;
})->purpose('Повторно скачать готовую выгрузку или явно разрешить создание новой');
