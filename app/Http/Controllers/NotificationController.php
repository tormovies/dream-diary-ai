<?php

namespace App\Http\Controllers;

use App\Helpers\SeoHelper;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * Список уведомлений (только непрочитанные)
     */
    public function index(): View
    {
        $user = auth()->user();
        
        // Показываем только непрочитанные уведомления
        $notifications = $user->notifications()
            ->whereNull('read_at')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        // Статистика пользователя
        $stats = [
            'reports_count' => $user->reports()->count(),
            'dreams_count' => $user->reports()->withCount('dreams')->get()->sum('dreams_count'),
            'friends_count' => \App\Models\Friendship::where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->where('status', 'accepted');
            })->orWhere(function ($query) use ($user) {
                $query->where('friend_id', $user->id)
                    ->where('status', 'accepted');
            })->count(),
            'friendship_requests_count' => \App\Models\Friendship::where('friend_id', $user->id)
                ->where('status', 'pending')
                ->count(),
            'tags_count' => \App\Models\Tag::whereHas('reports', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->distinct()->count(),
            'comments_count' => \App\Models\Comment::whereHas('report', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->count(),
        ];
        
        // Среднее количество снов в месяц
        $firstReport = $user->reports()->orderBy('created_at')->first();
        if ($firstReport) {
            $monthsDiff = $firstReport->created_at->diffInMonths(now());
            $stats['avg_dreams_per_month'] = $monthsDiff > 0 ? round($stats['dreams_count'] / max($monthsDiff, 1), 1) : $stats['dreams_count'];
        } else {
            $stats['avg_dreams_per_month'] = 0;
        }
        
        // Количество непрочитанных уведомлений
        $stats['unread_notifications_count'] = $notifications->count();
        
        // SEO данные
        $seo = SeoHelper::get('notifications');

        return view('notifications.index', compact('notifications', 'stats', 'seo'));
    }

    /**
     * Пометить уведомление как прочитанное
     */
    public function markAsRead(Notification $notification): RedirectResponse
    {
        if ($notification->user_id !== auth()->id()) {
            abort(403);
        }

        $notification->markAsRead();

        return back();
    }

    /**
     * Пометить все уведомления как прочитанные
     */
    public function markAllAsRead(): RedirectResponse
    {
        auth()->user()->notifications()
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return back()->with('success', 'Все уведомления отмечены как прочитанные');
    }

    /**
     * Получить количество непрочитанных уведомлений (AJAX)
     */
    public function unreadCount(): JsonResponse
    {
        $count = auth()->user()->notifications()
            ->whereNull('read_at')
            ->count();

        return response()->json(['count' => $count]);
    }
}
