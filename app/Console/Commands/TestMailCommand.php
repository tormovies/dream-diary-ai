<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestMailCommand extends Command
{
    protected $signature = 'mail:test {email : Адрес для проверки отправки}';

    protected $description = 'Отправить тестовое письмо для проверки настроек SMTP';

    public function handle(): int
    {
        $email = $this->argument('email');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Укажите корректный email.');
            return self::FAILURE;
        }

        $this->info('Отправка тестового письма на ' . $email . '...');

        try {
            Mail::raw(
                'Это тестовое письмо от ' . config('app.name') . '. Если вы его получили — настройки отправки почты работают.',
                function ($message) use ($email) {
                    $message->to($email)
                        ->subject('Тест отправки почты — ' . config('app.name'));
                }
            );
            $this->info('Письмо отправлено. Проверьте почту (и папку «Спам»).');
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Ошибка: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
