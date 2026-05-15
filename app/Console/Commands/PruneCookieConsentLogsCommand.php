<?php

namespace App\Console\Commands;

use App\Models\CookieConsentLog;
use Illuminate\Console\Command;

class PruneCookieConsentLogsCommand extends Command
{
    protected $signature = 'consent:prune-logs {--days=730 : Удалить записи старше указанного числа дней}';

    protected $description = 'Удаление старых записей журнала cookie-согласий';

    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));
        $cutoff = now()->subDays($days);

        $deleted = CookieConsentLog::query()->where('created_at', '<', $cutoff)->delete();

        $this->info("Удалено записей consent-log: {$deleted} (старше {$days} дн.).");

        return self::SUCCESS;
    }
}
