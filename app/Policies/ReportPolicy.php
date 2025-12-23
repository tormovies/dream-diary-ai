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
     */
    public function view(?User $user, Report $report): bool
    {
        // Если отчет не опубликован, его могут видеть только владелец и админ
        if ($report->status !== 'published') {
            // Неавторизованные не могут видеть неопубликованные отчеты
            if (!$user) {
                return false;
            }
            // Админ может видеть все
            if ($user->isAdmin()) {
                return true;
            }
            // Владелец может видеть свой отчет (включая черновики)
            if ($report->user_id === $user->id) {
                return true;
            }
            return false;
        }

        // Проверяем настройки доступа
        if ($report->access_level === 'none') {
            // Неавторизованные не могут видеть отчеты с доступом 'none'
            if (!$user) {
                return false;
            }
            // Админ может видеть все
            if ($user->isAdmin()) {
                return true;
            }
            // Владелец может видеть свой отчет
            if ($report->user_id === $user->id) {
                return true;
            }
            return false;
        }

        if ($report->access_level === 'friends') {
            // Неавторизованные не могут видеть отчеты только для друзей
            if (!$user) {
                return false;
            }
            // Админ может видеть все
            if ($user->isAdmin()) {
                return true;
            }
            // Владелец может видеть свой отчет
            if ($report->user_id === $user->id) {
                return true;
            }
            // Проверяем, являются ли пользователи друзьями
            $friendship = \App\Models\Friendship::where(function ($query) use ($user, $report) {
                $query->where('user_id', $user->id)
                    ->where('friend_id', $report->user_id)
                    ->where('status', 'accepted');
            })->orWhere(function ($query) use ($user, $report) {
                $query->where('user_id', $report->user_id)
                    ->where('friend_id', $user->id)
                    ->where('status', 'accepted');
            })->exists();

            return $friendship;
        }

        // access_level === 'all' - все могут видеть (включая неавторизованных)
        return true;
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
