<?php

namespace App\Helpers;

use App\Models\Comment;
use App\Models\DreamInterpretation;
use App\Models\Report;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class StatisticsHelper
{
    /**
     * Очистить кеш статистики
     */
    public static function clearCache(): void
    {
        Cache::forget('global_statistics');
        Cache::forget('global_statistics_avg_dreams');
    }
    
    /**
     * Получить глобальную статистику проекта
     * 
     * @param bool $includeAvgDreams Включить среднее количество снов на отчет
     * @return array
     */
    public static function getGlobalStatistics(bool $includeAvgDreams = false): array
    {
        // Используем единый ключ кеша для базовой статистики
        $baseStats = Cache::remember('global_statistics', 900, function () {
            $minDate = \Carbon\Carbon::create(2026, 1, 16, 0, 0, 0);
            
            return [
                'users' => User::count(),
                'reports' => Report::where('status', 'published')->count(),
                'dreams' => DB::table('dreams')
                    ->join('reports', 'dreams.report_id', '=', 'reports.id')
                    ->where('reports.status', 'published')
                    ->count(),
                'comments' => Comment::count(),
                'tags' => Tag::count(),
                'interpretations' => DreamInterpretation::where('processing_status', 'completed')
                    ->whereNull('api_error')
                    ->whereHas('result')
                    ->where('created_at', '>=', $minDate)
                    ->count(),
            ];
        });
        
        // Если нужен avg_dreams_per_report, добавляем его отдельно (не кешируем в базовом кеше)
        if ($includeAvgDreams) {
            $baseStats['avg_dreams_per_report'] = Cache::remember('global_statistics_avg_dreams', 900, function () {
                return Report::where('status', 'published')
                    ->withCount('dreams')
                    ->get()
                    ->avg('dreams_count') ?: 0;
            });
        }
        
        return $baseStats;
    }
}
