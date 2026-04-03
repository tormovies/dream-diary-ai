<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestUsersSeeder extends Seeder
{
    /**
     * Два тестовых пользователя с подтверждённым email (для локальной разработки).
     */
    public function run(): void
    {
        $password = Hash::make('password');

        User::query()->updateOrCreate(
            ['email' => 'test1@snovidec.test'],
            [
                'name' => 'Тест Первый',
                'nickname' => 'snovdev1',
                'password' => $password,
                'email_verified_at' => now(),
                'role' => 'user',
                'diary_privacy' => 'public',
                'comment_privacy' => 'all',
                'theme' => 'light',
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'test2@snovidec.test'],
            [
                'name' => 'Тест Второй',
                'nickname' => 'snovdev2',
                'password' => $password,
                'email_verified_at' => now(),
                'role' => 'user',
                'diary_privacy' => 'public',
                'comment_privacy' => 'all',
                'theme' => 'light',
            ]
        );
    }
}
