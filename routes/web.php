<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [\App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Публичные дневники (без авторизации)
Route::get('/diary/{publicLink}', [\App\Http\Controllers\DiaryController::class, 'public'])->name('diary.public');

// 301 редирект со старого формата /diaries/{id} на новый /diary/{public_link}
Route::get('/diaries/{id}', function ($id) {
    $user = \App\Models\User::find($id);
    if (!$user || !$user->public_link) {
        abort(404);
    }
    return redirect()->route('diary.public', ['publicLink' => $user->public_link], 301);
})->where('id', '[0-9]+');

// Публичные профили
Route::get('/users/{user}', [\App\Http\Controllers\UserController::class, 'profile'])->name('users.profile');

// Лента активности (доступна всем)
Route::get('/activity', [\App\Http\Controllers\ActivityController::class, 'index'])->name('activity.index');

// Анализатор снов (доступен всем)
Route::get('/tolkovanie-snov', [\App\Http\Controllers\DreamAnalyzerController::class, 'create'])->name('dream-analyzer.create');
Route::post('/tolkovanie-snov', [\App\Http\Controllers\DreamAnalyzerController::class, 'store'])->name('dream-analyzer.store');
Route::post('/tolkovanie-snov/{hash}/process', [\App\Http\Controllers\DreamAnalyzerController::class, 'processAnalysis'])->name('dream-analyzer.process');
Route::get('/tolkovanie-snov/{hash}', [\App\Http\Controllers\DreamAnalyzerController::class, 'show'])->name('dream-analyzer.show');

// Поиск отчетов (доступен всем)
Route::get('/search', [\App\Http\Controllers\ReportController::class, 'search'])->name('reports.search');

// Просмотр анализа отчета (доступен всем, проверка прав через Policy)
Route::get('/reports/{report}/analysis', [\App\Http\Controllers\ReportController::class, 'showAnalysis'])->name('reports.analysis');

Route::get('/dashboard', [\App\Http\Controllers\ReportController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Отчеты (кроме show, который публичный)
    Route::resource('reports', \App\Http\Controllers\ReportController::class)->except(['index', 'show']); // index теперь на dashboard
    Route::post('/reports/{report}/publish', [\App\Http\Controllers\ReportController::class, 'publish'])->name('reports.publish');
    Route::post('/reports/{report}/unpublish', [\App\Http\Controllers\ReportController::class, 'unpublish'])->name('reports.unpublish');
    
    // Анализ отчётов (создание и управление - только для авторизованных)
    Route::post('/reports/{report}/analyze', [\App\Http\Controllers\ReportController::class, 'analyze'])->name('reports.analyze');
    Route::post('/reports/{report}/analysis/process', [\App\Http\Controllers\ReportController::class, 'processAnalysis'])->name('reports.analysis.process');
    Route::delete('/reports/{report}/analysis', [\App\Http\Controllers\ReportController::class, 'detachAnalysis'])->name('reports.analysis.detach');
    
    // Редирект со старой страницы отчетов на dashboard
    Route::get('/reports', function () {
        return redirect()->route('dashboard');
    })->name('reports.index');
    
    // Теги (автодополнение)
    Route::get('/tags/autocomplete', [\App\Http\Controllers\TagController::class, 'autocomplete'])->name('tags.autocomplete');
    
    // Друзья
    Route::post('/friends', [\App\Http\Controllers\FriendshipController::class, 'store'])->name('friends.store');
    Route::post('/friends/{friendship}/accept', [\App\Http\Controllers\FriendshipController::class, 'accept'])->name('friends.accept');
    Route::post('/friends/{friendship}/reject', [\App\Http\Controllers\FriendshipController::class, 'reject'])->name('friends.reject');
    Route::delete('/friends/{friendship}', [\App\Http\Controllers\FriendshipController::class, 'destroy'])->name('friends.destroy');
    
    // Дневники
    Route::get('/diary/user/{user}', [\App\Http\Controllers\DiaryController::class, 'show'])->name('diary.show');
    
    // Комментарии
    Route::post('/reports/{report}/comments', [\App\Http\Controllers\CommentController::class, 'store'])->name('comments.store');
    Route::delete('/comments/{comment}', [\App\Http\Controllers\CommentController::class, 'destroy'])->name('comments.destroy');
    
    // Поиск пользователей
    Route::get('/users', [\App\Http\Controllers\UserController::class, 'search'])->name('users.search');
    
    // Статистика
    Route::get('/statistics', [\App\Http\Controllers\StatisticsController::class, 'index'])->name('statistics.index');
    
    // Переключение темы
    Route::post('/theme/toggle', function (\Illuminate\Http\Request $request) {
        $user = auth()->user();
        if ($user) {
            $user->theme = $request->theme;
            $user->save();
        }
        return response()->json(['success' => true]);
    })->name('theme.toggle');
    
    // Уведомления
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::get('/notifications/unread-count', [\App\Http\Controllers\NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
    
    // Админ-панель
    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/users', [\App\Http\Controllers\AdminController::class, 'users'])->name('users');
        Route::get('/users/{user}/edit', [\App\Http\Controllers\AdminController::class, 'editUser'])->name('users.edit');
        Route::patch('/users/{user}', [\App\Http\Controllers\AdminController::class, 'updateUser'])->name('users.update');
        Route::post('/users/{user}/ban', [\App\Http\Controllers\AdminController::class, 'banUser'])->name('users.ban');
        Route::post('/users/{user}/unban', [\App\Http\Controllers\AdminController::class, 'unbanUser'])->name('users.unban');
        Route::delete('/users/{user}', [\App\Http\Controllers\AdminController::class, 'deleteUser'])->name('users.delete');
        Route::get('/reports', [\App\Http\Controllers\AdminController::class, 'reports'])->name('reports');
        Route::get('/comments', [\App\Http\Controllers\AdminController::class, 'comments'])->name('comments');
        Route::delete('/comments/{comment}', [\App\Http\Controllers\AdminController::class, 'deleteComment'])->name('comments.destroy');
        Route::get('/interpretations', [\App\Http\Controllers\AdminController::class, 'interpretations'])->name('interpretations');
        Route::delete('/interpretations/{interpretation}', [\App\Http\Controllers\AdminController::class, 'deleteInterpretation'])->name('interpretations.delete');
        Route::get('/settings', [\App\Http\Controllers\AdminController::class, 'settings'])->name('settings');
        Route::patch('/settings', [\App\Http\Controllers\AdminController::class, 'updateSettings'])->name('settings.update');
        
        // SEO управление
        Route::resource('seo', \App\Http\Controllers\Admin\SeoController::class)->except(['show']);
    });
});

// Публичный просмотр отчетов (доступен всем) - должен быть после resource маршрутов
Route::get('/reports/{report}', [\App\Http\Controllers\ReportController::class, 'show'])->name('reports.show');

// Редиректы со старых URL дневника Tor
if (file_exists(__DIR__.'/redirects.php')) {
    require __DIR__.'/redirects.php';
}

require __DIR__.'/auth.php';
