<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Обновляем public_link для всех пользователей на нормализованный nickname
        $users = DB::table('users')->get();
        
        foreach ($users as $user) {
            if (!empty($user->nickname)) {
                $normalized = User::normalizeNickname($user->nickname);
                
                // Проверяем уникальность
                $exists = DB::table('users')
                    ->where('public_link', $normalized)
                    ->where('id', '!=', $user->id)
                    ->exists();
                
                // Если уже существует, добавляем суффикс
                $finalLink = $normalized;
                $counter = 1;
                while ($exists) {
                    $finalLink = $normalized . $counter;
                    $exists = DB::table('users')
                        ->where('public_link', $finalLink)
                        ->where('id', '!=', $user->id)
                        ->exists();
                    $counter++;
                }
                
                DB::table('users')
                    ->where('id', $user->id)
                    ->update(['public_link' => $finalLink]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Возвращаем случайные строки (но это не совсем обратимо)
        // Можно оставить пустым или сгенерировать новые случайные строки
        $users = DB::table('users')->get();
        
        foreach ($users as $user) {
            DB::table('users')
                ->where('id', $user->id)
                ->update(['public_link' => \Illuminate\Support\Str::random(32)]);
        }
    }
};
