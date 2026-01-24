<?php

namespace App\Http\Controllers;

use App\Helpers\SeoHelper;
use App\Models\Comment;
use App\Models\Report;
use App\Models\User;
use App\Models\Dream;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Главная страница
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        
        // Статистика проекта (с кэшированием на 15 минут)
        $globalStats = Cache::remember('global_statistics', 900, function () {
            return [
                'users' => User::count(),
                'reports' => Report::where('status', 'published')->count(),
                'dreams' => DB::table('dreams')
                    ->join('reports', 'dreams.report_id', '=', 'reports.id')
                    ->where('reports.status', 'published')
                    ->count(),
                'comments' => Comment::count(),
                'tags' => Tag::count(),
                'avg_dreams_per_report' => Report::where('status', 'published')
                    ->withCount('dreams')
                    ->get()
                    ->avg('dreams_count') ?: 0,
            ];
        });
        
        // Статистика проекта (для неавторизованных, без кэша для совместимости)
        $stats = [
            'users' => $globalStats['users'],
            'reports' => $globalStats['reports'],
            'dreams' => $globalStats['dreams'],
            'comments' => $globalStats['comments'],
            'tags' => $globalStats['tags'],
        ];
        
        // Статистика пользователя (если авторизован)
        $userStats = null;
        $friendsCount = 0;
        $friendsOnline = collect();
        $todayReportsCount = 0;
        
        if ($user) {
            // Статистика пользователя
            $userReportsCount = $user->reports()->count();
            $userDreamsCount = $user->reports()->withCount('dreams')->get()->sum('dreams_count');
            
            // Подсчет друзей
            $friendships = \App\Models\Friendship::where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->where('status', 'accepted');
            })->orWhere(function ($query) use ($user) {
                $query->where('friend_id', $user->id)
                    ->where('status', 'accepted');
            })->get();

            $friendIds = $friendships->map(function ($friendship) use ($user) {
                return $friendship->user_id === $user->id ? $friendship->friend_id : $friendship->user_id;
            })->toArray();
            
            $friendsCount = count($friendIds);
            
            // Друзья онлайн (последние активные друзья, которые добавили отчеты сегодня или недавно)
            if (!empty($friendIds)) {
                $friendsOnline = User::whereIn('id', $friendIds)
                    ->whereHas('reports', function ($q) {
                        $q->whereDate('created_at', '>=', now()->subDays(7));
                    })
                    ->with(['reports' => function ($q) {
                        $q->whereDate('created_at', '>=', now()->subDays(7))->latest()->limit(1);
                    }])
                    ->limit(5)
                    ->get();
            } else {
                $friendsOnline = collect();
            }
            
            // Количество отчетов, добавленных сегодня другими пользователями (только опубликованные)
            $todayReportsCount = Report::where('status', 'published')
                ->where('access_level', 'all')
                ->whereDate('created_at', today())
                ->where('user_id', '!=', $user->id)
                ->count();
            
            // Среднее количество снов в месяц
            $firstReport = $user->reports()->orderBy('report_date')->first();
            if ($firstReport) {
                $monthsDiff = $firstReport->report_date->diffInMonths(now());
                $avgDreamsPerMonth = $monthsDiff > 0 ? round($userDreamsCount / max($monthsDiff, 1), 1) : $userDreamsCount;
            } else {
                $avgDreamsPerMonth = 0;
            }
            
            $userStats = [
                'reports' => $userReportsCount,
                'dreams' => $userDreamsCount,
                'friends' => $friendsCount,
                'avg_per_month' => $avgDreamsPerMonth,
            ];
            
            $friendIds = $friendIds ?? [];
        } else {
            $friendIds = [];
            $todayReportsCount = Report::where('status', 'published')
                ->where('access_level', 'all')
                ->whereDate('created_at', today())
                ->count();
        }

        // Получаем последние отчеты (только опубликованные) - это "Лента сновидений"
        // Учитываем иерархию: diary_privacy (главное) -> access_level
        $reportsQuery = Report::with(['user', 'dreams', 'tags'])
            ->where('status', 'published');
        
        if ($user) {
            // Для авторизованных пользователей
            $reportsQuery->where(function ($query) use ($user, $friendIds) {
                // 1. Свои отчеты видим всегда
                $query->where('user_id', $user->id)
                      // 2. Отчеты из публичных дневников с правильным access_level
                      ->orWhere(function ($q) use ($user, $friendIds) {
                          $q->whereHas('user', function ($userQuery) {
                              $userQuery->where('diary_privacy', 'public');
                          })
                          ->where(function ($accessQuery) use ($friendIds) {
                              $accessQuery->where('access_level', 'all')
                                          ->orWhere(function ($friendQuery) use ($friendIds) {
                                              if (!empty($friendIds)) {
                                                  $friendQuery->where('access_level', 'friends')
                                                             ->whereIn('user_id', $friendIds);
                                              }
                                          });
                          });
                      })
                      // 3. Отчеты из дневников друзей (diary_privacy = 'friends')
                      ->orWhere(function ($q) use ($friendIds) {
                          if (!empty($friendIds)) {
                              $q->whereHas('user', function ($userQuery) {
                                  $userQuery->where('diary_privacy', 'friends');
                              })
                              ->whereIn('user_id', $friendIds);
                          }
                      });
            });
        } else {
            // Для неавторизованных только публичные дневники + access_level = 'all'
            $reportsQuery->whereHas('user', function ($userQuery) {
                $userQuery->where('diary_privacy', 'public');
            })
            ->where('access_level', 'all');
        }
        
        $reports = $reportsQuery->orderBy('report_date', 'desc')
            ->limit(15)
            ->get();

        // Популярные теги (топ 6) - с кэшированием на 1 час
        $popularTags = Cache::remember('popular_tags', 3600, function () {
            return Tag::withCount('reports')
                ->orderByDesc('reports_count')
                ->limit(6)
                ->get();
        });

        // Сонник (статичные данные для примера)
        $dreamDictionary = [
            ['symbol' => 'Летать', 'meaning' => 'Символизирует свободу, стремление к независимости, преодоление препятствий. Часто снится в периоды важных жизненных изменений.'],
            ['symbol' => 'Вода', 'meaning' => 'Олицетворяет эмоции, подсознание, очищение и перерождение. Чистая вода — к душевному покою, мутная — к внутренним конфликтам.'],
            ['symbol' => 'Дом', 'meaning' => 'Отражение вашего внутреннего мира. Исследование дома во сне означает самопознание и внутренний рост.'],
            ['symbol' => 'Потеряться', 'meaning' => 'Указывает на чувство растерянности в реальной жизни, поиск своего пути или необходимость принятия важного решения.'],
        ];

        // Последние толкования для перелинковки (5 штук)
        $latestInterpretations = \App\Helpers\InterpretationLinkHelper::getLatestInterpretations(0, 5);

        // SEO данные
        $seo = SeoHelper::get('home', null, [
            'total_reports' => $globalStats['reports'],
            'today_reports' => $todayReportsCount,
        ]);

        return view('welcome', compact('stats', 'globalStats', 'reports', 'userStats', 'friendsOnline', 'todayReportsCount', 'popularTags', 'dreamDictionary', 'seo', 'latestInterpretations'));
    }
}

