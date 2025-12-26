<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" 
      x-data="{ theme: 'light' }"
      x-bind:class="{ 'dark': theme === 'dark' }"
      x-init="
        const savedTheme = localStorage.getItem('theme') || 'light';
        theme = savedTheme;
        if (savedTheme === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
      ">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Профиль - {{ config('app.name', 'Дневник сновидений') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
        <style>
            .gradient-primary {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            }
            .card-shadow {
                box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            }
            .dark .card-shadow {
                box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
            }
            .profile-grid {
                display: grid;
                grid-template-columns: 1fr;
                gap: 1.5rem;
                width: 100%;
            }
            @media (min-width: 1024px) {
                .profile-grid {
                    grid-template-columns: 280px 1fr;
                    gap: 2rem;
                }
            }
            @media (min-width: 1400px) {
                .profile-grid {
                    grid-template-columns: 320px 1fr;
                    gap: 2.5rem;
                }
            }
            .sidebar-menu {
                display: none;
            }
            @media (min-width: 1024px) {
                .sidebar-menu {
                    display: block;
                }
            }
            /* Стили форм из примера */
            .profile-form-section {
                background-color: white;
                border-radius: 15px;
                padding: 30px;
                box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
                border: 1px solid #dee2e6;
            }
            .dark .profile-form-section {
                background-color: #1a1a2e;
                border-color: #343a40;
                box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
            }
            .form-section-title {
                font-size: 1.4rem;
                margin-bottom: 25px;
                color: #4263eb;
                display: flex;
                align-items: center;
                gap: 10px;
                padding-bottom: 15px;
                border-bottom: 1px solid #dee2e6;
                font-weight: 600;
            }
            .dark .form-section-title {
                color: #748ffc;
                border-bottom-color: #343a40;
            }
            .profile-form {
                display: flex;
                flex-direction: column;
                gap: 20px;
            }
            .form-row {
                display: grid;
                grid-template-columns: 1fr;
                gap: 20px;
            }
            @media (min-width: 768px) {
                .form-row {
                    grid-template-columns: 1fr 1fr;
                }
            }
            .form-group {
                display: flex;
                flex-direction: column;
                gap: 8px;
            }
            .form-label {
                font-weight: 600;
                color: #212529;
                display: flex;
                align-items: center;
                gap: 8px;
            }
            .dark .form-label {
                color: #f8f9fa;
            }
            .form-label.required:after {
                content: '*';
                color: #fa5252;
                margin-left: 4px;
            }
            .form-input, .form-select, .form-textarea {
                padding: 14px 18px;
                border-radius: 10px;
                border: 1px solid #dee2e6;
                background-color: white;
                color: #212529;
                font-family: inherit;
                font-size: 1rem;
                transition: all 0.2s;
                width: 100%;
            }
            .form-input:focus, .form-select:focus, .form-textarea:focus {
                outline: none;
                border-color: #4263eb;
                box-shadow: 0 0 0 3px rgba(116, 143, 252, 0.2);
            }
            .dark .form-input, .dark .form-select, .dark .form-textarea {
                background-color: #2d2d44;
                border-color: #343a40;
                color: #f8f9fa;
            }
            .dark .form-input:focus, .dark .form-select:focus, .dark .form-textarea:focus {
                border-color: #748ffc;
            }
            .form-textarea {
                min-height: 120px;
                resize: vertical;
            }
            .form-hint {
                font-size: 0.85rem;
                color: #495057;
                margin-top: 5px;
            }
            .dark .form-hint {
                color: #adb5bd;
            }
            .password-container {
                position: relative;
                width: 100%;
            }
            .toggle-password {
                position: absolute;
                right: 15px;
                top: 50%;
                transform: translateY(-50%);
                background: none;
                border: none;
                color: #495057;
                cursor: pointer;
                font-size: 1.1rem;
            }
            .dark .toggle-password {
                color: #adb5bd;
            }
            .form-checkbox {
                display: flex;
                align-items: flex-start;
                gap: 10px;
            }
            .checkbox-input {
                margin-top: 3px;
                cursor: pointer;
            }
            .checkbox-label {
                font-size: 0.95rem;
                color: #495057;
                line-height: 1.4;
            }
            .dark .checkbox-label {
                color: #adb5bd;
            }
            .form-actions {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-top: 30px;
                padding-top: 20px;
                border-top: 1px solid #dee2e6;
            }
            .dark .form-actions {
                border-top-color: #343a40;
            }
            .btn-form-primary {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
                padding: 12px 24px;
                border-radius: 8px;
                border: none;
                font-weight: 600;
                cursor: pointer;
                font-size: 1rem;
                transition: all 0.2s;
            }
            .btn-form-primary:hover {
                transform: translateY(-2px);
                box-shadow: 0 6px 18px rgba(102, 126, 234, 0.4);
            }
            .btn-form-secondary {
                background-color: transparent;
                color: #495057;
                border: 2px solid #dee2e6;
                padding: 12px 24px;
                border-radius: 8px;
                font-weight: 600;
                cursor: pointer;
                font-size: 1rem;
                transition: all 0.2s;
                text-decoration: none;
                display: inline-block;
            }
            .dark .btn-form-secondary {
                color: #adb5bd;
                border-color: #343a40;
            }
            .btn-form-secondary:hover {
                background-color: #f8f9fa;
            }
            .dark .btn-form-secondary:hover {
                background-color: #2d2d44;
            }
            .btn-form-danger {
                background-color: transparent;
                color: #fa5252;
                border: 2px solid #fa5252;
                padding: 12px 24px;
                border-radius: 8px;
                font-weight: 600;
                cursor: pointer;
                font-size: 1rem;
                transition: all 0.2s;
            }
            .dark .btn-form-danger {
                color: #ff8787;
                border-color: #ff8787;
            }
            .btn-form-danger:hover {
                background-color: #fa5252;
                color: white;
                transform: translateY(-2px);
            }
            .dark .btn-form-danger:hover {
                background-color: #ff8787;
            }
            .delete-section {
                background-color: rgba(250, 82, 82, 0.1);
                border: 1px solid rgba(250, 82, 82, 0.3);
            }
            .dark .delete-section {
                background-color: rgba(255, 135, 135, 0.1);
                border-color: rgba(255, 135, 135, 0.3);
            }
            .delete-warning {
                color: #fa5252;
                font-size: 1.1rem;
                margin-bottom: 15px;
                display: flex;
                align-items: center;
                gap: 10px;
                font-weight: 600;
            }
            .dark .delete-warning {
                color: #ff8787;
            }
            .delete-description {
                color: #495057;
                margin-bottom: 20px;
                line-height: 1.5;
            }
            .dark .delete-description {
                color: #adb5bd;
            }
            .delete-confirm {
                display: flex;
                align-items: center;
                gap: 10px;
                margin-bottom: 20px;
            }
            .delete-section .form-section-title {
                color: #fa5252;
            }
            .dark .delete-section .form-section-title {
                color: #ff8787;
            }
        </style>
        <x-header-styles />
    </head>
    <body class="font-sans antialiased bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100">
        <x-header />

        <!-- Основной контент -->
        <div class="w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="profile-grid w-full">
                <!-- Левая панель -->
                <aside class="space-y-6">
                    <!-- Приветственная карточка -->
                    <div class="gradient-primary rounded-2xl p-6 text-white card-shadow">
                        <h3 class="text-xl font-bold mb-2">Добро пожаловать, {{ auth()->user()->nickname }}!</h3>
                        <p class="text-purple-100 mb-4 text-sm">
                            Управляйте настройками своего профиля
                        </p>
                        <a href="{{ route('reports.create') }}" class="inline-block bg-white text-purple-600 px-4 py-2 rounded-lg font-semibold hover:bg-purple-50 transition-colors text-sm">
                            <i class="fas fa-plus mr-2"></i>Добавить сон
                        </a>
                    </div>
                    
                    <!-- Карточка пользователя -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700">
                        <div class="text-center">
                            <div class="flex justify-center">
                                <x-avatar :user="auth()->user()" size="lg" />
                            </div>
                            <div class="mt-4">
                                <div class="font-semibold text-lg text-gray-900 dark:text-white">{{ auth()->user()->nickname }}</div>
                                <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                    @if($userStats)
                                        {{ $userStats['reports'] }} {{ $userStats['reports'] == 1 ? 'запись' : ($userStats['reports'] < 5 ? 'записи' : 'записей') }}
                                    @else
                                        Пользователь
                                    @endif
                                </div>
                            </div>
                            
                            @if($userStats)
                            <div class="flex justify-between mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                                <div class="text-center flex-1">
                                    <div class="text-xl font-bold text-purple-600 dark:text-purple-400">{{ $userStats['friends'] }}</div>
                                    <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">друзей</div>
                                </div>
                                <div class="text-center flex-1">
                                    <div class="text-xl font-bold text-purple-600 dark:text-purple-400">{{ $userStats['dreams'] }}</div>
                                    <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">снов</div>
                                </div>
                                <div class="text-center flex-1">
                                    <div class="text-xl font-bold text-purple-600 dark:text-purple-400">{{ $userStats['avg_per_month'] }}</div>
                                    <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">снов/мес</div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Быстрое меню -->
                    <div class="sidebar-menu bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold mb-4 text-purple-600 dark:text-purple-400 flex items-center gap-2">
                            <i class="fas fa-bars"></i> Меню
                        </h3>
                        <nav class="space-y-2">
                            <a href="{{ route('dream-analyzer.create') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-purple-600 dark:hover:text-purple-400 transition-all {{ request()->routeIs('dream-analyzer.*') ? 'bg-gray-100 dark:bg-gray-700 text-purple-600 dark:text-purple-400 font-medium' : '' }}">
                                <i class="fas fa-magic w-5"></i> Толкование снов
                            </a>
                            <a href="{{ route('activity.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-purple-600 dark:hover:text-purple-400 transition-all {{ request()->routeIs('activity.*') ? 'bg-gray-100 dark:bg-gray-700 text-purple-600 dark:text-purple-400 font-medium' : '' }}">
                                <i class="fas fa-home w-5"></i> Лента активности
                            </a>
                            <a href="{{ route('reports.create') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-purple-600 dark:hover:text-purple-400 transition-all {{ request()->routeIs('reports.create') ? 'bg-gray-100 dark:bg-gray-700 text-purple-600 dark:text-purple-400 font-medium' : '' }}">
                                <i class="fas fa-plus-circle w-5"></i> Добавить сон
                            </a>
                            <a href="{{ route('statistics.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-purple-600 dark:hover:text-purple-400 transition-all {{ request()->routeIs('statistics.*') ? 'bg-gray-100 dark:bg-gray-700 text-purple-600 dark:text-purple-400 font-medium' : '' }}">
                                <i class="fas fa-chart-pie w-5"></i> Статистика
                            </a>
                            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-purple-600 dark:hover:text-purple-400 transition-all {{ request()->routeIs('dashboard') ? 'bg-gray-100 dark:bg-gray-700 text-purple-600 dark:text-purple-400 font-medium' : '' }}">
                                <i class="fas fa-calendar-alt w-5"></i> Мои отчеты
                            </a>
                            <a href="{{ route('users.search') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-purple-600 dark:hover:text-purple-400 transition-all {{ request()->routeIs('users.search') ? 'bg-gray-100 dark:bg-gray-700 text-purple-600 dark:text-purple-400 font-medium' : '' }}">
                                <i class="fas fa-user-friends w-5"></i> Мои друзья
                            </a>
                            <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-purple-600 dark:hover:text-purple-400 transition-all {{ request()->routeIs('profile.*') ? 'bg-gray-100 dark:bg-gray-700 text-purple-600 dark:text-purple-400 font-medium' : '' }}">
                                <i class="fas fa-cog w-5"></i> Настройки
                            </a>
                        </nav>
                    </div>
                </aside>
                
                <!-- Центрально-правая панель -->
                <main class="space-y-6 min-w-0">
                    <!-- Заголовок -->
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700">
                        <h2 class="text-2xl font-bold text-purple-600 dark:text-purple-400">Настройки профиля</h2>
                    </div>

                    <!-- Информация профиля -->
                    <div class="profile-form-section card-shadow">
                        @include('profile.partials.update-profile-information-form')
                    </div>

                    <!-- Обновление пароля -->
                    <div class="profile-form-section card-shadow">
                        @include('profile.partials.update-password-form')
                    </div>

                    <!-- Удаление аккаунта -->
                    <div class="profile-form-section delete-section card-shadow">
                        @include('profile.partials.delete-user-form')
                    </div>
                </main>
            </div>
        </div>

        <script>
            function toggleTheme() {
                const html = document.documentElement;
                const currentTheme = html.classList.contains('dark') ? 'dark' : 'light';
                const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                
                if (newTheme === 'dark') {
                    html.classList.add('dark');
                } else {
                    html.classList.remove('dark');
                }
                
                localStorage.setItem('theme', newTheme);
            }

            function copyPublicLink() {
                const linkInput = document.getElementById('public-link');
                if (linkInput) {
                    linkInput.select();
                    document.execCommand('copy');
                    alert('Ссылка скопирована в буфер обмена');
                }
            }
        </script>
    </body>
</html>
