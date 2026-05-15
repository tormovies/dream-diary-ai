<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PruneRequestLogsCommand extends Command
{
    protected $signature = 'logs:prune-requests {--days=90 : Удалить записи старше указанного числа дней}';

    protected $description = 'Очистка таблицы HTTP/request-логов (если она есть в проекте)';

    public function handle(): int
    {
        if (! Schema::hasTable('request_logs')) {
            $this->warn('Таблица request_logs не найдена — очистка пропущена (в текущей схеме БД её нет).');

            return self::SUCCESS;
        }

        $days = max(1, (int) $this->option('days'));
        $cutoff = now()->subDays($days);

        $deleted = DB::table('request_logs')->where('created_at', '<', $cutoff)->delete();

        $this->info("Удалено записей request_logs: {$deleted} (старше {$days} дн.).");

        return self::SUCCESS;
    }
}
