<?php

namespace App\Console\Commands;

use App\Models\DreamInterpretation;
use App\Models\DreamInterpretationEntity;
use App\Models\DreamInterpretationResult;
use App\Models\DreamInterpretationSeriesDream;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class IndexDreamInterpretationEntities extends Command
{
    protected $signature = 'interpretations:index-entities
                            {--since= : Индексировать только толкования с created_at >= даты (Y-m-d)}
                            {--only-new : Только толкования, у которых ещё нет записей в dream_interpretation_entities}
                            {--chunk=300 : Размер чанка}
                            {--dry-run : Не писать в БД, только показать объём}';

    protected $description = 'Проиндексировать символы, локации и теги из толкований в dream_interpretation_entities (для страниц и статистики по дням)';

    public function handle(): int
    {
        $since = $this->option('since');
        $onlyNew = $this->option('only-new');
        $chunk = (int) $this->option('chunk');
        $dryRun = $this->option('dry-run');

        $query = DreamInterpretation::query()
            ->where('processing_status', 'completed')
            ->whereHas('result')
            ->with(['result' => fn ($q) => $q->select('id', 'dream_interpretation_id', 'type', 'key_symbols', 'unified_locations', 'key_tags')]);

        if ($since !== null) {
            $query->where('created_at', '>=', $since . ' 00:00:00');
        }

        if ($onlyNew) {
            $query->whereDoesntHave('entities');
        }

        $total = $query->count();
        $this->info("Толкований к обработке: {$total}" . ($dryRun ? ' (dry-run)' : ''));

        if ($total === 0) {
            return 0;
        }

        if ($dryRun) {
            $this->info('Dry-run: запись в БД отключена.');
            return 0;
        }

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $query->select('id', 'created_at')
            ->chunk($chunk, function ($interpretations) use ($bar) {
                foreach ($interpretations as $interpretation) {
                    $this->indexOne($interpretation);
                    $bar->advance();
                }
            });

        $bar->finish();
        $this->newLine();
        $this->info('Готово.');

        return 0;
    }

    private function indexOne(DreamInterpretation $interpretation): void
    {
        $interpretation->load([
            'result' => fn ($q) => $q->select('id', 'dream_interpretation_id', 'type', 'key_symbols', 'unified_locations', 'key_tags'),
        ]);

        $result = $interpretation->result;
        if (!$result) {
            return;
        }

        $createdAt = $interpretation->created_at;

        DB::transaction(function () use ($interpretation, $result, $createdAt) {
            // Удаляем старые записи этого толкования (идемпотентность при повторном запуске)
            DreamInterpretationEntity::where('dream_interpretation_id', $interpretation->id)->delete();

            $rows = [];

            // Из результата (одиночный сон или заголовок серии)
            $this->collectFromResult($result->id, self::SOURCE_RESULT, $interpretation->id, $createdAt, $result->key_symbols ?? [], $result->unified_locations ?? [], $result->key_tags ?? [], $rows);

            // Из снов серии
            if ($result->type === 'series') {
                $seriesDreams = DreamInterpretationSeriesDream::where('dream_interpretation_result_id', $result->id)
                    ->select('id', 'key_symbols', 'unified_locations', 'key_tags')
                    ->get();
                foreach ($seriesDreams as $dream) {
                    $this->collectFromResult($dream->id, self::SOURCE_SERIES_DREAM, $interpretation->id, $createdAt, $dream->key_symbols ?? [], $dream->unified_locations ?? [], $dream->key_tags ?? [], $rows);
                }
            }

            if (!empty($rows)) {
                DreamInterpretationEntity::insert($rows);
            }
        });
    }

    private const SOURCE_RESULT = 'result';
    private const SOURCE_SERIES_DREAM = 'series_dream';

    private function collectFromResult(
        int $sourceId,
        string $source,
        int $interpretationId,
        $interpretationCreatedAt,
        array $keySymbols,
        array $unifiedLocations,
        array $keyTags,
        array &$rows
    ): void {
        $now = now()->format('Y-m-d H:i:s');
        $createdAt = $interpretationCreatedAt instanceof \DateTimeInterface
            ? $interpretationCreatedAt->format('Y-m-d H:i:s')
            : $interpretationCreatedAt;

        foreach ($keySymbols as $item) {
            $name = is_array($item) ? trim(strip_tags((string) ($item['symbol'] ?? ''))) : trim(strip_tags((string) $item));
            if ($name === '') {
                continue;
            }
            $slug = DreamInterpretationEntity::nameToSlug($name);
            $meaning = is_array($item) && isset($item['meaning']) ? $item['meaning'] : null;
            $canonicalName = mb_substr(mb_strtolower($name, 'UTF-8'), 0, 500);
            $rows[] = [
                'dream_interpretation_id' => $interpretationId,
                'type' => DreamInterpretationEntity::TYPE_SYMBOL,
                'slug' => $slug,
                'name' => $canonicalName,
                'meaning' => $meaning ? mb_substr($meaning, 0, 65535) : null,
                'source' => $source,
                'source_id' => $sourceId,
                'interpretation_created_at' => $createdAt,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach ($unifiedLocations as $loc) {
            $name = is_string($loc) ? trim($loc) : '';
            if ($name === '') {
                continue;
            }
            $slug = DreamInterpretationEntity::nameToSlug($name);
            $canonicalName = mb_substr(mb_strtolower($name, 'UTF-8'), 0, 500);
            $rows[] = [
                'dream_interpretation_id' => $interpretationId,
                'type' => DreamInterpretationEntity::TYPE_LOCATION,
                'slug' => $slug,
                'name' => $canonicalName,
                'meaning' => null,
                'source' => $source,
                'source_id' => $sourceId,
                'interpretation_created_at' => $createdAt,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach ($keyTags as $tag) {
            $name = is_string($tag) ? trim($tag) : '';
            if ($name === '') {
                continue;
            }
            $slug = DreamInterpretationEntity::nameToSlug($name);
            $canonicalName = mb_substr(mb_strtolower($name, 'UTF-8'), 0, 500);
            $rows[] = [
                'dream_interpretation_id' => $interpretationId,
                'type' => DreamInterpretationEntity::TYPE_TAG,
                'slug' => $slug,
                'name' => $canonicalName,
                'meaning' => null,
                'source' => $source,
                'source_id' => $sourceId,
                'interpretation_created_at' => $createdAt,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
    }
}
