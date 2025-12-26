<!-- Навигация -->
<header x-data="{ mobileMenuOpen: false }" class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 sticky top-0 z-50 card-shadow">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <nav class="flex justify-between items-center py-4">
            <div class="flex items-center gap-6">
                <a href="{{ route('home') }}" class="text-2xl font-bold text-purple-600 dark:text-purple-400 flex items-center gap-2">
                    <i class="fas fa-moon text-purple-600 dark:text-purple-400"></i>
                    <span>{{ config('seo.site_name', 'Дневник сновидений') }}</span>
                </a>
            </div>
            
            <div class="flex items-center gap-3">
                <button onclick="toggleTheme()" 
                        class="w-10 h-10 rounded-full flex items-center justify-center text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                        title="Переключить тему">
                    <i id="theme-toggle-light-icon" class="fas fa-sun hidden dark:block"></i>
                    <i id="theme-toggle-dark-icon" class="fas fa-moon block dark:hidden"></i>
                </button>
                
                @auth
                    <div class="hidden md:flex items-center gap-3">
                        <x-avatar :user="auth()->user()" size="sm" />
                    </div>
                @else
                    <div class="hidden md:flex items-center gap-3">
                        <a href="{{ route('login') }}" class="text-sm text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white px-4 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                            Войти
                        </a>
                        <a href="{{ route('register') }}" class="text-sm bg-gradient-to-r from-purple-600 to-indigo-600 text-white px-4 py-2 rounded-lg hover:shadow-lg transition-all">
                            Регистрация
                        </a>
                    </div>
                @endauth
                
                <!-- Кнопка меню-гамбургер (видна всегда) -->
                <button @click="mobileMenuOpen = !mobileMenuOpen" 
                        class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-700 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': mobileMenuOpen, 'inline-flex': !mobileMenuOpen}" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': !mobileMenuOpen, 'inline-flex': mobileMenuOpen}" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </nav>
        
        <!-- Меню (горизонтальное на ПК, вертикальное на мобильных) -->
        <div x-show="mobileMenuOpen" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="border-t border-gray-200 dark:border-gray-700 pt-4 pb-3">
            <!-- Горизонтальное меню для ПК -->
            <div class="desktop-menu-horizontal flex items-center gap-4 flex-wrap">
                @auth
                    <a href="{{ route('notifications.index') }}" class="px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-md transition-colors">
                        <i class="fas fa-user mr-2"></i>Профиль
                    </a>
                    <a href="{{ route('dashboard') }}" class="px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-md transition-colors">
                        <i class="fas fa-book mr-2"></i>Мой дневник
                    </a>
                @endauth
                <a href="{{ route('dream-analyzer.create') }}" class="px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-md transition-colors">
                    <i class="fas fa-magic mr-2"></i>Толкование снов
                </a>
                <a href="{{ route('reports.search') }}" class="px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-md transition-colors">
                    <i class="fas fa-search mr-2"></i>Поиск
                </a>
                @auth
                    <a href="{{ route('statistics.index') }}" class="px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-md transition-colors">
                        <i class="fas fa-chart-bar mr-2"></i>Статистика
                    </a>
                    <a href="{{ route('users.search') }}" class="px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-md transition-colors">
                        <i class="fas fa-user-friends mr-2"></i>Сообщество
                    </a>
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-md transition-colors">
                            <i class="fas fa-cog mr-2"></i>Админ-панель
                        </a>
                    @endif
                    <a href="{{ route('profile.edit') }}" class="px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-md transition-colors">
                        <i class="fas fa-user mr-2"></i>Настройки
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-md transition-colors">
                            <i class="fas fa-sign-out-alt mr-2"></i>Выйти
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-md transition-colors">
                        <i class="fas fa-sign-in-alt mr-2"></i>Войти
                    </a>
                    <a href="{{ route('register') }}" class="px-3 py-2 text-sm font-medium text-purple-600 dark:text-purple-400 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-md transition-colors">
                        <i class="fas fa-user-plus mr-2"></i>Регистрация
                    </a>
                @endauth
            </div>
            
            <!-- Вертикальное меню для мобильных -->
            <div class="mobile-menu-vertical space-y-1">
                @auth
                    <a href="{{ route('notifications.index') }}" class="block px-3 py-2 text-base font-medium text-gray-700 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-md transition-colors">
                        <i class="fas fa-user mr-2"></i>Профиль
                    </a>
                    <a href="{{ route('dashboard') }}" class="block px-3 py-2 text-base font-medium text-gray-700 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-md transition-colors">
                        <i class="fas fa-book mr-2"></i>Мой дневник
                    </a>
                @endauth
                <a href="{{ route('dream-analyzer.create') }}" class="block px-3 py-2 text-base font-medium text-gray-700 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-md transition-colors">
                    <i class="fas fa-magic mr-2"></i>Толкование снов
                </a>
                @auth
                    <a href="{{ route('statistics.index') }}" class="block px-3 py-2 text-base font-medium text-gray-700 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-md transition-colors">
                        <i class="fas fa-chart-bar mr-2"></i>Статистика
                    </a>
                    <a href="{{ route('reports.search') }}" class="block px-3 py-2 text-base font-medium text-gray-700 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-md transition-colors">
                        <i class="fas fa-search mr-2"></i>Поиск
                    </a>
                    <a href="{{ route('users.search') }}" class="block px-3 py-2 text-base font-medium text-gray-700 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-md transition-colors">
                        <i class="fas fa-user-friends mr-2"></i>Сообщество
                    </a>
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2 text-base font-medium text-gray-700 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-md transition-colors">
                            <i class="fas fa-cog mr-2"></i>Админ-панель
                        </a>
                    @endif
                    <div class="border-t border-gray-200 dark:border-gray-700 mt-3 pt-3">
                        <a href="{{ route('profile.edit') }}" class="block px-3 py-2 text-base font-medium text-gray-700 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-md transition-colors">
                            <i class="fas fa-user mr-2"></i>Настройки
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left block px-3 py-2 text-base font-medium text-gray-700 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-md transition-colors">
                                <i class="fas fa-sign-out-alt mr-2"></i>Выйти
                            </button>
                        </form>
                    </div>
                @else
                    <div class="border-t border-gray-200 dark:border-gray-700 mt-3 pt-3">
                        <a href="{{ route('login') }}" class="block px-3 py-2 text-base font-medium text-gray-700 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-md transition-colors">
                            <i class="fas fa-sign-in-alt mr-2"></i>Войти
                        </a>
                        <a href="{{ route('register') }}" class="block px-3 py-2 text-base font-medium text-purple-600 dark:text-purple-400 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-md transition-colors">
                            <i class="fas fa-user-plus mr-2"></i>Регистрация
                        </a>
                    </div>
                @endauth
            </div>
        </div>
    </div>
</header>



