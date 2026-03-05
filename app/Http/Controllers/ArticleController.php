<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\DreamInterpretation;
use App\Models\DreamInterpretationEntity;
use App\Helpers\SeoHelper;
use Illuminate\View\View;

class ArticleController extends Controller
{
    /**
     * Список инструкций (guide)
     */
    public function guideIndex(): View
    {
        $articles = Article::guide()
            ->published()
            ->orderBy('order', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();

        // SEO для заглавной страницы
        $seo = SeoHelper::get('guide-index');

        // Статистика проекта (с кэшированием на 15 минут)
        $globalStats = \App\Helpers\StatisticsHelper::getGlobalStatistics();

        // Статистика пользователя (только для авторизованных)
        $userStats = null;
        $user = auth()->user();
        if ($user) {
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
        }

        // Структурированные данные (CollectionPage + Organization)
        $structuredData = [
            SeoHelper::getStructuredDataForCollectionPage(
                $seo['h1'] ?? 'Инструкции',
                $seo['description'] ?? 'Полезные инструкции и руководства по использованию платформы',
                'guide.index',
                $seo
            ),
            SeoHelper::getStructuredDataForOrganization()
        ];

        return view('articles.guide.index', [
            'articles' => $articles,
            'seo' => $seo,
            'globalStats' => $globalStats,
            'userStats' => $userStats,
            'structuredData' => $structuredData,
        ]);
    }

    /**
     * Список страниц символов (entity_group)
     */
    public function symbolIndex(): View
    {
        $articles = Article::where('type', 'entity_group')
            ->where('status', 'published')
            ->with('entityGroup')
            ->orderBy('title', 'asc')
            ->get();

        $seo = SeoHelper::get('symbol-index');

        $globalStats = \App\Helpers\StatisticsHelper::getGlobalStatistics();
        $userStats = null;
        $user = auth()->user();
        if ($user) {
            $userReportsCount = $user->reports()->count();
            $userDreamsCount = $user->reports()->withCount('dreams')->get()->sum('dreams_count');
            $allFriendships = \App\Models\Friendship::where(function ($q) use ($user) {
                $q->where('user_id', $user->id)->where('status', 'accepted');
            })->orWhere(function ($q) use ($user) {
                $q->where('friend_id', $user->id)->where('status', 'accepted');
            })->get();
            $allFriendIds = $allFriendships->map(fn ($f) => $f->user_id === $user->id ? $f->friend_id : $f->user_id)->toArray();
            $friendsCount = count($allFriendIds);
            $firstReport = $user->reports()->orderBy('report_date')->first();
            $monthsDiff = $firstReport ? $firstReport->report_date->diffInMonths(now()) : 0;
            $avgDreamsPerMonth = $monthsDiff > 0 && $userDreamsCount ? round($userDreamsCount / max($monthsDiff, 1), 1) : $userDreamsCount;
            $userStats = [
                'reports' => $userReportsCount,
                'dreams' => $userDreamsCount,
                'friends' => $friendsCount,
                'avg_per_month' => $avgDreamsPerMonth,
            ];
        }

        $structuredData = [
            SeoHelper::getStructuredDataForCollectionPage(
                $seo['h1'] ?? 'Символы снов',
                $seo['description'] ?? 'Толкования символов сновидений',
                'symbol.index',
                $seo
            ),
            SeoHelper::getStructuredDataForOrganization()
        ];

        return view('articles.symbol.index', [
            'articles' => $articles,
            'seo' => $seo,
            'globalStats' => $globalStats,
            'userStats' => $userStats,
            'structuredData' => $structuredData,
        ]);
    }

    /**
     * Список статей (article)
     */
    public function articlesIndex(): View
    {
        $articles = Article::article()
            ->published()
            ->orderBy('order', 'asc')
            ->orderBy('published_at', 'desc')
            ->get();

        // SEO для заглавной страницы
        $seo = SeoHelper::get('articles-index');

        // Статистика проекта (с кэшированием на 15 минут)
        $globalStats = \App\Helpers\StatisticsHelper::getGlobalStatistics();

        // Статистика пользователя (только для авторизованных)
        $userStats = null;
        $user = auth()->user();
        if ($user) {
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
        }

        // Структурированные данные (CollectionPage + Organization)
        $structuredData = [
            SeoHelper::getStructuredDataForCollectionPage(
                $seo['h1'] ?? 'Статьи',
                $seo['description'] ?? 'Интересные статьи о сновидениях, психологии сна и анализе снов',
                'articles.index',
                $seo
            ),
            SeoHelper::getStructuredDataForOrganization()
        ];

        return view('articles.articles.index', [
            'articles' => $articles,
            'seo' => $seo,
            'globalStats' => $globalStats,
            'userStats' => $userStats,
            'structuredData' => $structuredData,
        ]);
    }

    /**
     * Просмотр инструкции
     */
    public function guideShow(string $slug): View
    {
        // Сначала проверяем без published, чтобы понять, существует ли статья
        $article = Article::guide()
            ->where('slug', $slug)
            ->first();
        
        if (!$article) {
            abort(404, 'Инструкция не найдена');
        }
        
        // Проверяем статус публикации
        if ($article->status !== 'published') {
            abort(404, 'Инструкция не опубликована (статус: ' . $article->status . ')');
        }

        // SEO для статьи
        $seo = SeoHelper::get('guide', $article->id, [
            'title' => $article->title,
            'slug' => $article->slug,
            'article' => $article, // Для article мета-тегов
        ]);

        // Статистика проекта (с кэшированием на 15 минут)
        $globalStats = \App\Helpers\StatisticsHelper::getGlobalStatistics();

        // Статистика пользователя (только для авторизованных)
        $userStats = null;
        $user = auth()->user();
        if ($user) {
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
        }

        // Структурированные данные для SEO
        $structuredData = [];
        
        // FAQPage для инструкций (guide)
        if ($article->type === 'guide') {
            $faqData = SeoHelper::getStructuredDataForFAQPage($article, $seo);
            if ($faqData) {
                $structuredData[] = $faqData;
            }
        } else {
            // Article для обычных статей
            $structuredData[] = SeoHelper::getStructuredDataForArticle($article, $seo);
        }
        
        // Organization на всех страницах
        $structuredData[] = SeoHelper::getStructuredDataForOrganization();

        // Breadcrumbs
        $breadcrumbs = SeoHelper::getBreadcrumbsForGuide($article);

        return view('articles.show', [
            'article' => $article,
            'seo' => $seo,
            'globalStats' => $globalStats,
            'userStats' => $userStats,
            'structuredData' => $structuredData,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }

    /**
     * Просмотр страницы группы сущностей (символа) по slug.
     */
    public function symbolShow(string $slug): View
    {
        $article = Article::with('entityGroup.mappings')
            ->where('type', 'entity_group')
            ->where('slug', $slug)
            ->first();

        if (!$article) {
            abort(404, 'Страница не найдена');
        }

        if ($article->status !== 'published') {
            abort(404, 'Страница не опубликована');
        }

        $seo = SeoHelper::get('entity_group', $article->id, [
            'title' => $article->title,
            'slug' => $article->slug,
            'article' => $article,
        ]);

        $globalStats = \App\Helpers\StatisticsHelper::getGlobalStatistics();
        $userStats = null;
        $user = auth()->user();
        if ($user) {
            $userReportsCount = $user->reports()->count();
            $userDreamsCount = $user->reports()->withCount('dreams')->get()->sum('dreams_count');
            $allFriendships = \App\Models\Friendship::where(function ($q) use ($user) {
                $q->where('user_id', $user->id)->where('status', 'accepted');
            })->orWhere(function ($q) use ($user) {
                $q->where('friend_id', $user->id)->where('status', 'accepted');
            })->get();
            $allFriendIds = $allFriendships->map(fn ($f) => $f->user_id === $user->id ? $f->friend_id : $f->user_id)->toArray();
            $friendsCount = count($allFriendIds);
            $firstReport = $user->reports()->orderBy('report_date')->first();
            $monthsDiff = $firstReport ? $firstReport->report_date->diffInMonths(now()) : 0;
            $avgDreamsPerMonth = $monthsDiff > 0 && $userDreamsCount ? round($userDreamsCount / max($monthsDiff, 1), 1) : $userDreamsCount;
            $userStats = [
                'reports' => $userReportsCount,
                'dreams' => $userDreamsCount,
                'friends' => $friendsCount,
                'avg_per_month' => $avgDreamsPerMonth,
            ];
        }

        $structuredData = [
            SeoHelper::getStructuredDataForArticle($article, $seo),
            SeoHelper::getStructuredDataForOrganization()
        ];
        $breadcrumbs = SeoHelper::getBreadcrumbsForSymbol($article);

        // Примеры толкований с символом этой группы: только толкования, в которых есть
        // хотя бы одна сущность (символ/локация/тег) из маппингов группы. 10 кандидатов → до 6 без дубликатов заголовка.
        $exampleInterpretations = collect();
        $entityGroup = $article->entityGroup;
        if ($entityGroup) {
            $slugs = $entityGroup->mappings->pluck('entity_slug')->unique()->filter()->values()->toArray();
            if (!empty($slugs)) {
                $interpretationIds = DreamInterpretationEntity::whereIn('slug', $slugs)
                    ->distinct()
                    ->pluck('dream_interpretation_id');
                if ($interpretationIds->isNotEmpty()) {
                    $candidates = DreamInterpretation::whereIn('id', $interpretationIds)
                        ->where('processing_status', 'completed')
                        ->orderByDesc('created_at')
                        ->limit(10)
                        ->get(['id', 'hash', 'dream_description', 'created_at']);
                    $seenTitles = [];
                    foreach ($candidates as $interp) {
                        $title = $interp->dream_description
                            ? \Illuminate\Support\Str::limit(strip_tags((string) $interp->dream_description), 80)
                            : ('Толкование от ' . ($interp->created_at?->format('d.m.Y') ?? '—'));
                        if (!isset($seenTitles[$title])) {
                            $seenTitles[$title] = true;
                            $exampleInterpretations->push($interp);
                            if ($exampleInterpretations->count() >= 6) {
                                break;
                            }
                        }
                    }
                }
            }
        }

        return view('articles.show', [
            'article' => $article,
            'seo' => $seo,
            'globalStats' => $globalStats,
            'userStats' => $userStats,
            'structuredData' => $structuredData,
            'breadcrumbs' => $breadcrumbs,
            'exampleInterpretations' => $exampleInterpretations,
        ]);
    }

    /**
     * Просмотр статьи
     */
    public function articleShow(string $slug): View
    {
        // Сначала проверяем без published, чтобы понять, существует ли статья
        $article = Article::article()
            ->where('slug', $slug)
            ->first();
        
        if (!$article) {
            abort(404, 'Статья не найдена');
        }
        
        // Проверяем статус публикации
        if ($article->status !== 'published') {
            abort(404, 'Статья не опубликована (статус: ' . $article->status . ')');
        }

        // SEO для статьи
        $seo = SeoHelper::get('article', $article->id, [
            'title' => $article->title,
            'slug' => $article->slug,
            'article' => $article, // Для article мета-тегов
        ]);

        // Статистика проекта (с кэшированием на 15 минут)
        $globalStats = \App\Helpers\StatisticsHelper::getGlobalStatistics();

        // Статистика пользователя (только для авторизованных)
        $userStats = null;
        $user = auth()->user();
        if ($user) {
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
        }

        // Структурированные данные для SEO (Article + Organization)
        $structuredData = [
            SeoHelper::getStructuredDataForArticle($article, $seo),
            SeoHelper::getStructuredDataForOrganization()
        ];

        // Breadcrumbs
        $breadcrumbs = SeoHelper::getBreadcrumbsForArticle($article);

        return view('articles.show', [
            'article' => $article,
            'seo' => $seo,
            'globalStats' => $globalStats,
            'userStats' => $userStats,
            'structuredData' => $structuredData,
            'breadcrumbs' => $breadcrumbs,
        ]);
    }
}
