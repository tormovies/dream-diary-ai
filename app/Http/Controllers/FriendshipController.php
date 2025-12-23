<?php

namespace App\Http\Controllers;

use App\Models\Friendship;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class FriendshipController extends Controller
{
    /**
     * Отправить запрос в друзья
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'friend_id' => ['required', 'exists:users,id'],
        ]);

        $user = auth()->user();
        $friendId = $request->friend_id;

        // Проверка на спам (не более 10 запросов в день)
        $todayRequests = Friendship::where('user_id', $user->id)
            ->where('created_at', '>=', now()->startOfDay())
            ->count();

        if ($todayRequests >= 10) {
            return back()->with('error', 'Превышен лимит запросов в друзья на сегодня. Попробуйте завтра.');
        }

        // Проверяем, не существует ли уже запрос
        $existingFriendship = Friendship::where(function ($query) use ($user, $friendId) {
            $query->where('user_id', $user->id)
                  ->where('friend_id', $friendId);
        })->orWhere(function ($query) use ($user, $friendId) {
            $query->where('user_id', $friendId)
                  ->where('friend_id', $user->id);
        })->first();

        if ($existingFriendship) {
            return back()->with('error', 'Запрос в друзья уже существует.');
        }

        // Создаем запрос
        $friendship = Friendship::create([
            'user_id' => $user->id,
            'friend_id' => $friendId,
            'status' => 'pending',
        ]);

        // Создаем уведомление
        Notification::create([
            'user_id' => $friendId,
            'type' => 'friendship_request',
            'data' => [
                'friendship_id' => $friendship->id,
                'from_user_id' => $user->id,
                'from_user_nickname' => $user->nickname,
            ],
        ]);

        return back()->with('success', 'Запрос в друзья отправлен.');
    }

    /**
     * Принять запрос в друзья
     */
    public function accept(Friendship $friendship): RedirectResponse
    {
        if ($friendship->friend_id !== auth()->id() || $friendship->status !== 'pending') {
            abort(403);
        }

        $friendship->update(['status' => 'accepted']);

        // Создаем уведомление для отправителя
        Notification::create([
            'user_id' => $friendship->user_id,
            'type' => 'friendship_accepted',
            'data' => [
                'friendship_id' => $friendship->id,
                'from_user_id' => auth()->id(),
                'from_user_nickname' => auth()->user()->nickname,
            ],
        ]);

        return back()->with('success', 'Запрос в друзья принят.');
    }

    /**
     * Отклонить запрос в друзья
     */
    public function reject(Friendship $friendship): RedirectResponse
    {
        if ($friendship->friend_id !== auth()->id() || $friendship->status !== 'pending') {
            abort(403);
        }

        $friendship->delete();

        return back()->with('success', 'Запрос в друзья отклонен.');
    }

    /**
     * Удалить из друзей
     */
    public function destroy(Friendship $friendship): RedirectResponse
    {
        $user = auth()->user();

        if ($friendship->user_id !== $user->id && $friendship->friend_id !== $user->id) {
            abort(403);
        }

        $friendship->delete();

        return back()->with('success', 'Пользователь удален из друзей.');
    }
}
