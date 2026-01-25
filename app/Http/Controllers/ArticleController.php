<?php

namespace App\Http\Controllers;

use App\Models\Article;
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

        return view('articles.guide.index', [
            'articles' => $articles,
            'seo' => $seo,
            'globalStats' => $globalStats,
            'userStats' => $userStats,
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

        return view('articles.articles.index', [
            'articles' => $articles,
            'seo' => $seo,
            'globalStats' => $globalStats,
            'userStats' => $userStats,
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

        return view('articles.show', [
            'article' => $article,
            'seo' => $seo,
            'globalStats' => $globalStats,
            'userStats' => $userStats,
            'structuredData' => $structuredData,
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

        return view('articles.show', [
            'article' => $article,
            'seo' => $seo,
            'globalStats' => $globalStats,
            'userStats' => $userStats,
            'structuredData' => $structuredData,
        ]);
    }
}
