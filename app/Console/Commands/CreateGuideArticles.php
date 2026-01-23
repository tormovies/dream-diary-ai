<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\SeoMeta;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CreateGuideArticles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'guides:create-all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Создать все 10 категорий инструкций (черновики)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Создание категорий инструкций...');

        // Находим первого админа
        $admin = User::where('role', 'admin')->first();
        if (!$admin) {
            $this->error('Администратор не найден! Создайте администратора сначала.');
            return 1;
        }

        $this->info("Используется администратор: {$admin->nickname} (ID: {$admin->id})");

        // Данные для всех категорий
        $categories = $this->getCategoriesData();

        $created = 0;
        $skipped = 0;

        foreach ($categories as $categoryData) {
            // Проверяем, существует ли уже статья с таким slug
            $existing = Article::where('slug', $categoryData['slug'])->first();
            if ($existing) {
                $this->warn("Пропущено: {$categoryData['title']} (уже существует)");
                $skipped++;
                continue;
            }

            // Создаем статью
            $article = Article::create([
                'title' => $categoryData['title'],
                'slug' => $categoryData['slug'],
                'content' => $categoryData['content'],
                'questions_preview' => $categoryData['questions_preview'],
                'type' => 'guide',
                'status' => 'draft', // ЧЕРНОВИК
                'order' => $categoryData['order'],
                'author_id' => $admin->id,
                'image' => null,
                'published_at' => null, // null для черновиков
            ]);

            // Создаем SEO метаданные
            if (!empty($categoryData['seo'])) {
                SeoMeta::create([
                    'page_type' => 'guide',
                    'page_id' => $article->id,
                    'title' => $categoryData['seo']['title'] ?? null,
                    'description' => $categoryData['seo']['description'] ?? null,
                    'h1' => $categoryData['seo']['h1'] ?? null,
                    'h1_description' => $categoryData['seo']['h1_description'] ?? null,
                    'og_title' => $categoryData['seo']['og_title'] ?? $categoryData['seo']['title'] ?? null,
                    'og_description' => $categoryData['seo']['og_description'] ?? $categoryData['seo']['description'] ?? null,
                    'og_image' => null,
                    'is_active' => true,
                    'priority' => 0,
                ]);
            }

            $this->info("✓ Создано: {$categoryData['title']} (ID: {$article->id})");
            $created++;
        }

        $this->newLine();
        $this->info("Готово! Создано: {$created}, Пропущено: {$skipped}");
        $this->info("Все категории созданы как черновики. Проверьте их в админ-панели: /admin/articles");

        return 0;
    }

    /**
     * Получить данные для всех категорий
     */
    private function getCategoriesData(): array
    {
        $telegramLink = 'https://t.me/snovidec_ru';

        return [
            // Категория 1: Начало работы
            [
                'title' => 'Начало работы',
                'slug' => 'nachalo-raboty',
                'order' => 10,
                'questions_preview' => "Что такое Дневник сновидений?\nЧто можно делать без регистрации?\nЗачем регистрироваться? Преимущества аккаунта\nКак зарегистрироваться?\nКак войти в аккаунт?",
                'content' => $this->getCategory1Content(),
                'seo' => [
                    'title' => 'Начало работы — Сновидец.ру',
                    'description' => 'Инструкции по началу работы с платформой Дневник сновидений. Регистрация, вход, возможности для гостей и пользователей.',
                    'h1' => 'Начало работы',
                    'h1_description' => 'Всё, что нужно знать для начала работы с платформой Дневник сновидений',
                    'og_title' => 'Начало работы — Сновидец.ру',
                    'og_description' => 'Инструкции по началу работы с платформой Дневник сновидений',
                ],
            ],

            // Категория 2: Толкование снов
            [
                'title' => 'Толкование снов',
                'slug' => 'tolkovanie-snov',
                'order' => 20,
                'questions_preview' => "Как работает толкование снов?\nМожно ли толковать без регистрации?\nСколько времени занимает анализ?\nЧто делать, если анализ не работает?\nКакие традиции анализа доступны?\nЧто такое контекст и зачем он нужен?\nКак повторить анализ, если произошла ошибка?",
                'content' => $this->getCategory2Content($telegramLink),
                'seo' => [
                    'title' => 'Толкование снов — Сновидец.ру',
                    'description' => 'Как работает толкование снов на платформе. Доступные традиции анализа, контекст, решение проблем и повторный анализ.',
                    'h1' => 'Толкование снов',
                    'h1_description' => 'Всё о функции автоматического толкования снов на платформе',
                    'og_title' => 'Толкование снов — Сновидец.ру',
                    'og_description' => 'Как работает толкование снов на платформе',
                ],
            ],

            // Категория 3: Отчеты и сны
            [
                'title' => 'Отчеты и сны',
                'slug' => 'otchety-i-sny',
                'order' => 30,
                'questions_preview' => "Как создать отчет о сне?\nЧто такое отчет и чем он отличается от сна?\nТипы снов (Яркий, Бледный, ВТО, ОС и т.д.)\nКак редактировать отчет?\nКак удалить отчет?\nНастройки доступа к отчету (все/только друзья/никто)\nЧто такое черновик?",
                'content' => $this->getCategory3Content(),
                'seo' => [
                    'title' => 'Отчеты и сны — Сновидец.ру',
                    'description' => 'Как создавать, редактировать и управлять отчетами о сновидениях. Типы снов, настройки доступа, черновики.',
                    'h1' => 'Отчеты и сны',
                    'h1_description' => 'Работа с отчетами о сновидениях на платформе',
                    'og_title' => 'Отчеты и сны — Сновидец.ру',
                    'og_description' => 'Как создавать и управлять отчетами о сновидениях',
                ],
            ],

            // Категория 4: Анализ отчетов
            [
                'title' => 'Анализ отчетов',
                'slug' => 'analiz-otchetov',
                'order' => 40,
                'questions_preview' => "Как проанализировать свой отчет?\nЧем анализ отчета отличается от толкования?\nСколько времени занимает анализ?\nМожно ли анализировать несколько отчетов вместе?",
                'content' => $this->getCategory4Content(),
                'seo' => [
                    'title' => 'Анализ отчетов — Сновидец.ру',
                    'description' => 'Как анализировать свои отчеты о сновидениях. Отличия от толкования, время анализа, групповой анализ.',
                    'h1' => 'Анализ отчетов',
                    'h1_description' => 'Анализ ваших отчетов о сновидениях',
                    'og_title' => 'Анализ отчетов — Сновидец.ру',
                    'og_description' => 'Как анализировать свои отчеты о сновидениях',
                ],
            ],

            // Категория 5: Дневник и профиль
            [
                'title' => 'Дневник и профиль',
                'slug' => 'dnevnik-i-profil',
                'order' => 50,
                'questions_preview' => "Что такое публичный дневник?\nКак настроить приватность дневника?\nКак получить публичную ссылку на дневник?\nКак изменить профиль?\nКак загрузить аватар?",
                'content' => $this->getCategory5Content(),
                'seo' => [
                    'title' => 'Дневник и профиль — Сновидец.ру',
                    'description' => 'Настройка дневника сновидений и профиля. Приватность, публичные ссылки, редактирование профиля и аватара.',
                    'h1' => 'Дневник и профиль',
                    'h1_description' => 'Управление дневником и настройками профиля',
                    'og_title' => 'Дневник и профиль — Сновидец.ру',
                    'og_description' => 'Настройка дневника сновидений и профиля',
                ],
            ],

            // Категория 6: Друзья и сообщество
            [
                'title' => 'Друзья и сообщество',
                'slug' => 'druzya-i-soobshchestvo',
                'order' => 60,
                'questions_preview' => "Как добавить друга?\nКак принять/отклонить запрос в друзья?\nЧто дает дружба?\nКак посмотреть дневник друга?",
                'content' => $this->getCategory6Content(),
                'seo' => [
                    'title' => 'Друзья и сообщество — Сновидец.ру',
                    'description' => 'Система друзей на платформе. Как добавлять друзей, принимать запросы, просматривать дневники друзей.',
                    'h1' => 'Друзья и сообщество',
                    'h1_description' => 'Социальные функции платформы',
                    'og_title' => 'Друзья и сообщество — Сновидец.ру',
                    'og_description' => 'Система друзей на платформе',
                ],
            ],

            // Категория 7: Комментарии и взаимодействие
            [
                'title' => 'Комментарии и взаимодействие',
                'slug' => 'kommentarii-i-vzaimodeystvie',
                'order' => 70,
                'questions_preview' => "Как оставить комментарий к отчету?\nКак удалить свой комментарий?\nКто может комментировать мои отчеты?",
                'content' => $this->getCategory7Content(),
                'seo' => [
                    'title' => 'Комментарии и взаимодействие — Сновидец.ру',
                    'description' => 'Как комментировать отчеты о сновидениях. Управление комментариями, настройки доступа.',
                    'h1' => 'Комментарии и взаимодействие',
                    'h1_description' => 'Взаимодействие с другими пользователями через комментарии',
                    'og_title' => 'Комментарии и взаимодействие — Сновидец.ру',
                    'og_description' => 'Как комментировать отчеты о сновидениях',
                ],
            ],

            // Категория 8: Поиск и навигация
            [
                'title' => 'Поиск и навигация',
                'slug' => 'poisk-i-navigatsiya',
                'order' => 80,
                'questions_preview' => "Как искать отчеты и сны?\nКак искать пользователей?\nЧто такое теги и как их использовать?",
                'content' => $this->getCategory8Content(),
                'seo' => [
                    'title' => 'Поиск и навигация — Сновидец.ру',
                    'description' => 'Поиск отчетов, снов и пользователей на платформе. Работа с тегами для организации контента.',
                    'h1' => 'Поиск и навигация',
                    'h1_description' => 'Поиск контента и пользователей на платформе',
                    'og_title' => 'Поиск и навигация — Сновидец.ру',
                    'og_description' => 'Поиск отчетов, снов и пользователей',
                ],
            ],

            // Категория 9: Техническая поддержка
            [
                'title' => 'Техническая поддержка',
                'slug' => 'tehnicheskaya-podderzhka',
                'order' => 90,
                'questions_preview' => "Проблемы с загрузкой страницы\nАнализ не работает / ошибка\nНе приходит письмо подтверждения\nКак сменить тему (светлая/темная)?\nКак связаться с поддержкой?",
                'content' => $this->getCategory9Content($telegramLink),
                'seo' => [
                    'title' => 'Техническая поддержка — Сновидец.ру',
                    'description' => 'Решение технических проблем на платформе. Ошибки анализа, проблемы с регистрацией, смена темы, контакты поддержки.',
                    'h1' => 'Техническая поддержка',
                    'h1_description' => 'Решение технических проблем и вопросы поддержки',
                    'og_title' => 'Техническая поддержка — Сновидец.ру',
                    'og_description' => 'Решение технических проблем на платформе',
                ],
            ],

            // Категория 10: Безопасность и приватность
            [
                'title' => 'Безопасность и приватность',
                'slug' => 'bezopasnost-i-privatnost',
                'order' => 100,
                'questions_preview' => "Кто видит мои отчеты?\nКак защитить свои данные?\nЧто делать, если забыл пароль?\nКак удалить аккаунт?",
                'content' => $this->getCategory10Content(),
                'seo' => [
                    'title' => 'Безопасность и приватность — Сновидец.ру',
                    'description' => 'Безопасность данных и настройки приватности на платформе. Восстановление пароля, удаление аккаунта.',
                    'h1' => 'Безопасность и приватность',
                    'h1_description' => 'Защита ваших данных и настройки приватности',
                    'og_title' => 'Безопасность и приватность — Сновидец.ру',
                    'og_description' => 'Безопасность данных и настройки приватности',
                ],
            ],
        ];
    }

    /**
     * Форматирование списка вопросов (содержание)
     */
    private function formatQuestionsList(array $questions): string
    {
        $items = '';
        foreach ($questions as $index => $question) {
            $num = $index + 1;
            $items .= '<li class="mb-2"><a href="#question-' . $num . '" class="block bg-gradient-to-r from-purple-50 to-blue-50 dark:bg-gray-700 hover:from-purple-100 hover:to-blue-100 dark:hover:bg-gray-600 border-l-4 border-purple-500 dark:border-gray-600 px-4 py-3 rounded-r-lg transition-all duration-200 text-purple-700 dark:text-gray-300 hover:text-purple-900 dark:hover:text-gray-100 font-medium"><i class="fas fa-arrow-right mr-2 text-purple-500 dark:text-gray-400"></i>' . htmlspecialchars($question) . '</a></li>';
        }
        
        return '<div class="mb-8 p-6 bg-gradient-to-br from-purple-50 via-blue-50 to-indigo-50 dark:bg-gray-800 rounded-2xl border-2 border-purple-200 dark:border-gray-700 shadow-lg">
            <h2 class="text-2xl font-bold text-purple-700 dark:text-gray-300 mb-4 flex items-center">
                <i class="fas fa-list-ul mr-3 text-purple-500 dark:text-gray-400"></i>Содержание
            </h2>
            <ul class="space-y-2">' . $items . '</ul>
        </div>';
    }

    /**
     * Форматирование заголовка вопроса
     */
    private function formatQuestionHeader(string $id, string $title): string
    {
        return '<div class="question-header pb-4 border-b-2 border-purple-200 dark:border-gray-700">
            <h2 id="' . $id . '" class="text-2xl font-bold text-purple-600 dark:text-purple-300 flex items-center">
                <i class="fas fa-question-circle mr-3 text-purple-500 dark:text-purple-400"></i>' . htmlspecialchars($title) . '
            </h2>
        </div>';
    }

    /**
     * Категория 1: Начало работы
     */
    private function getCategory1Content(): string
    {
        $questions = [
            'Что такое Дневник сновидений?',
            'Что можно делать без регистрации?',
            'Зачем регистрироваться? Преимущества аккаунта',
            'Как зарегистрироваться?',
            'Как войти в аккаунт?'
        ];
        
        return $this->formatQuestionsList($questions) . '

<div class="space-y-8">' . $this->formatQuestionHeader('question-1', 'Что такое Дневник сновидений?') . '
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm">
        <p class="text-gray-700 dark:text-gray-200 mb-4 leading-relaxed">Дневник сновидений — это платформа для записи, анализа и исследования ваших снов. Здесь вы можете:</p>
        <ul class="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-200 ml-4">
            <li>Вести личный дневник сновидений с подробными записями</li>
            <li>Использовать автоматическое толкование снов с помощью искусственного интеллекта</li>
            <li>Анализировать свои отчеты для поиска паттернов и закономерностей</li>
            <li>Общаться с единомышленниками, делиться снами и получать комментарии</li>
            <li>Организовывать записи с помощью тегов</li>
            <li>Настраивать приватность вашего дневника</li>
        </ul>
        <p class="text-gray-700 dark:text-gray-200 mt-4 leading-relaxed">Платформа создана для тех, кто хочет глубже понять свой внутренний мир через исследование сновидений.</p>
    </div>

' . $this->formatQuestionHeader('question-2', 'Что можно делать без регистрации?') . '
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm">
        <p class="text-gray-700 dark:text-gray-200 mb-4 leading-relaxed">Без регистрации на платформе доступны следующие возможности:</p>
        <ul class="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-200 ml-4">
            <li><strong class="text-purple-600 dark:text-purple-300">Просмотр публичных дневников</strong> — вы можете просматривать дневники пользователей, которые настроили их как публичные</li>
            <li><strong class="text-purple-600 dark:text-purple-300">Толкование снов</strong> — функция автоматического толкования доступна всем посетителям</li>
            <li><strong class="text-purple-600 dark:text-purple-300">Просмотр ленты активности</strong> — видеть последние публичные отчеты и комментарии</li>
            <li><strong class="text-purple-600 dark:text-purple-300">Поиск по публичным отчетам</strong> — искать и просматривать публичные записи других пользователей</li>
        </ul>
        <p class="text-gray-700 dark:text-gray-200 mt-4 leading-relaxed">Однако для полноценной работы с платформой рекомендуется зарегистрироваться.</p>
    </div>

' . $this->formatQuestionHeader('question-3', 'Зачем регистрироваться? Преимущества аккаунта') . '
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm">
        <p class="text-gray-700 dark:text-gray-200 mb-4 leading-relaxed">Регистрация открывает множество дополнительных возможностей:</p>
        <ul class="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-200 ml-4">
            <li><strong class="text-purple-600 dark:text-purple-300">Личный дневник</strong> — создание и ведение собственного дневника сновидений</li>
            <li><strong class="text-purple-600 dark:text-purple-300">Неограниченное количество записей</strong> — записывайте столько снов, сколько хотите</li>
            <li><strong class="text-purple-600 dark:text-purple-300">Анализ отчетов</strong> — глубокий анализ ваших записей для поиска паттернов</li>
            <li><strong class="text-purple-600 dark:text-purple-300">Система друзей</strong> — добавляйте друзей и делитесь с ними своими снами</li>
            <li><strong class="text-purple-600 dark:text-purple-300">Комментарии</strong> — общайтесь с другими пользователями через комментарии</li>
            <li><strong class="text-purple-600 dark:text-purple-300">Настройки приватности</strong> — контролируйте, кто может видеть ваши записи</li>
            <li><strong class="text-purple-600 dark:text-purple-300">Теги и организация</strong> — используйте теги для удобной организации записей</li>
            <li><strong class="text-purple-600 dark:text-purple-300">История и статистика</strong> — отслеживайте свою активность и статистику</li>
        </ul>
        <p class="text-gray-700 dark:text-gray-200 mt-4 leading-relaxed">Регистрация бесплатна и занимает всего несколько минут.</p>
    </div>

' . $this->formatQuestionHeader('question-4', 'Как зарегистрироваться?') . '
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm">
        <p class="text-gray-700 dark:text-gray-200 mb-4 leading-relaxed">Регистрация на платформе очень проста:</p>
        <ol class="list-decimal list-inside space-y-3 text-gray-700 dark:text-gray-300 ml-4">
            <li>Перейдите на страницу регистрации, нажав кнопку "Регистрация" в правом верхнем углу сайта</li>
            <li>Заполните форму регистрации:
                <ul class="list-disc list-inside space-y-1 text-gray-700 dark:text-gray-200 ml-6 mt-2">
                    <li>Введите ваше имя</li>
                    <li>Придумайте уникальный никнейм (он будет отображаться в вашем профиле)</li>
                    <li>Укажите ваш email адрес</li>
                    <li>Придумайте надежный пароль (минимум 8 символов)</li>
                </ul>
            </li>
            <li>Нажмите кнопку "Зарегистрироваться"</li>
            <li>Проверьте вашу почту — вам придет письмо с подтверждением регистрации</li>
            <li>Перейдите по ссылке в письме для подтверждения email</li>
        </ol>
        <p class="text-gray-700 dark:text-gray-200 mt-4 leading-relaxed">После подтверждения email вы сможете войти в свой аккаунт и начать использовать все возможности платформы.</p>
    </div>

' . $this->formatQuestionHeader('question-5', 'Как войти в аккаунт?') . '
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm">
        <p class="text-gray-700 dark:text-gray-200 mb-4 leading-relaxed">Для входа в ваш аккаунт:</p>
        <ol class="list-decimal list-inside space-y-2 text-gray-700 dark:text-gray-200 ml-4">
            <li>Нажмите кнопку "Войти" в правом верхнем углу сайта</li>
            <li>Введите ваш email адрес (тот, который вы использовали при регистрации)</li>
            <li>Введите ваш пароль</li>
            <li>Нажмите кнопку "Войти"</li>
        </ol>
        <div class="mt-4 p-4 bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-400 dark:border-yellow-600 rounded-r-lg">
            <p class="text-gray-700 dark:text-gray-200"><strong class="text-yellow-800 dark:text-yellow-300">Важно:</strong> Убедитесь, что вы подтвердили ваш email адрес. Без подтверждения некоторые функции могут быть недоступны.</p>
        </div>
        <p class="text-gray-700 dark:text-gray-200 mt-4 leading-relaxed">Если вы забыли пароль, используйте ссылку "Забыли пароль?" на странице входа для его восстановления.</p>
    </div>
</div>';

    }

    /**
     * Категория 2: Толкование снов
     */
    private function getCategory2Content(string $telegramLink): string
    {
        $questions = [
            'Как работает толкование снов?',
            'Можно ли толковать без регистрации?',
            'Сколько времени занимает анализ?',
            'Что делать, если анализ не работает?',
            'Какие традиции анализа доступны?',
            'Что такое контекст и зачем он нужен?',
            'Как повторить анализ, если произошла ошибка?'
        ];
        
        return $this->formatQuestionsList($questions) . '

<div class="space-y-8">' . $this->formatQuestionHeader('question-1', 'Как работает толкование снов?') . '
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm">
        <p class="text-gray-700 dark:text-gray-200 mb-4 leading-relaxed">Толкование снов на платформе работает с помощью искусственного интеллекта. Процесс очень прост:</p>
        <ol class="list-decimal list-inside space-y-2 text-gray-700 dark:text-gray-200 ml-4">
            <li>Перейдите на страницу <a href="' . route('dream-analyzer.create') . '" class="text-purple-600 dark:text-purple-400 hover:text-purple-800 dark:hover:text-purple-300 font-medium underline">Толкование снов</a></li>
            <li>Введите описание вашего сна в текстовое поле</li>
            <li>(Опционально) Добавьте контекст — дополнительную информацию о вашей жизни, которая может помочь в анализе</li>
            <li>Выберите традицию анализа (если хотите)</li>
            <li>Нажмите кнопку "Проанализировать сон"</li>
            <li>Дождитесь завершения анализа (обычно это занимает от 30 секунд до нескольких минут)</li>
            <li>Просмотрите результаты толкования</li>
        </ol>
        <p class="text-gray-700 dark:text-gray-200 mt-4 leading-relaxed">Система проанализирует ваш сон и предоставит подробное толкование в выбранной традиции.</p>
    </div>

' . $this->formatQuestionHeader('question-2', 'Можно ли толковать без регистрации?') . '
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm">
        <p class="text-gray-700 dark:text-gray-200 mb-4 leading-relaxed">Да, функция толкования снов доступна всем посетителям сайта, даже без регистрации. Вы можете:</p>
        <ul class="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-200 ml-4">
            <li>Вводить описание сна</li>
            <li>Получать толкование</li>
            <li>Просматривать результаты</li>
        </ul>
        <p class="text-gray-700 dark:text-gray-200 mt-4 leading-relaxed">Однако для сохранения результатов и доступа к истории толкований рекомендуется зарегистрироваться.</p>
    </div>

' . $this->formatQuestionHeader('question-3', 'Сколько времени занимает анализ?') . '
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm">
        <p class="text-gray-700 dark:text-gray-200 mb-4 leading-relaxed">Время анализа зависит от нескольких факторов:</p>
        <ul class="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-200 ml-4">
            <li><strong class="text-purple-600 dark:text-purple-300">Длина описания сна</strong> — чем подробнее описание, тем дольше анализ</li>
            <li><strong class="text-purple-600 dark:text-purple-300">Нагрузка на сервер</strong> — в периоды высокой активности анализ может занять больше времени</li>
            <li><strong class="text-purple-600 dark:text-purple-300">Выбранная традиция</strong> — некоторые традиции требуют более глубокого анализа</li>
        </ul>
        <div class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-400 dark:border-blue-600 rounded-r-lg">
            <p class="text-gray-700 dark:text-gray-200">Обычно анализ занимает от <strong class="text-blue-800 dark:text-blue-300">30 секунд до 3-5 минут</strong>. Если анализ занимает дольше 10 минут, возможно, произошла ошибка — попробуйте повторить анализ.</p>
        </div>
    </div>

' . $this->formatQuestionHeader('question-4', 'Что делать, если анализ не работает?') . '
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm">
        <p class="text-gray-700 dark:text-gray-200 mb-4 leading-relaxed">Если анализ не работает или выдает ошибку:</p>
        <ol class="list-decimal list-inside space-y-2 text-gray-700 dark:text-gray-200 ml-4">
            <li><strong class="text-purple-600 dark:text-purple-300">Проверьте подключение к интернету</strong> — убедитесь, что у вас стабильное соединение</li>
            <li><strong class="text-purple-600 dark:text-purple-300">Попробуйте обновить страницу</strong> — иногда помогает простая перезагрузка</li>
            <li><strong class="text-purple-600 dark:text-purple-300">Повторите анализ</strong> — нажмите кнопку "Повторить анализ" на странице с ошибкой</li>
            <li><strong class="text-purple-600 dark:text-purple-300">Проверьте длину текста</strong> — очень длинные описания могут вызывать проблемы</li>
            <li><strong class="text-purple-600 dark:text-purple-300">Обратитесь в поддержку</strong> — если проблема повторяется, свяжитесь с нами через <a href="' . $telegramLink . '" target="_blank" class="text-purple-600 dark:text-purple-400 hover:text-purple-800 dark:hover:text-purple-300 font-medium underline">Telegram</a></li>
        </ol>
        <p class="text-gray-700 dark:text-gray-200 mt-4 leading-relaxed">Большинство ошибок носят временный характер и решаются повторным анализом.</p>
    </div>

' . $this->formatQuestionHeader('question-5', 'Какие традиции анализа доступны?') . '
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm">
        <p class="text-gray-700 dark:text-gray-200 mb-4 leading-relaxed">На платформе доступны различные традиции толкования снов:</p>
        <ul class="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-200 ml-4">
            <li><strong class="text-purple-600 dark:text-purple-300">Психологическая</strong> — анализ с точки зрения психологии и подсознания</li>
            <li><strong class="text-purple-600 dark:text-purple-300">Символическая</strong> — толкование символов и образов</li>
            <li><strong class="text-purple-600 dark:text-purple-300">Культурная</strong> — анализ с учетом культурных особенностей</li>
            <li><strong class="text-purple-600 dark:text-purple-300">Другие традиции</strong> — в зависимости от настроек платформы</li>
        </ul>
        <p class="text-gray-700 dark:text-gray-200 mt-4 leading-relaxed">Вы можете выбрать традицию перед началом анализа или оставить выбор по умолчанию.</p>
    </div>

' . $this->formatQuestionHeader('question-6', 'Что такое контекст и зачем он нужен?') . '
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm">
        <p class="text-gray-700 dark:text-gray-200 mb-4 leading-relaxed">Контекст — это дополнительная информация о вашей жизни, которая может помочь в более точном толковании сна:</p>
        <ul class="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-200 ml-4">
            <li>Текущие события в вашей жизни</li>
            <li>Эмоциональное состояние</li>
            <li>Важные отношения</li>
            <li>Проблемы или переживания</li>
        </ul>
        <p class="text-gray-700 dark:text-gray-200 mt-4 leading-relaxed">Контекст необязателен, но его использование может значительно улучшить качество толкования, сделав его более персонализированным и релевантным для вас.</p>
        <div class="mt-4 p-4 bg-green-50 dark:bg-green-900/20 border-l-4 border-green-400 dark:border-green-600 rounded-r-lg">
            <p class="text-gray-700 dark:text-gray-200"><strong class="text-green-800 dark:text-green-300">Пример:</strong> Если вы переживаете из-за работы, указание этого в контексте поможет системе связать образы сна с вашими переживаниями.</p>
        </div>
    </div>

' . $this->formatQuestionHeader('question-7', 'Как повторить анализ, если произошла ошибка?') . '
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm">
        <p class="text-gray-700 dark:text-gray-200 mb-4 leading-relaxed">Если при анализе произошла ошибка, вы можете повторить анализ:</p>
        <ol class="list-decimal list-inside space-y-2 text-gray-700 dark:text-gray-200 ml-4">
            <li>На странице с результатами (или ошибкой) найдите кнопку <strong class="text-purple-600 dark:text-purple-300">"Повторить анализ"</strong></li>
            <li>Нажмите на неё</li>
            <li>Система автоматически сбросит предыдущий результат и начнет новый анализ</li>
            <li>Дождитесь завершения анализа</li>
        </ol>
        <p class="text-gray-700 dark:text-gray-200 mt-4 leading-relaxed">Повторный анализ использует те же данные (описание сна, контекст, традицию), которые вы вводили ранее, поэтому вам не нужно вводить их заново.</p>
        <p class="text-gray-700 dark:text-gray-200 mt-4 leading-relaxed">Если ошибка повторяется несколько раз, обратитесь в <a href="' . $telegramLink . '" target="_blank" class="text-purple-600 dark:text-purple-400 hover:text-purple-800 dark:hover:text-purple-300 font-medium underline">поддержку</a>.</p>
    </div>
</div>';

    }

    /**
     * Категория 3: Отчеты и сны
     */
    private function getCategory3Content(): string
    {
        $questions = [
            'Как создать отчет о сне?',
            'Что такое отчет и чем он отличается от сна?',
            'Типы снов (Яркий, Бледный, ВТО, ОС и т.д.)',
            'Как редактировать отчет?',
            'Как удалить отчет?',
            'Настройки доступа к отчету (все/только друзья/никто)',
            'Что такое черновик?'
        ];
        
        return $this->formatQuestionsList($questions) . '

<div class="space-y-8">' . $this->formatQuestionHeader('question-1', 'Как создать отчет о сне?') . '
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm">
        <p class="text-gray-700 dark:text-gray-200 mb-4 leading-relaxed">Для создания отчета о сне:</p>
        <ol class="list-decimal list-inside space-y-2 text-gray-700 dark:text-gray-200 ml-4">
            <li>Войдите в свой аккаунт</li>
            <li>Нажмите кнопку <strong class="text-purple-600 dark:text-purple-300">"Добавить сон"</strong> в правом верхнем углу или перейдите на страницу <a href="' . route('reports.create') . '" class="text-purple-600 dark:text-purple-400 hover:text-purple-800 dark:hover:text-purple-300 font-medium underline">Создание отчета</a></li>
            <li>Выберите дату сна (по умолчанию — сегодняшняя дата)</li>
            <li>Нажмите кнопку <strong class="text-purple-600 dark:text-purple-300">"Добавить сон"</strong> в разделе "Сны"</li>
            <li>Заполните информацию о сне:
                <ul class="list-disc list-inside space-y-1 text-gray-700 dark:text-gray-200 ml-6 mt-2">
                    <li>Название сна (опционально)</li>
                    <li>Описание сна (обязательно)</li>
                    <li>Тип сна (Яркий, Бледный, ВТО и т.д.)</li>
                </ul>
            </li>
            <li>Можете добавить несколько снов в один отчет, нажав "Добавить сон" еще раз</li>
            <li>(Опционально) Добавьте теги к отчету</li>
            <li>Настройте доступ к отчету (кто может его видеть)</li>
            <li>Нажмите кнопку <strong class="text-purple-600 dark:text-purple-300">"Сохранить"</strong> или <strong class="text-purple-600 dark:text-purple-300">"Сохранить как черновик"</strong></li>
        </ol>
        <p class="text-gray-700 dark:text-gray-200 mt-4 leading-relaxed">Отчет будет сохранен и появится в вашем дневнике.</p>
    </div>

' . $this->formatQuestionHeader('question-2', 'Что такое отчет и чем он отличается от сна?') . '
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm">
        <p class="text-gray-700 dark:text-gray-200 mb-4 leading-relaxed"><strong class="text-purple-600 dark:text-purple-300">Отчет</strong> — это контейнер, который объединяет один или несколько снов за определенную дату. В одном отчете может быть несколько снов.</p>
        <p class="text-gray-700 dark:text-gray-300 mb-4 leading-relaxed"><strong class="text-purple-600 dark:text-purple-300">Сон</strong> — это отдельная запись о конкретном сновидении внутри отчета.</p>
        <div class="mt-4 p-4 bg-green-50 dark:bg-green-900/20 border-l-4 border-green-400 dark:border-green-600 rounded-r-lg">
            <p class="text-gray-700 dark:text-gray-200"><strong class="text-green-800 dark:text-green-300">Пример:</strong> Если вам приснилось несколько снов за одну ночь, вы можете создать один отчет на эту дату и добавить в него несколько снов. Каждый сон будет иметь свое описание и тип.</p>
        </div>
        <p class="text-gray-700 dark:text-gray-200 mt-4 leading-relaxed">Такая структура позволяет лучше организовывать записи и анализировать сны, которые приснились в одну ночь.</p>
    </div>

' . $this->formatQuestionHeader('question-3', 'Типы снов (Яркий, Бледный, ВТО, ОС и т.д.)') . '
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm">
        <p class="text-gray-700 dark:text-gray-200 mb-4 leading-relaxed">На платформе доступны следующие типы снов:</p>
        <ul class="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-200 ml-4">
            <li><strong class="text-purple-600 dark:text-purple-300">Яркий сон</strong> — сон с яркими, запоминающимися образами и эмоциями</li>
            <li><strong class="text-purple-600 dark:text-purple-300">Бледный сон</strong> — сон с размытыми, нечеткими образами</li>
            <li><strong class="text-purple-600 dark:text-purple-300">Пограничное состояние</strong> — состояние между сном и бодрствованием</li>
            <li><strong class="text-purple-600 dark:text-purple-300">Паралич</strong> — сонный паралич</li>
            <li><strong class="text-purple-600 dark:text-purple-300">ВТО</strong> — выход из тела (астральная проекция)</li>
            <li><strong class="text-purple-600 dark:text-purple-300">Осознанное сновидение</strong> — сон, в котором вы осознаете, что спите</li>
            <li><strong class="text-purple-600 dark:text-purple-300">Глюк</strong> — необычные, странные образы</li>
            <li><strong class="text-purple-600 dark:text-purple-300">Транс / Гипноз</strong> — состояние транса или гипноза</li>
        </ul>
        <p class="text-gray-700 dark:text-gray-200 mt-4 leading-relaxed">Выбор типа сна помогает лучше классифицировать и анализировать ваши сновидения.</p>
    </div>

' . $this->formatQuestionHeader('question-4', 'Как редактировать отчет?') . '
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm">
        <p class="text-gray-700 dark:text-gray-200 mb-4 leading-relaxed">Для редактирования отчета:</p>
        <ol class="list-decimal list-inside space-y-2 text-gray-700 dark:text-gray-200 ml-4">
            <li>Найдите нужный отчет в вашем дневнике</li>
            <li>Откройте его, нажав на заголовок или дату</li>
            <li>Нажмите кнопку <strong class="text-purple-600 dark:text-purple-300">"Редактировать"</strong> (иконка карандаша)</li>
            <li>Внесите необходимые изменения:
                <ul class="list-disc list-inside space-y-1 text-gray-700 dark:text-gray-200 ml-6 mt-2">
                    <li>Измените дату отчета</li>
                    <li>Отредактируйте существующие сны</li>
                    <li>Добавьте новые сны</li>
                    <li>Удалите ненужные сны</li>
                    <li>Измените теги</li>
                    <li>Измените настройки доступа</li>
                </ul>
            </li>
            <li>Нажмите кнопку <strong class="text-purple-600 dark:text-purple-300">"Сохранить"</strong></li>
        </ol>
        <div class="mt-4 p-4 bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-400 dark:border-yellow-600 rounded-r-lg">
            <p class="text-gray-700 dark:text-gray-200"><strong class="text-yellow-800 dark:text-yellow-300">Важно:</strong> Вы можете редактировать только свои отчеты. Отчеты других пользователей недоступны для редактирования.</p>
        </div>
    </div>

' . $this->formatQuestionHeader('question-5', 'Как удалить отчет?') . '
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm">
        <p class="text-gray-700 dark:text-gray-200 mb-4 leading-relaxed">Для удаления отчета:</p>
        <ol class="list-decimal list-inside space-y-2 text-gray-700 dark:text-gray-200 ml-4">
            <li>Откройте отчет, который хотите удалить</li>
            <li>Нажмите кнопку <strong class="text-purple-600 dark:text-purple-300">"Удалить"</strong> (иконка корзины)</li>
            <li>Подтвердите удаление в появившемся окне</li>
        </ol>
        <div class="mt-4 p-4 bg-red-50 dark:bg-red-900/20 border-l-4 border-red-400 dark:border-red-600 rounded-r-lg">
            <p class="text-gray-700 dark:text-gray-200"><strong class="text-red-800 dark:text-red-300">Внимание:</strong> Удаление отчета необратимо. Все сны, комментарии и связанные данные будут удалены навсегда.</p>
        </div>
        <p class="text-gray-700 dark:text-gray-200 mt-4 leading-relaxed">Если вы не уверены, хотите ли удалять отчет, лучше сохраните его как черновик или измените настройки доступа на "Никто".</p>
    </div>

' . $this->formatQuestionHeader('question-6', 'Настройки доступа к отчету (все/только друзья/никто)') . '
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm">
        <p class="text-gray-700 dark:text-gray-200 mb-4 leading-relaxed">Вы можете контролировать, кто может видеть ваш отчет:</p>
        <ul class="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-200 ml-4">
            <li><strong class="text-purple-600 dark:text-purple-300">Все</strong> — отчет виден всем посетителям сайта (публичный)</li>
            <li><strong class="text-purple-600 dark:text-purple-300">Только друзья</strong> — отчет виден только вашим друзьям</li>
            <li><strong class="text-purple-600 dark:text-purple-300">Никто</strong> — отчет виден только вам (приватный)</li>
        </ul>
        <p class="text-gray-700 dark:text-gray-200 mt-4 leading-relaxed">Настройки доступа можно изменить при создании или редактировании отчета. По умолчанию отчеты создаются с доступом "Все".</p>
        <div class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-400 dark:border-blue-600 rounded-r-lg">
            <p class="text-gray-700 dark:text-gray-200"><strong class="text-blue-800 dark:text-blue-300">Примечание:</strong> Настройки доступа к отчету работают независимо от настроек приватности дневника. Даже если ваш дневник приватный, вы можете сделать отдельные отчеты публичными.</p>
        </div>
    </div>

' . $this->formatQuestionHeader('question-7', 'Что такое черновик?') . '
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm">
        <p class="text-gray-700 dark:text-gray-200 mb-4 leading-relaxed">Черновик — это неопубликованный отчет, который сохранен, но еще не готов к публикации. Черновики:</p>
        <ul class="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-200 ml-4">
            <li>Не видны другим пользователям (даже если настройки доступа "Все")</li>
            <li>Не отображаются в публичных лентах</li>
            <li>Могут быть отредактированы и опубликованы позже</li>
            <li>Помогают сохранить незавершенные записи</li>
        </ul>
        <p class="text-gray-700 dark:text-gray-200 mt-4 leading-relaxed">Чтобы сохранить отчет как черновик, нажмите кнопку <strong class="text-purple-600 dark:text-purple-300">"Сохранить как черновик"</strong> вместо "Сохранить". Позже вы можете опубликовать черновик, отредактировав его и нажав "Опубликовать".</p>
    </div>
</div>';

    }

    /**
     * Категория 4: Анализ отчетов
     */
    private function getCategory4Content(): string
    {
        $questions = [
            'Как проанализировать свой отчет?',
            'Чем анализ отчета отличается от толкования?',
            'Сколько времени занимает анализ?',
            'Можно ли анализировать несколько отчетов вместе?'
        ];
        
        return $this->formatQuestionsList($questions) . '

<div class="space-y-8">' . $this->formatQuestionHeader('question-1', 'Как проанализировать свой отчет?') . '
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm">
        <p class="text-gray-700 dark:text-gray-200 mb-4 leading-relaxed">Для анализа вашего отчета:</p>
        <ol class="list-decimal list-inside space-y-2 text-gray-700 dark:text-gray-200 ml-4">
            <li>Откройте отчет, который хотите проанализировать</li>
            <li>Найдите кнопку <strong class="text-purple-600 dark:text-purple-300">"Проанализировать отчет"</strong> или перейдите на страницу анализа</li>
            <li>Нажмите на кнопку — система начнет анализ</li>
            <li>Дождитесь завершения анализа (обычно это занимает несколько минут)</li>
            <li>Просмотрите результаты анализа</li>
        </ol>
        <p class="text-gray-700 dark:text-gray-200 mt-4 leading-relaxed">Анализ отчета включает глубокое изучение всех снов в отчете, поиск паттернов, символов и связей между различными элементами.</p>
        <div class="mt-4 p-4 bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-400 dark:border-yellow-600 rounded-r-lg">
            <p class="text-gray-700 dark:text-gray-200"><strong class="text-yellow-800 dark:text-yellow-300">Важно:</strong> Анализ доступен только для ваших собственных отчетов. Анализировать отчеты других пользователей нельзя.</p>
        </div>
    </div>

' . $this->formatQuestionHeader('question-2', 'Чем анализ отчета отличается от толкования?') . '
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm">
        <p class="text-gray-700 dark:text-gray-200 mb-4 leading-relaxed">Анализ отчета и толкование снов — это разные функции:</p>
        <div class="overflow-x-auto mt-4">
            <table class="w-full border-collapse border border-gray-300 dark:border-gray-600">
                <thead>
                    <tr class="bg-gray-100 dark:bg-gray-700">
                        <th class="p-3 border border-gray-300 dark:border-gray-600 text-left font-semibold text-gray-900 dark:text-gray-100">Толкование снов</th>
                        <th class="p-3 border border-gray-300 dark:border-gray-600 text-left font-semibold text-gray-900 dark:text-gray-100">Анализ отчета</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="bg-white dark:bg-gray-800">
                        <td class="p-3 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300">Работает с одним описанием сна</td>
                        <td class="p-3 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300">Анализирует весь отчет (все сны за дату)</td>
                    </tr>
                    <tr class="bg-gray-50 dark:bg-gray-700/50">
                        <td class="p-3 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300">Доступно без регистрации</td>
                        <td class="p-3 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300">Требует регистрации и наличия отчета</td>
                    </tr>
                    <tr class="bg-white dark:bg-gray-800">
                        <td class="p-3 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300">Быстрый результат (30 сек - 5 мин)</td>
                        <td class="p-3 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300">Более глубокий анализ (5-10 минут)</td>
                    </tr>
                    <tr class="bg-gray-50 dark:bg-gray-700/50">
                        <td class="p-3 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300">Фокус на одном сне</td>
                        <td class="p-3 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300">Поиск связей и паттернов между снами</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="text-gray-700 dark:text-gray-200 mt-4 leading-relaxed">Используйте толкование для быстрого понимания отдельного сна, а анализ отчета — для глубокого исследования ваших сновидений.</p>
    </div>

' . $this->formatQuestionHeader('question-3', 'Сколько времени занимает анализ?') . '
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm">
        <p class="text-gray-700 dark:text-gray-200 mb-4 leading-relaxed">Время анализа отчета зависит от:</p>
        <ul class="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-200 ml-4">
            <li><strong class="text-purple-600 dark:text-purple-300">Количества снов в отчете</strong> — чем больше снов, тем дольше анализ</li>
            <li><strong class="text-purple-600 dark:text-purple-300">Длины описаний</strong> — подробные описания требуют больше времени</li>
            <li><strong class="text-purple-600 dark:text-purple-300">Нагрузки на сервер</strong> — в периоды высокой активности анализ может занять больше времени</li>
        </ul>
        <div class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-400 dark:border-blue-600 rounded-r-lg">
            <p class="text-gray-700 dark:text-gray-200">Обычно анализ занимает от <strong class="text-blue-800 dark:text-blue-300">5 до 10 минут</strong>. В некоторых случаях анализ может занять до 15 минут.</p>
        </div>
        <p class="text-gray-700 dark:text-gray-200 mt-4 leading-relaxed">Если анализ занимает дольше 20 минут, возможно, произошла ошибка — попробуйте обновить страницу или повторить анализ позже.</p>
    </div>

' . $this->formatQuestionHeader('question-4', 'Можно ли анализировать несколько отчетов вместе?') . '
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm">
        <p class="text-gray-700 dark:text-gray-200 mb-4 leading-relaxed">В текущей версии платформы анализ работает с одним отчетом за раз. Однако вы можете:</p>
        <ul class="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-200 ml-4">
            <li>Проанализировать несколько отчетов последовательно</li>
            <li>Сравнить результаты анализов разных отчетов</li>
            <li>Искать общие паттерны в результатах</li>
        </ul>
        <p class="text-gray-700 dark:text-gray-200 mt-4 leading-relaxed">Если вам нужно проанализировать несколько отчетов за разные даты, создайте их отдельно и проанализируйте каждый. Результаты будут сохранены и доступны для просмотра в любое время.</p>
        <div class="mt-4 p-4 bg-green-50 dark:bg-green-900/20 border-l-4 border-green-400 dark:border-green-600 rounded-r-lg">
            <p class="text-gray-700 dark:text-gray-200"><strong class="text-green-800 dark:text-green-300">Совет:</strong> Для лучшего понимания паттернов анализируйте отчеты за разные периоды и сравнивайте результаты.</p>
        </div>
    </div>
</div>';

    }

    /**
     * Категория 5: Дневник и профиль
     */
    private function getCategory5Content(): string
    {
        $questions = [
            'Что такое публичный дневник?',
            'Как настроить приватность дневника?',
            'Как получить публичную ссылку на дневник?',
            'Как изменить профиль?',
            'Как загрузить аватар?'
        ];
        
        return $this->formatQuestionsList($questions) . '

<div class="space-y-8">' . $this->formatQuestionHeader('question-1', 'Что такое публичный дневник?') . '
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm">
        <p class="text-gray-700 dark:text-gray-200 mb-4 leading-relaxed">Публичный дневник — это ваш дневник сновидений, который доступен для просмотра всем посетителям сайта. В публичном дневнике отображаются:</p>
        <ul class="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-200 ml-4">
            <li>Ваш никнейм и профиль</li>
            <li>Все отчеты с настройкой доступа "Все"</li>
            <li>Статистика вашего дневника (количество отчетов, снов)</li>
            <li>Дата последнего отчета</li>
        </ul>
        <p class="text-gray-700 dark:text-gray-200 mt-4 leading-relaxed">Приватные отчеты (с настройкой "Никто" или "Только друзья") не отображаются в публичном дневнике.</p>
        <p class="text-gray-700 dark:text-gray-200 mt-4 leading-relaxed">Публичный дневник можно открыть по специальной ссылке, которую вы можете поделиться с друзьями или разместить в социальных сетях.</p>
    </div>

' . $this->formatQuestionHeader('question-2', 'Как настроить приватность дневника?') . '
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm">
        <p class="text-gray-700 dark:text-gray-200 mb-4 leading-relaxed">Для настройки приватности дневника:</p>
        <ol class="list-decimal list-inside space-y-2 text-gray-700 dark:text-gray-200 ml-4">
            <li>Перейдите в настройки профиля (меню пользователя → "Профиль" или "Настройки")</li>
            <li>Найдите раздел "Приватность дневника"</li>
            <li>Выберите один из вариантов:
                <ul class="list-disc list-inside space-y-1 text-gray-700 dark:text-gray-200 ml-6 mt-2">
                    <li><strong class="text-purple-600 dark:text-purple-300">Публичный</strong> — дневник виден всем (но отдельные отчеты могут быть приватными)</li>
                    <li><strong class="text-purple-600 dark:text-purple-300">Приватный</strong> — дневник виден только вам</li>
                    <li><strong class="text-purple-600 dark:text-purple-300">Только друзья</strong> — дневник виден только вашим друзьям</li>
                </ul>
            </li>
            <li>Сохраните изменения</li>
        </ol>
        <div class="mt-4 p-4 bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-400 dark:border-yellow-600 rounded-r-lg">
            <p class="text-gray-700 dark:text-gray-200"><strong class="text-yellow-800 dark:text-yellow-300">Важно:</strong> Настройки приватности дневника работают на уровне всего дневника. Отдельные отчеты могут иметь свои настройки доступа, которые работают независимо.</p>
        </div>
    </div>

' . $this->formatQuestionHeader('question-3', 'Как получить публичную ссылку на дневник?') . '
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm">
        <p class="text-gray-700 dark:text-gray-200 mb-4 leading-relaxed">Публичная ссылка на ваш дневник генерируется автоматически при создании аккаунта. Чтобы найти её:</p>
        <ol class="list-decimal list-inside space-y-2 text-gray-700 dark:text-gray-200 ml-4">
            <li>Перейдите в настройки профиля</li>
            <li>Найдите раздел "Публичная ссылка" или "Ссылка на дневник"</li>
            <li>Скопируйте ссылку</li>
            <li>Поделитесь ею с друзьями или разместите где угодно</li>
        </ol>
        <p class="text-gray-700 dark:text-gray-200 mt-4 leading-relaxed">Публичная ссылка имеет формат: <code class="bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded text-sm">' . url('/diary') . '/{ваша-ссылка}</code></p>
        <div class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-400 dark:border-blue-600 rounded-r-lg">
            <p class="text-gray-700 dark:text-gray-200"><strong class="text-blue-800 dark:text-blue-300">Примечание:</strong> Публичная ссылка работает только если ваш дневник настроен как публичный или "Только друзья". Если дневник приватный, ссылка не будет работать для других пользователей.</p>
        </div>
    </div>

' . $this->formatQuestionHeader('question-4', 'Как изменить профиль?') . '
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm">
        <p class="text-gray-700 dark:text-gray-200 mb-4 leading-relaxed">Для изменения профиля:</p>
        <ol class="list-decimal list-inside space-y-2 text-gray-700 dark:text-gray-200 ml-4">
            <li>Нажмите на ваш никнейм или аватар в правом верхнем углу</li>
            <li>Выберите "Профиль" или "Настройки"</li>
            <li>На странице редактирования профиля вы можете изменить:
                <ul class="list-disc list-inside space-y-1 text-gray-700 dark:text-gray-200 ml-6 mt-2">
                    <li>Имя</li>
                    <li>Никнейм (если доступно)</li>
                    <li>Описание (био)</li>
                    <li>Аватар</li>
                    <li>Настройки приватности</li>
                </ul>
            </li>
            <li>Нажмите кнопку "Сохранить"</li>
        </ol>
        <p class="text-gray-700 dark:text-gray-200 mt-4 leading-relaxed">Некоторые поля (например, email) могут быть недоступны для изменения или требуют дополнительной проверки.</p>
    </div>

' . $this->formatQuestionHeader('question-5', 'Как загрузить аватар?') . '
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm">
        <p class="text-gray-700 dark:text-gray-200 mb-4 leading-relaxed">Для загрузки аватара:</p>
        <ol class="list-decimal list-inside space-y-2 text-gray-700 dark:text-gray-200 ml-4">
            <li>Перейдите в настройки профиля</li>
            <li>Найдите раздел "Аватар" или "Фото профиля"</li>
            <li>Нажмите кнопку "Загрузить" или "Выбрать файл"</li>
            <li>Выберите изображение с вашего компьютера</li>
            <li>Подождите, пока изображение загрузится</li>
            <li>При необходимости обрежьте или отредактируйте изображение</li>
            <li>Нажмите "Сохранить"</li>
        </ol>
        <p class="text-gray-700 dark:text-gray-200 mt-4 leading-relaxed"><strong class="text-purple-600 dark:text-purple-300">Требования к изображению:</strong></p>
        <ul class="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-4 mt-2">
            <li>Формат: JPG, PNG, GIF</li>
            <li>Максимальный размер: обычно 2-5 МБ (зависит от настроек платформы)</li>
            <li>Рекомендуемый размер: квадратное изображение (например, 200x200 или 400x400 пикселей)</li>
        </ul>
        <p class="text-gray-700 dark:text-gray-200 mt-4 leading-relaxed">После загрузки аватар будет отображаться в вашем профиле, комментариях и везде, где показывается ваш аккаунт.</p>
    </div>
</div>';

    }

    /**
     * Категория 6: Друзья и сообщество
     */
    private function getCategory6Content(): string
    {
        $questions = [
            'Как добавить друга?',
            'Как принять/отклонить запрос в друзья?',
            'Что дает дружба?',
            'Как посмотреть дневник друга?'
        ];
        
        return $this->formatQuestionsList($questions) . '

<div class="space-y-8">' . $this->formatQuestionHeader('question-1', 'Как добавить друга?') . '
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm">
        <p class="text-gray-700 dark:text-gray-200 mb-4 leading-relaxed">Для добавления пользователя в друзья:</p>
        <ol class="list-decimal list-inside space-y-2 text-gray-700 dark:text-gray-200 ml-4">
            <li>Найдите пользователя, которого хотите добавить:
                <ul class="list-disc list-inside space-y-1 text-gray-700 dark:text-gray-200 ml-6 mt-2">
                    <li>Через поиск пользователей</li>
                    <li>На странице его профиля</li>
                    <li>В комментариях к отчетам</li>
                </ul>
            </li>
            <li>Откройте профиль пользователя</li>
            <li>Нажмите кнопку <strong class="text-purple-600 dark:text-purple-300">"Добавить в друзья"</strong> или "Отправить запрос в друзья"</li>
            <li>Дождитесь подтверждения запроса от пользователя</li>
        </ol>
        <p class="text-gray-700 dark:text-gray-200 mt-4 leading-relaxed">После отправки запроса пользователь получит уведомление и сможет принять или отклонить ваш запрос.</p>
        <div class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-400 dark:border-blue-600 rounded-r-lg">
            <p class="text-gray-700 dark:text-gray-200"><strong class="text-blue-800 dark:text-blue-300">Примечание:</strong> Вы не можете добавить в друзья пользователя, который уже отправил вам запрос — в этом случае вам нужно принять его запрос.</p>
        </div>
    </div>

' . $this->formatQuestionHeader('question-2', 'Как принять/отклонить запрос в друзья?') . '
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm">
        <p class="text-gray-700 dark:text-gray-200 mb-4 leading-relaxed">Когда кто-то отправляет вам запрос в друзья:</p>
        <ol class="list-decimal list-inside space-y-2 text-gray-700 dark:text-gray-200 ml-4">
            <li>Вы получите уведомление о новом запросе</li>
            <li>Перейдите в раздел "Друзья" или "Запросы в друзья"</li>
            <li>Найдите запрос от пользователя</li>
            <li>Выберите действие:
                <ul class="list-disc list-inside space-y-1 text-gray-700 dark:text-gray-200 ml-6 mt-2">
                    <li><strong class="text-purple-600 dark:text-purple-300">"Принять"</strong> — пользователь станет вашим другом</li>
                    <li><strong class="text-purple-600 dark:text-purple-300">"Отклонить"</strong> — запрос будет отклонен, пользователь не получит уведомление об отклонении</li>
                </ul>
            </li>
        </ol>
        <p class="text-gray-700 dark:text-gray-200 mt-4 leading-relaxed">После принятия запроса пользователь автоматически станет вашим другом, и вы сможете видеть его приватные отчеты (если они настроены как "Только друзья").</p>
        <p class="text-gray-700 dark:text-gray-200 mt-4 leading-relaxed">Вы также можете удалить друга позже, если захотите.</p>
    </div>

' . $this->formatQuestionHeader('question-3', 'Что дает дружба?') . '
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm">
        <p class="text-gray-700 dark:text-gray-200 mb-4 leading-relaxed">Дружба на платформе открывает следующие возможности:</p>
        <ul class="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-200 ml-4">
            <li><strong class="text-purple-600 dark:text-purple-300">Доступ к приватным отчетам</strong> — вы можете видеть отчеты друга, которые настроены как "Только друзья"</li>
            <li><strong class="text-purple-600 dark:text-purple-300">Быстрый доступ к дневнику друга</strong> — дневник друга появляется в вашем списке друзей</li>
            <li><strong class="text-purple-600 dark:text-purple-300">Уведомления</strong> — вы можете получать уведомления о новых отчетах друзей (если настроено)</li>
            <li><strong class="text-purple-600 dark:text-purple-300">Социальное взаимодействие</strong> — легче находить и комментировать отчеты друзей</li>
        </ul>
        <p class="text-gray-700 dark:text-gray-200 mt-4 leading-relaxed">Дружба — это двусторонняя связь: если вы добавили пользователя в друзья, он также становится вашим другом и получает доступ к вашим приватным отчетам (с настройкой "Только друзья").</p>
    </div>

' . $this->formatQuestionHeader('question-4', 'Как посмотреть дневник друга?') . '
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm">
        <p class="text-gray-700 dark:text-gray-200 mb-4 leading-relaxed">Для просмотра дневника друга:</p>
        <ol class="list-decimal list-inside space-y-2 text-gray-700 dark:text-gray-200 ml-4">
            <li>Перейдите в раздел "Друзья" или найдите друга через поиск</li>
            <li>Нажмите на имя или аватар друга</li>
            <li>Откроется его профиль и дневник</li>
            <li>Просматривайте его публичные отчеты и отчеты, доступные друзьям</li>
        </ol>
        <p class="text-gray-700 dark:text-gray-200 mt-4 leading-relaxed">В дневнике друга вы увидите:</p>
        <ul class="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-200 ml-4">
            <li>Все его публичные отчеты</li>
            <li>Отчеты с настройкой "Только друзья" (если вы друзья)</li>
            <li>Статистику его дневника</li>
            <li>Дату последнего отчета</li>
        </ul>
        <p class="text-gray-700 dark:text-gray-200 mt-4 leading-relaxed">Приватные отчеты друга (с настройкой "Никто") недоступны для просмотра, даже если вы друзья.</p>
    </div>
</div>';

    }

    /**
     * Категория 7: Комментарии и взаимодействие
     */
    private function getCategory7Content(): string
    {
        $questions = [
            'Как оставить комментарий к отчету?',
            'Как удалить свой комментарий?',
            'Кто может комментировать мои отчеты?'
        ];
        
        return $this->formatQuestionsList($questions) . '

<div class="space-y-8">' . $this->formatQuestionHeader('question-1', 'Как оставить комментарий к отчету?') . '
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm">
        <p class="text-gray-700 dark:text-gray-200 mb-4 leading-relaxed">Для оставления комментария к отчету:</p>
        <ol class="list-decimal list-inside space-y-2 text-gray-700 dark:text-gray-200 ml-4">
            <li>Откройте отчет, к которому хотите оставить комментарий</li>
            <li>Прокрутите страницу вниз до раздела "Комментарии"</li>
            <li>Введите ваш комментарий в текстовое поле</li>
            <li>Нажмите кнопку <strong class="text-purple-600 dark:text-purple-300">"Отправить"</strong> или "Оставить комментарий"</li>
        </ol>
        <p class="text-gray-700 dark:text-gray-200 mt-4 leading-relaxed">Ваш комментарий появится под отчетом, и автор отчета получит уведомление (если у него включены уведомления).</p>
        <div class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-400 dark:border-blue-600 rounded-r-lg">
            <p class="text-gray-700 dark:text-gray-200"><strong class="text-blue-800 dark:text-blue-300">Примечание:</strong> Вы можете комментировать только публичные отчеты или отчеты, к которым у вас есть доступ (например, отчеты друзей с настройкой "Только друзья").</p>
        </div>
        <p class="text-gray-700 dark:text-gray-200 mt-4 leading-relaxed">Также вы можете отвечать на комментарии других пользователей, создавая вложенные комментарии.</p>
    </div>

' . $this->formatQuestionHeader('question-2', 'Как удалить свой комментарий?') . '
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm">
        <p class="text-gray-700 dark:text-gray-200 mb-4 leading-relaxed">Для удаления вашего комментария:</p>
        <ol class="list-decimal list-inside space-y-2 text-gray-700 dark:text-gray-200 ml-4">
            <li>Найдите ваш комментарий под отчетом</li>
            <li>Нажмите кнопку <strong class="text-purple-600 dark:text-purple-300">"Удалить"</strong> (иконка корзины) рядом с комментарием</li>
            <li>Подтвердите удаление</li>
        </ol>
        <p class="text-gray-700 dark:text-gray-200 mt-4 leading-relaxed">Комментарий будет удален, и другие пользователи больше не смогут его видеть.</p>
        <div class="mt-4 p-4 bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-400 dark:border-yellow-600 rounded-r-lg">
            <p class="text-gray-700 dark:text-gray-200"><strong class="text-yellow-800 dark:text-yellow-300">Важно:</strong> Вы можете удалять только свои собственные комментарии. Комментарии других пользователей удалить нельзя, но автор отчета может удалить любые комментарии к своему отчету.</p>
        </div>
        <p class="text-gray-700 dark:text-gray-200 mt-4 leading-relaxed">Если вы хотите изменить комментарий, удалите его и создайте новый.</p>
    </div>

' . $this->formatQuestionHeader('question-3', 'Кто может комментировать мои отчеты?') . '
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm">
        <p class="text-gray-700 dark:text-gray-200 mb-4 leading-relaxed">Комментировать ваши отчеты могут:</p>
        <ul class="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-200 ml-4">
            <li><strong class="text-purple-600 dark:text-purple-300">Все зарегистрированные пользователи</strong> — если отчет имеет настройку доступа "Все"</li>
            <li><strong class="text-purple-600 dark:text-purple-300">Только ваши друзья</strong> — если отчет имеет настройку "Только друзья"</li>
            <li><strong class="text-purple-600 dark:text-purple-300">Никто</strong> — если отчет имеет настройку "Никто" (приватный)</li>
        </ul>
        <p class="text-gray-700 dark:text-gray-200 mt-4 leading-relaxed">Настройки доступа к комментариям соответствуют настройкам доступа к самому отчету.</p>
        <div class="mt-4 p-4 bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-400 dark:border-yellow-600 rounded-r-lg">
            <p class="text-gray-700 dark:text-gray-200"><strong class="text-yellow-800 dark:text-yellow-300">Важно:</strong> Вы, как автор отчета, можете удалить любой комментарий к вашему отчету, независимо от того, кто его оставил. Это помогает поддерживать комфортную атмосферу в вашем дневнике.</p>
        </div>
        <p class="text-gray-700 dark:text-gray-200 mt-4 leading-relaxed">Незарегистрированные пользователи (гости) не могут оставлять комментарии — для этого требуется регистрация.</p>
    </div>
</div>';

    }

    /**
     * Категория 8: Поиск и навигация
     */
    private function getCategory8Content(): string
    {
        $questions = [
            'Как искать отчеты и сны?',
            'Как искать пользователей?',
            'Что такое теги и как их использовать?'
        ];
        
        return $this->formatQuestionsList($questions) . '

<div class="space-y-8">' . $this->formatQuestionHeader('question-1', 'Как искать отчеты и сны?') . '
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm">
        <p class="text-gray-700 dark:text-gray-200 mb-4 leading-relaxed">Для поиска отчетов и снов:</p>
        <ol class="list-decimal list-inside space-y-2 text-gray-700 dark:text-gray-200 ml-4">
            <li>Перейдите на страницу <a href="' . route('reports.search') . '" class="text-purple-600 dark:text-purple-400 hover:text-purple-800 dark:hover:text-purple-300 font-medium underline">Поиск</a> (обычно доступна через меню или поисковую строку)</li>
            <li>Введите ключевые слова в поле поиска:
                <ul class="list-disc list-inside space-y-1 text-gray-700 dark:text-gray-200 ml-6 mt-2">
                    <li>Текст из описания сна</li>
                    <li>Название сна</li>
                    <li>Любые слова, которые могут быть в отчете</li>
                </ul>
            </li>
            <li>Используйте фильтры (если доступны):
                <ul class="list-disc list-inside space-y-1 text-gray-700 dark:text-gray-200 ml-6 mt-2">
                    <li>По дате (период)</li>
                    <li>По типу сна</li>
                    <li>По автору</li>
                    <li>По тегам</li>
                </ul>
            </li>
            <li>Нажмите кнопку "Найти" или нажмите Enter</li>
            <li>Просмотрите результаты поиска</li>
        </ol>
        <p class="text-gray-700 dark:text-gray-200 mt-4 leading-relaxed">Поиск работает по всем публичным отчетам и отчетам, к которым у вас есть доступ (например, отчеты друзей).</p>
        <div class="mt-4 p-4 bg-green-50 dark:bg-green-900/20 border-l-4 border-green-400 dark:border-green-600 rounded-r-lg">
            <p class="text-gray-700 dark:text-gray-200"><strong class="text-green-800 dark:text-green-300">Совет:</strong> Используйте конкретные слова для более точных результатов. Поиск учитывает описание снов, названия и теги.</p>
        </div>
    </div>

' . $this->formatQuestionHeader('question-2', 'Как искать пользователей?') . '
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm">
        <p class="text-gray-700 dark:text-gray-200 mb-4 leading-relaxed">Для поиска пользователей:</p>
        <ol class="list-decimal list-inside space-y-2 text-gray-700 dark:text-gray-200 ml-4">
            <li>Используйте поисковую строку на сайте (если доступна функция поиска пользователей)</li>
            <li>Введите никнейм или имя пользователя</li>
            <li>Просмотрите результаты поиска</li>
            <li>Нажмите на пользователя, чтобы открыть его профиль</li>
        </ol>
        <p class="text-gray-700 dark:text-gray-200 mt-4 leading-relaxed">Вы также можете найти пользователей:</p>
        <ul class="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-200 ml-4">
            <li>Через комментарии к отчетам</li>
            <li>В ленте активности</li>
            <li>Через публичные дневники</li>
        </ul>
        <p class="text-gray-700 dark:text-gray-200 mt-4 leading-relaxed">После открытия профиля пользователя вы сможете просмотреть его публичный дневник, добавить в друзья (если хотите) или оставить комментарий к его отчетам.</p>
    </div>

' . $this->formatQuestionHeader('question-3', 'Что такое теги и как их использовать?') . '
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm">
        <p class="text-gray-700 dark:text-gray-200 mb-4 leading-relaxed"><strong class="text-purple-600 dark:text-purple-300">Теги</strong> — это ключевые слова, которые помогают организовать и категоризировать ваши отчеты о сновидениях.</p>
        <p class="text-gray-700 dark:text-gray-300 mb-4 leading-relaxed"><strong class="text-purple-600 dark:text-purple-300">Как использовать теги:</strong></p>
        <ol class="list-decimal list-inside space-y-2 text-gray-700 dark:text-gray-200 ml-4">
            <li>При создании или редактировании отчета найдите поле "Теги"</li>
            <li>Начните вводить название тега — система предложит существующие теги</li>
            <li>Выберите существующий тег из списка или создайте новый, введя его полностью</li>
            <li>Можно добавить несколько тегов к одному отчету</li>
            <li>Сохраните отчет</li>
        </ol>
        <p class="text-gray-700 dark:text-gray-200 mt-4 leading-relaxed"><strong class="text-purple-600 dark:text-purple-300">Примеры тегов:</strong></p>
        <ul class="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-200 ml-4">
            <li>Эмоции: страх, радость, тревога</li>
            <li>Места: дом, лес, море</li>
            <li>Люди: семья, друзья, незнакомцы</li>
            <li>События: полет, падение, погоня</li>
        </ul>
        <ul class="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300 ml-4 mt-2">
            <li>Быстрый поиск отчетов по темам</li>
            <li>Организация записей</li>
            <li>Поиск паттернов (какие теги часто встречаются вместе)</li>
            <li>Навигация по похожим снам</li>
        </ul>
        <p class="text-gray-700 dark:text-gray-200 mt-4 leading-relaxed">Используйте теги для лучшей организации вашего дневника и быстрого поиска нужных записей.</p>
    </div>
</div>';

    }

    /**
     * Категория 9: Техническая поддержка
     */
    private function getCategory9Content(string $telegramLink): string
    {
        $questions = [
            'Проблемы с загрузкой страницы',
            'Анализ не работает / ошибка',
            'Не приходит письмо подтверждения',
            'Как сменить тему (светлая/темная)?',
            'Как связаться с поддержкой?'
        ];
        
        return $this->formatQuestionsList($questions) . '

<div class="space-y-8">' . $this->formatQuestionHeader('question-1', 'Проблемы с загрузкой страницы') . '
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm">
        <p class="text-gray-700 dark:text-gray-200 mb-4 leading-relaxed">Если страница не загружается или загружается медленно:</p>
        <ol class="list-decimal list-inside space-y-2 text-gray-700 dark:text-gray-200 ml-4">
            <li><strong class="text-purple-600 dark:text-purple-300">Проверьте подключение к интернету</strong> — убедитесь, что у вас стабильное соединение</li>
            <li><strong class="text-purple-600 dark:text-purple-300">Обновите страницу</strong> — нажмите F5 или Ctrl+R (Cmd+R на Mac)</li>
            <li><strong class="text-purple-600 dark:text-purple-300">Очистите кэш браузера</strong> — иногда помогает очистка кэша и cookies</li>
            <li><strong class="text-purple-600 dark:text-purple-300">Попробуйте другой браузер</strong> — возможно, проблема в браузере</li>
            <li><strong class="text-purple-600 dark:text-purple-300">Отключите расширения браузера</strong> — некоторые расширения могут мешать загрузке</li>
            <li><strong class="text-purple-600 dark:text-purple-300">Проверьте, не блокирует ли антивирус</strong> — некоторые антивирусы блокируют сайты</li>
        </ol>
        <p class="text-gray-700 dark:text-gray-200 mt-4 leading-relaxed">Если проблема повторяется, это может быть временная проблема на сервере. Попробуйте зайти позже или обратитесь в <a href="' . $telegramLink . '" target="_blank" class="text-purple-600 dark:text-purple-400 hover:text-purple-800 dark:hover:text-purple-300 font-medium underline">поддержку</a>.</p>
    </div>

' . $this->formatQuestionHeader('question-2', 'Анализ не работает / ошибка') . '
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm">
        <p class="text-gray-700 dark:text-gray-200 mb-4 leading-relaxed">Если анализ снов не работает или выдает ошибку:</p>
        <ol class="list-decimal list-inside space-y-2 text-gray-700 dark:text-gray-200 ml-4">
            <li><strong class="text-purple-600 dark:text-purple-300">Проверьте подключение к интернету</strong> — анализ требует стабильного соединения</li>
            <li><strong class="text-purple-600 dark:text-purple-300">Попробуйте повторить анализ</strong> — нажмите кнопку "Повторить анализ" на странице с ошибкой</li>
            <li><strong class="text-purple-600 dark:text-purple-300">Проверьте длину текста</strong> — очень длинные описания (более 5000 символов) могут вызывать проблемы</li>
            <li><strong class="text-purple-600 dark:text-purple-300">Подождите несколько минут</strong> — возможно, сервер перегружен, попробуйте позже</li>
            <li><strong class="text-purple-600 dark:text-purple-300">Обновите страницу</strong> — иногда помогает перезагрузка</li>
        </ol>
        <p class="text-gray-700 dark:text-gray-200 mt-4 leading-relaxed">Если ошибка повторяется несколько раз подряд, это может указывать на техническую проблему. В этом случае:</p>
        <ul class="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-200 ml-4">
            <li>Сохраните текст ошибки (если он отображается)</li>
            <li>Запишите время, когда произошла ошибка</li>
            <li>Обратитесь в <a href="' . $telegramLink . '" target="_blank" class="text-purple-600 dark:text-purple-400 hover:text-purple-800 dark:hover:text-purple-300 font-medium underline">поддержку</a> с этой информацией</li>
        </ul>
    </div>

' . $this->formatQuestionHeader('question-3', 'Не приходит письмо подтверждения') . '
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm">
        <p class="text-gray-700 dark:text-gray-200 mb-4 leading-relaxed">Если письмо с подтверждением регистрации не приходит:</p>
        <ol class="list-decimal list-inside space-y-2 text-gray-700 dark:text-gray-200 ml-4">
            <li><strong class="text-purple-600 dark:text-purple-300">Проверьте папку "Спам"</strong> — письмо могло попасть туда</li>
            <li><strong class="text-purple-600 dark:text-purple-300">Проверьте правильность email</strong> — убедитесь, что вы ввели правильный адрес</li>
            <li><strong class="text-purple-600 dark:text-purple-300">Подождите несколько минут</strong> — письма иногда приходят с задержкой</li>
            <li><strong class="text-purple-600 dark:text-purple-300">Проверьте настройки почтового фильтра</strong> — возможно, письма блокируются</li>
            <li><strong class="text-purple-600 dark:text-purple-300">Попробуйте запросить повторную отправку</strong> — на странице входа обычно есть ссылка "Отправить письмо повторно"</li>
        </ol>
        <p class="text-gray-700 dark:text-gray-200 mt-4 leading-relaxed">Если письмо не пришло в течение часа:</p>
        <ul class="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-200 ml-4">
            <li>Проверьте, не заблокирован ли адрес отправителя</li>
            <li>Попробуйте использовать другой email адрес</li>
            <li>Обратитесь в <a href="' . $telegramLink . '" target="_blank" class="text-purple-600 dark:text-purple-400 hover:text-purple-800 dark:hover:text-purple-300 font-medium underline">поддержку</a> — мы поможем подтвердить аккаунт вручную</li>
        </ul>
    </div>

' . $this->formatQuestionHeader('question-4', 'Как сменить тему (светлая/темная)?') . '
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm">
        <p class="text-gray-700 dark:text-gray-200 mb-4 leading-relaxed">Для смены темы оформления сайта:</p>
        <ol class="list-decimal list-inside space-y-2 text-gray-700 dark:text-gray-200 ml-4">
            <li>Найдите переключатель темы на сайте (обычно в правом верхнем углу или в меню)</li>
            <li>Нажмите на иконку солнца/луны или переключатель</li>
            <li>Тема изменится автоматически</li>
        </ol>
        <p class="text-gray-700 dark:text-gray-200 mt-4 leading-relaxed">Доступны две темы:</p>
        <ul class="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-200 ml-4">
            <li><strong class="text-purple-600 dark:text-purple-300">Светлая тема</strong> — светлый фон, темный текст (по умолчанию)</li>
            <li><strong class="text-purple-600 dark:text-purple-300">Темная тема</strong> — темный фон, светлый текст (удобно для работы в темноте)</li>
        </ul>
        <p class="text-gray-700 dark:text-gray-200 mt-4 leading-relaxed">Ваш выбор темы сохраняется и будет применяться при следующих посещениях сайта.</p>
        <div class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-400 dark:border-blue-600 rounded-r-lg">
            <p class="text-gray-700 dark:text-gray-200"><strong class="text-blue-800 dark:text-blue-300">Примечание:</strong> Если переключатель темы не виден, возможно, он находится в меню пользователя или в настройках профиля.</p>
        </div>
    </div>

' . $this->formatQuestionHeader('question-5', 'Как связаться с поддержкой?') . '
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm">
        <p class="text-gray-700 dark:text-gray-200 mb-4 leading-relaxed">Если у вас возникли проблемы или вопросы, вы можете связаться с поддержкой:</p>
        <ul class="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-200 ml-4">
            <li><strong class="text-purple-600 dark:text-purple-300">Telegram:</strong> <a href="' . $telegramLink . '" target="_blank" class="text-purple-600 dark:text-purple-400 hover:text-purple-800 dark:hover:text-purple-300 font-medium underline">@snovidec_ru</a></li>
        </ul>
        <p class="text-gray-700 dark:text-gray-200 mt-4 leading-relaxed">При обращении в поддержку укажите:</p>
        <ul class="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-200 ml-4">
            <li>Ваш email или никнейм (если зарегистрированы)</li>
            <li>Описание проблемы</li>
            <li>Время, когда произошла проблема</li>
            <li>Скриншоты (если возможно)</li>
            <li>Текст ошибки (если есть)</li>
        </ul>
        <p class="text-gray-700 dark:text-gray-200 mt-4 leading-relaxed">Мы постараемся ответить как можно скорее и помочь решить вашу проблему.</p>
        <div class="mt-4 p-4 bg-green-50 dark:bg-green-900/20 border-l-4 border-green-400 dark:border-green-600 rounded-r-lg">
            <p class="text-gray-700 dark:text-gray-200"><strong class="text-green-800 dark:text-green-300">Время ответа:</strong> Обычно мы отвечаем в течение 24 часов. В рабочие дни ответ может прийти быстрее.</p>
        </div>
    </div>
</div>';

    }

    /**
     * Категория 10: Безопасность и приватность
     */
    private function getCategory10Content(): string
    {
        $questions = [
            'Кто видит мои отчеты?',
            'Как защитить свои данные?',
            'Что делать, если забыл пароль?',
            'Как удалить аккаунт?'
        ];
        
        return $this->formatQuestionsList($questions) . '

<div class="space-y-8">' . $this->formatQuestionHeader('question-1', 'Кто видит мои отчеты?') . '
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm">
        <p class="text-gray-700 dark:text-gray-200 mb-4 leading-relaxed">Видимость ваших отчетов зависит от настроек доступа:</p>
        <ul class="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-200 ml-4">
            <li><strong class="text-purple-600 dark:text-purple-300">Настройка "Все"</strong> — отчет виден всем посетителям сайта (публичный)</li>
            <li><strong class="text-purple-600 dark:text-purple-300">Настройка "Только друзья"</strong> — отчет виден только вашим друзьям</li>
            <li><strong class="text-purple-600 dark:text-purple-300">Настройка "Никто"</strong> — отчет виден только вам (приватный)</li>
        </ul>
        <p class="text-gray-700 dark:text-gray-200 mt-4 leading-relaxed">Также на видимость влияют настройки приватности дневника:</p>
        <ul class="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-200 ml-4">
            <li>Если дневник <strong class="text-purple-600 dark:text-purple-300">приватный</strong> — даже публичные отчеты не будут видны в публичных лентах</li>
            <li>Если дневник <strong class="text-purple-600 dark:text-purple-300">публичный</strong> — публичные отчеты видны всем</li>
            <li>Если дневник <strong class="text-purple-600 dark:text-purple-300">"Только друзья"</strong> — отчеты видны только друзьям</li>
        </ul>
        <div class="mt-4 p-4 bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-400 dark:border-yellow-600 rounded-r-lg">
            <p class="text-gray-700 dark:text-gray-200"><strong class="text-yellow-800 dark:text-yellow-300">Важно:</strong> Настройки доступа к отчету имеют приоритет над настройками дневника. Даже если дневник приватный, вы можете сделать отдельные отчеты публичными.</p>
        </div>
        <p class="text-gray-700 dark:text-gray-200 mt-4 leading-relaxed">Администраторы платформы имеют доступ ко всем отчетам для модерации и технической поддержки.</p>
    </div>

' . $this->formatQuestionHeader('question-2', 'Как защитить свои данные?') . '
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm">
        <p class="text-gray-700 dark:text-gray-200 mb-4 leading-relaxed">Для защиты ваших данных следуйте этим рекомендациям:</p>
        <ol class="list-decimal list-inside space-y-2 text-gray-700 dark:text-gray-200 ml-4">
            <li><strong class="text-purple-600 dark:text-purple-300">Используйте надежный пароль</strong>:
                <ul class="list-disc list-inside space-y-1 text-gray-700 dark:text-gray-200 ml-6 mt-2">
                    <li>Минимум 8 символов</li>
                    <li>Комбинация букв, цифр и символов</li>
                    <li>Не используйте простые пароли (123456, password и т.д.)</li>
                    <li>Не используйте один пароль для разных сервисов</li>
                </ul>
            </li>
            <li><strong class="text-purple-600 dark:text-purple-300">Настройте приватность</strong>:
                <ul class="list-disc list-inside space-y-1 text-gray-700 dark:text-gray-200 ml-6 mt-2">
                    <li>Установите дневник как приватный, если не хотите делиться</li>
                    <li>Используйте настройку "Никто" для личных отчетов</li>
                    <li>Регулярно проверяйте настройки доступа</li>
                </ul>
            </li>
            <li><strong class="text-purple-600 dark:text-purple-300">Не делитесь паролем</strong> — никогда не сообщайте пароль другим людям</li>
            <li><strong class="text-purple-600 dark:text-purple-300">Выходите из аккаунта</strong> — особенно на чужих компьютерах</li>
            <li><strong class="text-purple-600 dark:text-purple-300">Проверяйте активность</strong> — если заметите подозрительную активность, смените пароль</li>
        </ol>
        <p class="text-gray-700 dark:text-gray-200 mt-4 leading-relaxed">Платформа использует современные методы шифрования для защиты ваших данных. Ваши пароли хранятся в зашифрованном виде и недоступны даже администраторам.</p>
    </div>

' . $this->formatQuestionHeader('question-3', 'Что делать, если забыл пароль?') . '
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm">
        <p class="text-gray-700 dark:text-gray-200 mb-4 leading-relaxed">Если вы забыли пароль:</p>
        <ol class="list-decimal list-inside space-y-2 text-gray-700 dark:text-gray-200 ml-4">
            <li>Перейдите на страницу входа</li>
            <li>Нажмите ссылку <strong class="text-purple-600 dark:text-purple-300">"Забыли пароль?"</strong> или "Восстановить пароль"</li>
            <li>Введите ваш email адрес (тот, который использовали при регистрации)</li>
            <li>Нажмите "Отправить"</li>
            <li>Проверьте вашу почту — вам придет письмо со ссылкой для сброса пароля</li>
            <li>Перейдите по ссылке из письма</li>
            <li>Введите новый пароль (дважды для подтверждения)</li>
            <li>Сохраните новый пароль</li>
        </ol>
        <div class="mt-4 p-4 bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-400 dark:border-yellow-600 rounded-r-lg">
            <p class="text-gray-700 dark:text-gray-200"><strong class="text-yellow-800 dark:text-yellow-300">Важно:</strong></p>
            <ul class="list-disc list-inside space-y-1 text-gray-700 dark:text-gray-300 ml-4 mt-2">
                <li>Ссылка для сброса пароля действительна ограниченное время (обычно 1 час)</li>
                <li>Если письмо не пришло, проверьте папку "Спам"</li>
                <li>Если email не подтвержден, восстановление пароля может быть недоступно</li>
            </ul>
        </div>
        <p class="text-gray-700 dark:text-gray-200 mt-4 leading-relaxed">Если у вас проблемы с восстановлением пароля, обратитесь в <a href="https://t.me/snovidec_ru" target="_blank" class="text-purple-600 dark:text-purple-400 hover:text-purple-800 dark:hover:text-purple-300 font-medium underline">поддержку</a>.</p>
    </div>

' . $this->formatQuestionHeader('question-4', 'Как удалить аккаунт?') . '
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700 shadow-sm">
        <p class="text-gray-700 dark:text-gray-200 mb-4 leading-relaxed">Для удаления аккаунта:</p>
        <ol class="list-decimal list-inside space-y-2 text-gray-700 dark:text-gray-200 ml-4">
            <li>Войдите в свой аккаунт</li>
            <li>Перейдите в настройки профиля</li>
            <li>Найдите раздел "Удаление аккаунта" или "Опасная зона"</li>
            <li>Нажмите кнопку <strong class="text-purple-600 dark:text-purple-300">"Удалить аккаунт"</strong></li>
            <li>Подтвердите удаление (обычно требуется ввести пароль или подтвердить действие)</li>
        </ol>
        <div class="mt-4 p-4 bg-red-50 dark:bg-red-900/20 border-l-4 border-red-400 dark:border-red-600 rounded-r-lg">
            <p class="text-gray-700 dark:text-gray-200"><strong class="text-red-800 dark:text-red-300">Внимание!</strong> Удаление аккаунта необратимо и приведет к:</p>
            <ul class="list-disc list-inside space-y-1 text-gray-700 dark:text-gray-300 ml-4 mt-2">
                <li>Удалению всех ваших отчетов и снов</li>
                <li>Удалению всех комментариев</li>
                <li>Удалению всех данных профиля</li>
                <li>Разрыву всех связей с друзьями</li>
            </ul>
        </div>
        <p class="text-gray-700 dark:text-gray-200 mt-4 leading-relaxed">Перед удалением аккаунта убедитесь, что вы действительно хотите это сделать. Если вы просто хотите скрыть свой дневник, лучше измените настройки приватности на "Приватный" вместо удаления аккаунта.</p>
        <p class="text-gray-700 dark:text-gray-200 mt-4 leading-relaxed">Если у вас проблемы с удалением аккаунта или вы хотите восстановить удаленный аккаунт, обратитесь в <a href="https://t.me/snovidec_ru" target="_blank" class="text-purple-600 dark:text-purple-400 hover:text-purple-800 dark:hover:text-purple-300 font-medium underline">поддержку</a>.</p>
    </div>
</div>';

    }
}
