<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        
        // Статистика проекта (с кэшированием на 15 минут)
        $globalStats = \App\Helpers\StatisticsHelper::getGlobalStatistics(true);
        
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
        $friendsOnline = collect();
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

        // Популярные теги (топ 6)
        $popularTags = \App\Models\Tag::withCount('reports')
            ->orderByDesc('reports_count')
            ->limit(6)
            ->get();

        // Сонник (статичные данные)
        $dreamDictionary = [
            ['symbol' => 'Летать', 'meaning' => 'Символизирует свободу, стремление к независимости, преодоление препятствий. Часто снится в периоды важных жизненных изменений.'],
            ['symbol' => 'Вода', 'meaning' => 'Олицетворяет эмоции, подсознание, очищение и перерождение. Чистая вода — к душевному покою, мутная — к внутренним конфликтам.'],
            ['symbol' => 'Дом', 'meaning' => 'Отражение вашего внутреннего мира. Исследование дома во сне означает самопознание и внутренний рост.'],
            ['symbol' => 'Потеряться', 'meaning' => 'Указывает на чувство растерянности в реальной жизни, поиск своего пути или необходимость принятия важного решения.'],
        ];

        return view('profile.edit', [
            'user' => $user,
            'globalStats' => $globalStats,
            'userStats' => $userStats,
            'friendsOnline' => $friendsOnline,
            'popularTags' => $popularTags,
            'dreamDictionary' => $dreamDictionary,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        // public_link будет автоматически обновлен через boot метод модели при изменении nickname

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
