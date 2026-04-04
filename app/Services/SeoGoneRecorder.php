<?php

namespace App\Services;

use App\Helpers\InterpretationLinkHelper;
use App\Models\DreamInterpretation;
use App\Models\Redirect;
use App\Models\Report;
use App\Models\SeoGoneUrl;
use App\Models\User;
class SeoGoneRecorder
{
    public const SOURCE_MANUAL = 'manual';

    public const SOURCE_ADMIN_PURGE = 'admin_purge';

    public const SOURCE_ADMIN_DELETE = 'admin_delete';

    public const SOURCE_USER_DELETE = 'user_delete';

    /**
     * Записать путь (дубликаты по path игнорируются).
     */
    public static function recordPath(string $path, string $source, ?string $note = null): void
    {
        $normalized = SeoGoneUrl::normalizePath($path);
        if ($normalized === '/') {
            return;
        }

        SeoGoneUrl::query()->firstOrCreate(
            ['path' => $normalized],
            ['source' => $source, 'note' => $note]
        );
    }

    public static function recordNamedRoute(string $name, array $parameters, string $source, ?string $note = null): void
    {
        if (! \Illuminate\Support\Facades\Route::has($name)) {
            return;
        }
        $relative = route($name, $parameters, false);
        $trimmed = ltrim((string) $relative, '/');
        self::recordPath($trimmed, $source, $note);
    }

    /**
     * Записать URL отчёта и страницы анализа, если отчёт когда-либо был доступен публично.
     */
    public static function recordPublicReportIfNeeded(Report $report, string $source, ?string $note = null): void
    {
        if (! InterpretationLinkHelper::isReportPubliclyListed($report)) {
            return;
        }

        self::recordNamedRoute('reports.show', ['report' => $report->id], $source, $note);

        if ($report->analysis_id && $report->hasAnalysis()) {
            self::recordNamedRoute('reports.analysis', ['report' => $report->id], $source, $note);
        }
    }

    /**
     * Записать URL толкования.
     */
    public static function recordInterpretationIfNeeded(DreamInterpretation $interpretation, string $source, ?string $note = null): void
    {
        if (! InterpretationLinkHelper::isInterpretationPubliclyListed($interpretation)) {
            return;
        }

        self::recordNamedRoute('dream-analyzer.show', ['hash' => $interpretation->hash], $source, $note);
    }

    /**
     * Профиль и дневник (при полном удалении аккаунта или по согласованной логике).
     */
    public static function recordUserProfileAndDiaryPaths(User $user, string $source, ?string $note = null): void
    {
        self::recordNamedRoute('users.profile', ['user' => $user->id], $source, $note);

        if (! empty($user->public_link)) {
            self::recordNamedRoute('diary.public', ['publicLink' => $user->public_link], $source, $note);
        }

        self::recordNamedRoute('diary.show', ['user' => $user->id], $source, $note);
    }

    /**
     * Все публичные URL пользователя перед удалением аккаунта / полной очисткой.
     */
    public static function recordAllPublicContentForUser(User $user, string $source, ?string $note = null): void
    {
        self::recordUserProfileAndDiaryPaths($user, $source, $note);

        $interpretations = DreamInterpretation::query()
            ->where('user_id', $user->id)
            ->get();

        foreach ($interpretations as $interpretation) {
            self::recordInterpretationIfNeeded($interpretation, $source, $note);
        }

        $reports = Report::query()->where('user_id', $user->id)->get();
        foreach ($reports as $report) {
            self::recordPublicReportIfNeeded($report, $source, $note);
        }
    }
}
