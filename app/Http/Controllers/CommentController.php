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

        // Создаем уведомления
        $this->createNotifications($comment, $report, $request->parent_id);

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

        // Удаляем связанные уведомления
        $this->deleteNotifications($comment);

        // Удаляем все дочерние комментарии
        $this->deleteReplies($comment);

        $comment->delete();

        return back()->with('success', 'Комментарий удален');
    }
    
    /**
     * Создание уведомлений для комментария
     */
    private function createNotifications(Comment $comment, Report $report, $parentId): void
    {
        $currentUserId = auth()->id();
        $currentUserNickname = auth()->user()->nickname;
        
        if (!$parentId) {
            // Комментарий первого уровня - уведомляем владельца отчета
            if ($report->user_id !== $currentUserId) {
                // Проверяем, нет ли уже непрочитанного уведомления для этого отчета
                $existingNotification = Notification::where('user_id', $report->user_id)
                    ->where('type', 'comment')
                    ->whereNull('read_at')
                    ->whereJsonContains('data->report_id', $report->id)
                    ->first();
                
                if (!$existingNotification) {
                    Notification::create([
                        'user_id' => $report->user_id,
                        'type' => 'comment',
                        'data' => [
                            'comment_id' => $comment->id,
                            'report_id' => $report->id,
                            'report_date' => $report->report_date->format('d.m.Y'),
                            'from_user_id' => $currentUserId,
                            'from_user_nickname' => $currentUserNickname,
                        ],
                    ]);
                }
            }
        } else {
            // Ответ на комментарий
            $parentComment = Comment::find($parentId);
            
            if ($parentComment) {
                // 1. Уведомляем владельца отчета (если это не текущий пользователь)
                if ($report->user_id !== $currentUserId) {
                    $existingNotification = Notification::where('user_id', $report->user_id)
                        ->where('type', 'comment')
                        ->whereNull('read_at')
                        ->whereJsonContains('data->report_id', $report->id)
                        ->first();
                    
                    if (!$existingNotification) {
                        Notification::create([
                            'user_id' => $report->user_id,
                            'type' => 'comment',
                            'data' => [
                                'comment_id' => $comment->id,
                                'report_id' => $report->id,
                                'report_date' => $report->report_date->format('d.m.Y'),
                                'from_user_id' => $currentUserId,
                                'from_user_nickname' => $currentUserNickname,
                                'is_reply' => false,
                            ],
                        ]);
                    }
                }
                
                // 2. Уведомляем автора родительского комментария (если это не текущий пользователь и не владелец отчета)
                if ($parentComment->user_id !== $currentUserId && $parentComment->user_id !== $report->user_id) {
                    $existingReplyNotification = Notification::where('user_id', $parentComment->user_id)
                        ->where('type', 'comment_reply')
                        ->whereNull('read_at')
                        ->whereJsonContains('data->parent_comment_id', $parentComment->id)
                        ->first();
                    
                    if (!$existingReplyNotification) {
                        // Получаем первые 30 символов родительского комментария
                        $commentPreview = mb_substr($parentComment->content, 0, 30);
                        if (mb_strlen($parentComment->content) > 30) {
                            $commentPreview .= '...';
                        }
                        
                        Notification::create([
                            'user_id' => $parentComment->user_id,
                            'type' => 'comment_reply',
                            'data' => [
                                'comment_id' => $comment->id,
                                'parent_comment_id' => $parentComment->id,
                                'report_id' => $report->id,
                                'report_date' => $report->report_date->format('d.m.Y'),
                                'comment_preview' => $commentPreview,
                                'from_user_id' => $currentUserId,
                                'from_user_nickname' => $currentUserNickname,
                            ],
                        ]);
                    }
                }
            }
        }
    }
    
    /**
     * Удаление уведомлений, связанных с комментарием
     */
    private function deleteNotifications(Comment $comment): void
    {
        // Удаляем уведомления, где этот комментарий является основным
        Notification::where('type', 'comment')
            ->whereJsonContains('data->comment_id', $comment->id)
            ->delete();
        
        Notification::where('type', 'comment_reply')
            ->whereJsonContains('data->comment_id', $comment->id)
            ->delete();
        
        // Удаляем уведомления, где этот комментарий является родительским
        Notification::where('type', 'comment_reply')
            ->whereJsonContains('data->parent_comment_id', $comment->id)
            ->delete();
    }

    /**
     * Проверка, может ли пользователь комментировать отчет
     */
    private function canComment(Report $report): bool
    {
        $user = auth()->user();

        // Владелец отчёта всегда может комментировать свой отчёт
        if ($report->user_id === $user->id) {
            return true;
        }

        // Администратор всегда может комментировать любой отчёт
        if ($user->isAdmin()) {
            return true;
        }

        // Если отчет не опубликован, нельзя комментировать
        if ($report->status !== 'published') {
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
            // Удаляем уведомления для ответа
            $this->deleteNotifications($reply);
            // Рекурсивно удаляем вложенные ответы
            $this->deleteReplies($reply);
            $reply->delete();
        }
    }
}
