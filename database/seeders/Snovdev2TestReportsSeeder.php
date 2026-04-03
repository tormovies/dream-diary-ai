<?php

namespace Database\Seeders;

use App\Models\Dream;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Seeder;

class Snovdev2TestReportsSeeder extends Seeder
{
    /**
     * Два тестовых отчёта для пользователя snovdev2 (локальная разработка / тесты).
     */
    public function run(): void
    {
        $user = User::query()->where('nickname', 'snovdev2')->first();

        if ($user === null) {
            $this->command?->warn('Пользователь snovdev2 не найден. Сначала: php artisan db:seed --class=TestUsersSeeder');

            return;
        }

        $reportsData = [
            [
                'report_date' => now()->subDays(3)->toDateString(),
                'user_context' => 'Тестовый контекст: спокойная неделя.',
                'dreams' => [
                    [
                        'title' => 'Полёт над городом',
                        'description' => 'Видел себя парящим над ночными огнями, ветер в лицо, чувство лёгкости.',
                        'dream_type' => 'Осознанное сновидение',
                        'order' => 0,
                    ],
                    [
                        'title' => 'Старый дом',
                        'description' => 'Заходил в знакомый подъезд, но квартира была пустая.',
                        'dream_type' => 'Яркий сон',
                        'order' => 1,
                    ],
                ],
            ],
            [
                'report_date' => now()->subDay()->toDateString(),
                'user_context' => 'Второй тестовый отчёт.',
                'dreams' => [
                    [
                        'title' => 'Вода и мост',
                        'description' => 'Переправлялся по узкому мосту, внизу быстрая река.',
                        'dream_type' => 'Бледный сон',
                        'order' => 0,
                    ],
                ],
            ],
        ];

        foreach ($reportsData as $block) {
            $dreams = $block['dreams'];
            unset($block['dreams']);

            $report = Report::query()->create(array_merge([
                'user_id' => $user->id,
                'access_level' => 'all',
                'status' => 'published',
                'current_context' => null,
            ], $block));

            foreach ($dreams as $dream) {
                Dream::query()->create(array_merge($dream, [
                    'report_id' => $report->id,
                ]));
            }
        }

        $this->command?->info('Добавлено 2 отчёта с снами для snovdev2.');
    }
}
