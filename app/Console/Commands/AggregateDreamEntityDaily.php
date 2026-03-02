<?php

namespace App\Console\Commands;

use App\Models\DreamEntityDaily;
use App\Models\DreamInterpretationEntity;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AggregateDreamEntityDaily extends Command
{
    protected $signature = 'interpretations:aggregate-entity-daily
                            {--date= : Агрегировать за конкретную дату (Y-m-d)}
                            {--all : Обработать все даты из dream_interpretation_entities}
                            {--yesterday : Агрегировать за вчера в часовом поясе из настроек (для cron)}';

    protected $description = 'Агрегировать сущности по дням из dream_interpretation_entities в dream_entity_daily';

    public function handle(): int
    {
        $dateOpt = $this->option('date');
        $all = $this->option('all');
        $yesterday = $this->option('yesterday');

        $timezone = null;
        if ($yesterday) {
            $timezone = $this->getSettingsTimezone();
            $dates = [Carbon::now($timezone)->subDay()->format('Y-m-d')];
            $this->line("Часовой пояс: {$timezone}");
        } elseif ($dateOpt) {
            $dates = [$dateOpt];
        } elseif ($all) {
            $dates = DreamInterpretationEntity::query()
                ->selectRaw('DATE(interpretation_created_at) as d')
                ->distinct()
                ->pluck('d')
                ->map(fn ($d) => $d instanceof \DateTimeInterface ? $d->format('Y-m-d') : (string) $d)
                ->filter()
                ->values()
                ->toArray();
            $this->info('Найдено дат: ' . count($dates));
        } else {
            $this->error('Укажите --date=Y-m-d, --yesterday или --all');
            return 1;
        }

        foreach ($dates as $date) {
            $this->aggregateDate($date, $timezone);
        }

        $this->info('Готово.');
        return 0;
    }

    private function getSettingsTimezone(): string
    {
        $tz = Setting::getValue('timezone', config('app.timezone', 'UTC'));
        if (is_string($tz) && $tz !== '' && @timezone_open($tz)) {
            return $tz;
        }
        return config('app.timezone', 'UTC');
    }

    /**
     * @param string|null $timezone Если задан, дата считается в этом поясе; границы конвертируются в UTC для запроса (interpretation_created_at в БД в UTC).
     */
    private function aggregateDate(string $date, ?string $timezone = null): void
    {
        if ($timezone !== null) {
            $start = Carbon::parse($date . ' 00:00:00', $timezone)->setTimezone('UTC')->format('Y-m-d H:i:s');
            $end = Carbon::parse($date . ' 23:59:59', $timezone)->setTimezone('UTC')->format('Y-m-d H:i:s');
        } else {
            $start = $date . ' 00:00:00';
            $end = $date . ' 23:59:59';
        }

        if ($timezone !== null) {
            $rows = DreamInterpretationEntity::query()
                ->whereBetween('interpretation_created_at', [$start, $end])
                ->get(['interpretation_created_at', 'type', 'slug', 'name']);
            $grouped = $rows->groupBy(function ($r) use ($timezone) {
                return Carbon::parse($r->interpretation_created_at)->setTimezone($timezone)->format('Y-m-d');
            });
            $rows = $grouped->get($date, collect())->groupBy(fn ($r) => $r->type . '|' . $r->slug)->map(function ($group) {
                $first = $group->first();
                return (object) [
                    'date' => $date,
                    'type' => $first->type,
                    'slug' => $first->slug,
                    'name' => $group->pluck('name')->filter()->first() ?? $first->name,
                    'mentions' => $group->count(),
                ];
            })->values();
        } else {
            $rows = DreamInterpretationEntity::query()
                ->whereBetween('interpretation_created_at', [$start, $end])
                ->selectRaw('DATE(interpretation_created_at) as date, type, slug, MAX(name) as name, COUNT(*) as mentions')
                ->groupBy(DB::raw('DATE(interpretation_created_at)'), 'type', 'slug')
                ->get();
        }

        if ($rows->isEmpty()) {
            $this->line("  {$date}: нет данных");
            return;
        }

        DreamEntityDaily::where('date', $date)->delete();

        $insert = $rows->map(fn ($r) => [
            'date' => $date,
            'type' => $r->type,
            'slug' => $r->slug,
            'name' => $r->name,
            'mentions' => (int) $r->mentions,
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ])->toArray();

        DreamEntityDaily::insert($insert);
        $this->line("  {$date}: записей " . count($insert));
    }
}
