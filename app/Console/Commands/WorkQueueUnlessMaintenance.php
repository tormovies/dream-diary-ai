<?php

namespace App\Console\Commands;

use App\Support\MaintenanceMode;
use Illuminate\Console\Command;

class WorkQueueUnlessMaintenance extends Command
{
    protected $signature = 'queue:worker
        {--max-time=55 : Максимум секунд работы (чтобы не пересекаться со следующим запуском cron)}
        {--sleep=3 : Пауза между проверками очереди (секунды)}';

    protected $description = 'Запуск queue:work для cron «раз в минуту»: не стартует в режиме сервисных работ, останавливается при пустой очереди';

    public function handle(): int
    {
        if (MaintenanceMode::enabled()) {
            $this->info('Режим сервисных работ включён — воркер очереди не запускается.');

            return self::SUCCESS;
        }

        return $this->call('queue:work', [
            '--stop-when-empty' => true,
            '--max-time' => max(1, (int) $this->option('max-time')),
            '--sleep' => max(1, (int) $this->option('sleep')),
        ]);
    }
}
