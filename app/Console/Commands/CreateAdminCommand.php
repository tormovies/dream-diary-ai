<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateAdminCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:create {--email=} {--name=} {--nickname=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Создать администратора';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->option('email') ?: $this->ask('Email администратора');
        $name = $this->option('name') ?: $this->ask('Имя администратора');
        $nickname = $this->option('nickname') ?: $this->ask('Никнейм администратора');
        $password = $this->secret('Пароль (оставьте пустым для генерации)');

        if (empty($password)) {
            $password = Str::random(12);
            $this->info("Сгенерирован пароль: {$password}");
        }

        if (User::where('email', $email)->exists()) {
            $this->error("Пользователь с email {$email} уже существует!");
            return 1;
        }

        if (User::where('nickname', $nickname)->exists()) {
            $this->error("Пользователь с никнеймом {$nickname} уже существует!");
            return 1;
        }

        $user = User::create([
            'name' => $name,
            'nickname' => $nickname,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => 'admin',
            'email_verified_at' => now(),
            'diary_privacy' => 'private',
            // public_link будет автоматически установлен через boot метод модели
            'theme' => 'light',
        ]);

        $this->info("✅ Администратор успешно создан!");
        $this->table(
            ['Поле', 'Значение'],
            [
                ['ID', $user->id],
                ['Имя', $user->name],
                ['Никнейм', $user->nickname],
                ['Email', $user->email],
                ['Роль', $user->role],
                ['Пароль', $password],
            ]
        );

        return 0;
    }
}
