<?php

namespace Database\Seeders;

use App\Models\Dream;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestUsersAndReportsSeeder extends Seeder
{
    /**
     * Два тестовых пользователя и по два опубликованных отчёта с снами (идемпотентно по email).
     */
    public function run(): void
    {
        $seed = [
            [
                'email' => 'testuser_alpha@local.test',
                'name' => 'Тест Альфа',
                'nickname' => 'test_alpha',
                'reports' => [
                    [
                        'report_date' => now()->subDays(3)->toDateString(),
                        'dream_title' => 'Полёт над городом',
                        'dream_text' => 'Летал над ночным городом, внизу огни. Тестовые данные для разработки.',
                    ],
                    [
                        'report_date' => now()->subDays(1)->toDateString(),
                        'dream_title' => 'Вода и мост',
                        'dream_text' => 'Переплывал широкую реку, впереди каменный мост. Тестовые данные.',
                    ],
                ],
            ],
            [
                'email' => 'testuser_beta@local.test',
                'name' => 'Тест Бета',
                'nickname' => 'test_beta',
                'reports' => [
                    [
                        'report_date' => now()->subDays(5)->toDateString(),
                        'dream_title' => 'Дом детства',
                        'dream_text' => 'Оказался в знакомом дворе, снег тает. Тестовые данные для разработки.',
                    ],
                    [
                        'report_date' => now()->subDays(2)->toDateString(),
                        'dream_title' => 'Разговор с незнакомцем',
                        'dream_text' => 'Незнакомец что-то объяснял на улице. Тестовые данные.',
                    ],
                ],
            ],
        ];

        foreach ($seed as $row) {
            $user = User::updateOrCreate(
                ['email' => $row['email']],
                [
                    'name' => $row['name'],
                    'nickname' => $row['nickname'],
                    'password' => Hash::make('password'),
                    'role' => 'user',
                    'diary_privacy' => 'public',
                    'comment_privacy' => 'all',
                    'email_verified_at' => now(),
                    'is_banned' => false,
                ]
            );

            foreach ($row['reports'] as $idx => $rep) {
                $exists = Report::query()
                    ->where('user_id', $user->id)
                    ->whereDate('report_date', $rep['report_date'])
                    ->exists();

                if ($exists) {
                    continue;
                }

                $report = Report::query()->create([
                    'user_id' => $user->id,
                    'report_date' => $rep['report_date'],
                    'access_level' => 'all',
                    'status' => 'published',
                ]);

                Dream::query()->create([
                    'report_id' => $report->id,
                    'title' => $rep['dream_title'],
                    'description' => $rep['dream_text'],
                    'dream_type' => 'Яркий сон',
                    'order' => 0,
                ]);
            }
        }
    }
}
