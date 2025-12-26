<?php

namespace App\Policies;

use App\Models\Report;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ReportPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     * 
     * Иерархия приватности:
     * 1. diary_privacy владельца (главное)
     *    - private: только владелец и админ
     *    - friends: только друзья владельца (игнорируем access_level)
     *    - public: смотрим access_level
     * 2. status отчета (published/draft)
     * 3. access_level отчета (только если дневник public)
     */
    public function view(?User $user, Report $report): bool
    {
        // Загружаем владельца отчета
        $owner = $report->user;
        
        // Админ может видеть все
        if ($user && $user->isAdmin()) {
            return true;
        }
        
        // Владелец всегда может видеть свой отчет
        if ($user && $report->user_id === $user->id) {
            return true;
        }
        
        // Если отчет не опубликован, его могут видеть только владелец и админ
        if ($report->status !== 'published') {
            return false;
        }
        
        // УРОВЕНЬ 1: Проверяем diary_privacy владельца (ГЛАВНОЕ!)
        switch ($owner->diary_privacy) {
            case 'private':
                // Приватный дневник - только владелец и админ (уже проверено выше)
                return false;
                
            case 'friends':
                // Дневник для друзей - только друзья владельца (игнорируем access_level отчета)
                if (!$user) {
                    return false;
                }
                
                // Проверяем дружбу с владельцем дневника
                return \App\Models\Friendship::where(function ($query) use ($user, $owner) {
                    $query->where('user_id', $user->id)
                        ->where('friend_id', $owner->id)
                        ->where('status', 'accepted');
                })->orWhere(function ($query) use ($user, $owner) {
                    $query->where('user_id', $owner->id)
                        ->where('friend_id', $user->id)
                        ->where('status', 'accepted');
                })->exists();
                
            case 'public':
                // УРОВЕНЬ 2: Дневник публичный - проверяем access_level отчета
                
                if ($report->access_level === 'none') {
                    // Никому (кроме владельца и админа, которые уже проверены)
                    return false;
                }
                
                if ($report->access_level === 'friends') {
                    // Только друзьям
                    if (!$user) {
                        return false;
                    }
                    
                    // Проверяем дружбу с владельцем отчета
                    return \App\Models\Friendship::where(function ($query) use ($user, $owner) {
                        $query->where('user_id', $user->id)
                            ->where('friend_id', $owner->id)
                            ->where('status', 'accepted');
                    })->orWhere(function ($query) use ($user, $owner) {
                        $query->where('user_id', $owner->id)
                            ->where('friend_id', $user->id)
                            ->where('status', 'accepted');
                    })->exists();
                }
                
                // access_level === 'all' - доступен всем
                return true;
                
            default:
                // Неизвестное значение diary_privacy - запрещаем доступ
                return false;
        }
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Report $report): bool
    {
        // Админ может редактировать все
        if ($user->isAdmin()) {
            return true;
        }

        // Владелец может редактировать свой отчет
        if ($report->user_id === $user->id) {
            // Проверяем настройки админа (если есть ограничение по дням)
            $daysLimit = \App\Models\Setting::getValue('edit_dreams_after_days', null);
            if ($daysLimit !== null) {
                $daysSinceCreation = now()->diffInDays($report->created_at);
                return $daysSinceCreation <= (int)$daysLimit;
            }
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Report $report): bool
    {
        // Админ может удалять все
        if ($user->isAdmin()) {
            return true;
        }

        // Владелец может удалять свой отчет
        if ($report->user_id === $user->id) {
            // Проверяем настройки админа (если удаление запрещено)
            $allowDeletion = \App\Models\Setting::getValue('allow_report_deletion', true);
            return filter_var($allowDeletion, FILTER_VALIDATE_BOOLEAN);
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Report $report): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Report $report): bool
    {
        return false;
    }
}
