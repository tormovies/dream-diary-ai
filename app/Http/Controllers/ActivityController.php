<?php

namespace App\Http\Controllers;

use App\Helpers\SeoHelper;
use App\Models\Comment;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityController extends Controller
{
    /**
     * Лента активности
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $filter = $request->get('filter', 'all'); // all, friends

        // Получаем ID друзей (только для авторизованных)
        $friendIds = [];
        if ($user && $filter === 'friends') {
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
        }

        // Получаем последние комментарии
        // Учитываем иерархию: diary_privacy (главное) -> access_level
        $commentsQuery = Comment::with(['user', 'report.user'])
            ->where('parent_id', null) // Только корневые комментарии
            ->whereHas('report', function ($q) use ($user, $friendIds, $filter) {
                $q->where('status', 'published');
                
                // Применяем фильтры по доступу с учетом diary_privacy
                if ($user && $filter === 'friends') {
                    // Для авторизованных: только отчеты друзей
                    $q->whereIn('user_id', $friendIds)
                      ->where(function ($query) use ($friendIds) {
                          // Дневники друзей: public с любым access_level, friends (игнорируем access_level)
                          $query->where(function ($publicQuery) {
                              $publicQuery->whereHas('user', function ($uq) {
                                  $uq->where('diary_privacy', 'public');
                              })
                              ->whereIn('access_level', ['all', 'friends']);
                          })
                          ->orWhereHas('user', function ($friendDiaryQuery) {
                              $friendDiaryQuery->where('diary_privacy', 'friends');
                          });
                      });
                } else {
                    if ($user) {
                        $q->where(function ($query) use ($user, $friendIds) {
                            // Свои отчеты
                            $query->where('user_id', $user->id)
                                  // Публичные дневники с правильным access_level
                                  ->orWhere(function ($publicQuery) use ($friendIds) {
                                      $publicQuery->whereHas('user', function ($uq) {
                                          $uq->where('diary_privacy', 'public');
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
                                  // Дневники друзей (diary_privacy = 'friends')
                                  ->orWhere(function ($friendDiaryQuery) use ($friendIds) {
                                      if (!empty($friendIds)) {
                                          $friendDiaryQuery->whereHas('user', function ($uq) {
                                              $uq->where('diary_privacy', 'friends');
                                          })
                                          ->whereIn('user_id', $friendIds);
                                      }
                                  });
                        });
                    } else {
                        // Для неавторизованных: только публичные дневники + access_level = 'all'
                        $q->whereHas('user', function ($userQuery) {
                            $userQuery->where('diary_privacy', 'public');
                        })
                        ->where('access_level', 'all');
                    }
                }
            })
            ->orderBy('created_at', 'desc')
            ->limit(20);

        $comments = $commentsQuery->get();

        // Получаем последние отчеты (только опубликованные)
        // Оптимизация: добавлен eager loading для comments
        // Учитываем иерархию: diary_privacy (главное) -> access_level
        $reportsQuery = Report::with(['user', 'dreams', 'tags', 'comments'])
            ->where('status', 'published');
        
        // Применяем фильтры по доступу с учетом diary_privacy
        if ($user && $filter === 'friends') {
            // Для авторизованных: только отчеты друзей
            $reportsQuery->whereIn('user_id', $friendIds)
                ->where(function ($query) use ($friendIds) {
                    // Дневники друзей: public с любым access_level, friends (игнорируем access_level)
                    $query->where(function ($publicQuery) {
                        $publicQuery->whereHas('user', function ($uq) {
                            $uq->where('diary_privacy', 'public');
                        })
                        ->whereIn('access_level', ['all', 'friends']);
                    })
                    ->orWhereHas('user', function ($friendDiaryQuery) {
                        $friendDiaryQuery->where('diary_privacy', 'friends');
                    });
                });
        } else {
            if ($user) {
                $reportsQuery->where(function ($query) use ($user, $friendIds) {
                    // Свои отчеты
                    $query->where('user_id', $user->id)
                          // Публичные дневники с правильным access_level
                          ->orWhere(function ($publicQuery) use ($friendIds) {
                              $publicQuery->whereHas('user', function ($uq) {
                                  $uq->where('diary_privacy', 'public');
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
                          // Дневники друзей (diary_privacy = 'friends')
                          ->orWhere(function ($friendDiaryQuery) use ($friendIds) {
                              if (!empty($friendIds)) {
                                  $friendDiaryQuery->whereHas('user', function ($uq) {
                                      $uq->where('diary_privacy', 'friends');
                                  })
                                  ->whereIn('user_id', $friendIds);
                              }
                          });
                });
            } else {
                // Для неавторизованных: только публичные дневники + access_level = 'all'
                $reportsQuery->whereHas('user', function ($userQuery) {
                    $userQuery->where('diary_privacy', 'public');
                })
                ->where('access_level', 'all');
            }
        }
        
        $reportsQuery->orderBy('created_at', 'desc')
            ->limit(20);

        $reports = $reportsQuery->get();

        // Объединяем и сортируем по дате
        $activities = collect()
            ->merge($comments->map(function ($comment) {
                return [
                    'type' => 'comment',
                    'item' => $comment,
                    'date' => $comment->created_at,
                ];
            }))
            ->merge($reports->map(function ($report) {
                return [
                    'type' => 'report',
                    'item' => $report,
                    'date' => $report->created_at,
                ];
            }))
            ->sortByDesc('date')
            ->take(30);

        // Статистика проекта (с кэшированием на 15 минут)
        $globalStats = \App\Helpers\StatisticsHelper::getGlobalStatistics(true);
        
        // Статистика проекта (для неавторизованных)
        $stats = [
            'users' => $globalStats['users'],
            'reports' => $globalStats['reports'],
            'dreams' => $globalStats['dreams'],
            'comments' => $globalStats['comments'] ?? 0,
            'tags' => $globalStats['tags'] ?? 0,
            'interpretations' => $globalStats['interpretations'] ?? 0,
        ];
        
        // Статистика пользователя (если авторизован)
        $userStats = null;
        $friendsOnline = collect();
        $todayReportsCount = 0;
        
        if ($user) {
            // Статистика пользователя
            $userReportsCount = $user->reports()->count();
            $userDreamsCount = $user->reports()->withCount('dreams')->get()->sum('dreams_count');
            
            // Подсчет друзей
            $allFriendships = \App\Models\Friendship::where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->where('status', 'accepted');
            })->orWhere(function ($query) use ($user) {
                $query->where('friend_id', $user->id)
                    ->where('status', 'accepted');
            })->get();

            $allFriendIds = $allFriendships->map(function ($friendship) use ($user) {
                return $friendship->user_id === $user->id ? $friendship->friend_id : $friendship->user_id;
            })->toArray();
            
            $friendsCount = count($allFriendIds);
            
            // Друзья онлайн
            if (!empty($allFriendIds)) {
                $friendsOnline = \App\Models\User::whereIn('id', $allFriendIds)
                    ->whereHas('reports', function ($q) {
                        $q->whereDate('created_at', '>=', now()->subDays(7));
                    })
                    ->with(['reports' => function ($q) {
                        $q->whereDate('created_at', '>=', now()->subDays(7))->latest()->limit(1);
                    }])
                    ->limit(5)
                    ->get();
            }
            
            // Количество отчетов, добавленных сегодня другими пользователями
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
        } else {
            $todayReportsCount = Report::where('status', 'published')
                ->where('access_level', 'all')
                ->whereDate('created_at', today())
                ->count();
        }

        // Популярные теги (топ 6) - с кэшированием на 1 час
        $popularTags = \Illuminate\Support\Facades\Cache::remember('popular_tags', 3600, function () {
            return \App\Models\Tag::withCount('reports')
                ->orderByDesc('reports_count')
                ->limit(6)
                ->get();
        });

        // Сонник (статичные данные)
        $dreamDictionary = [
            ['symbol' => 'Летать', 'meaning' => 'Символизирует свободу, стремление к независимости, преодоление препятствий. Часто снится в периоды важных жизненных изменений.'],
            ['symbol' => 'Вода', 'meaning' => 'Олицетворяет эмоции, подсознание, очищение и перерождение. Чистая вода — к душевному покою, мутная — к внутренним конфликтам.'],
            ['symbol' => 'Дом', 'meaning' => 'Отражение вашего внутреннего мира. Исследование дома во сне означает самопознание и внутренний рост.'],
            ['symbol' => 'Потеряться', 'meaning' => 'Указывает на чувство растерянности в реальной жизни, поиск своего пути или необходимость принятия важного решения.'],
        ];

        // SEO данные
        $seo = SeoHelper::get('activity');

        // Структурированные данные (Organization)
        $structuredData = [
            SeoHelper::getStructuredDataForOrganization()
        ];

        return view('activity.index', compact('activities', 'filter', 'stats', 'globalStats', 'userStats', 'friendsOnline', 'todayReportsCount', 'popularTags', 'dreamDictionary', 'seo', 'structuredData'));
    }
}
