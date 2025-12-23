<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Models\Comment;
use App\Models\Notification;
use App\Models\Report;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class CommentController extends Controller
{
    /**
     * Сохранить комментарий
     */
    public function store(StoreCommentRequest $request, Report $report): RedirectResponse
    {
        // Проверяем, может ли пользователь комментировать этот отчет
        if (!$this->canComment($report)) {
            abort(403, 'У вас нет доступа для комментирования этого отчета');
        }

        $comment = Comment::create([
            'user_id' => auth()->id(),
            'report_id' => $report->id,
            'parent_id' => $request->parent_id,
            'content' => $request->content,
        ]);

        // Создаем уведомление только для комментариев первого уровня
        if (!$request->parent_id) {
            // Уведомляем автора отчета (если это не он сам)
            if ($report->user_id !== auth()->id()) {
                Notification::create([
                    'user_id' => $report->user_id,
                    'type' => 'comment',
                    'data' => [
                        'comment_id' => $comment->id,
                        'report_id' => $report->id,
                        'from_user_id' => auth()->id(),
                        'from_user_nickname' => auth()->user()->nickname,
                    ],
                ]);
            }
        }

        return back()->with('success', 'Комментарий добавлен');
    }

    /**
     * Удалить комментарий
     */
    public function destroy(Comment $comment): RedirectResponse
    {
        $user = auth()->user();
        $report = $comment->report;

        // Проверяем права на удаление
        if (!$user->isAdmin() && $report->user_id !== $user->id && $comment->user_id !== $user->id) {
            abort(403, 'У вас нет прав для удаления этого комментария');
        }

        // Удаляем все дочерние комментарии
        $this->deleteReplies($comment);

        $comment->delete();

        return back()->with('success', 'Комментарий удален');
    }

    /**
     * Проверка, может ли пользователь комментировать отчет
     */
    private function canComment(Report $report): bool
    {
        $user = auth()->user();

        // Если отчет не опубликован, нельзя комментировать (кроме владельца)
        if ($report->status !== 'published' && $report->user_id !== $user->id) {
            return false;
        }

        // Если отчет недоступен для просмотра, нельзя комментировать
        if ($report->access_level === 'none') {
            return false;
        }

        // Если доступ только для друзей, проверяем дружбу
        if ($report->access_level === 'friends') {
            return \App\Models\Friendship::where(function ($query) use ($user, $report) {
                $query->where('user_id', $user->id)
                    ->where('friend_id', $report->user_id)
                    ->where('status', 'accepted');
            })->orWhere(function ($query) use ($user, $report) {
                $query->where('user_id', $report->user_id)
                    ->where('friend_id', $user->id)
                    ->where('status', 'accepted');
            })->exists();
        }

        // Если дневник публичный или доступ "всем", можно комментировать
        // Но нужно проверить настройки приватности дневника
        $diaryOwner = $report->user;
        
        if ($diaryOwner->diary_privacy === 'private') {
            return false;
        }

        if ($diaryOwner->diary_privacy === 'friends') {
            return \App\Models\Friendship::where(function ($query) use ($user, $diaryOwner) {
                $query->where('user_id', $user->id)
                    ->where('friend_id', $diaryOwner->id)
                    ->where('status', 'accepted');
            })->orWhere(function ($query) use ($user, $diaryOwner) {
                $query->where('user_id', $diaryOwner->id)
                    ->where('friend_id', $user->id)
                    ->where('status', 'accepted');
            })->exists();
        }

        // Публичный дневник - все могут комментировать
        return true;
    }

    /**
     * Рекурсивное удаление дочерних комментариев
     */
    private function deleteReplies(Comment $comment): void
    {
        foreach ($comment->replies as $reply) {
            $this->deleteReplies($reply);
            $reply->delete();
        }
    }
}
