<?php

namespace App\Http\Controllers;

use App\Helpers\SeoHelper;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DiaryController extends Controller
{
    /**
     * Публичный просмотр дневника по ссылке (используется нормализованный nickname)
     */
    public function public(string $publicLink): View
    {
        // Ищем по public_link (который теперь равен нормализованному nickname)
        $user = User::where('public_link', $publicLink)->firstOrFail();

        // Получаем только опубликованные отчеты с access_level = 'all'
        $reports = Report::where('user_id', $user->id)
            ->where('status', 'published')
            ->where('access_level', 'all')
            ->with(['dreams', 'tags'])
            ->orderBy('report_date', 'desc')
            ->paginate(20);

        // SEO данные
        $seo = SeoHelper::forDiary($user);

        return view('diary.public', compact('user', 'reports', 'seo'));
    }

    /**
     * Просмотр дневника пользователя (с проверкой прав доступа)
     */
    public function show(User $user): View
    {
        $currentUser = auth()->user();

        // Проверяем доступ к дневнику
        if (!$this->canViewDiary($currentUser, $user)) {
            abort(403, 'У вас нет доступа к этому дневнику');
        }

        // Владелец видит все свои отчеты (включая черновики), другие - только опубликованные
        $reportsQuery = Report::where('user_id', $user->id)
            ->with(['dreams', 'tags']);
        
        // Если это не владелец дневника, показываем только опубликованные
        if ($currentUser->id !== $user->id) {
            $reportsQuery->where('status', 'published');
        }
        
        $reports = $reportsQuery->orderBy('report_date', 'desc')
            ->paginate(20);

        // SEO данные
        $seo = SeoHelper::forDiary($user);

        return view('diary.show', compact('user', 'reports', 'seo'));
    }

    /**
     * Проверка прав доступа к дневнику
     */
    private function canViewDiary($currentUser, $diaryOwner): bool
    {
        // Если не авторизован
        if (!$currentUser) {
            return $diaryOwner->diary_privacy === 'public';
        }

        // Владелец всегда может видеть свой дневник
        if ($currentUser->id === $diaryOwner->id) {
            return true;
        }

        // Админ может видеть все
        if ($currentUser->isAdmin()) {
            return true;
        }

        // Проверяем настройки приватности
        switch ($diaryOwner->diary_privacy) {
            case 'public':
                return true;
            case 'friends':
                // Проверяем, являются ли пользователи друзьями
                return \App\Models\Friendship::where(function ($query) use ($currentUser, $diaryOwner) {
                    $query->where('user_id', $currentUser->id)
                        ->where('friend_id', $diaryOwner->id)
                        ->where('status', 'accepted');
                })->orWhere(function ($query) use ($currentUser, $diaryOwner) {
                    $query->where('user_id', $diaryOwner->id)
                        ->where('friend_id', $currentUser->id)
                        ->where('status', 'accepted');
                })->exists();
            case 'private':
            default:
                return false;
        }
    }
}
