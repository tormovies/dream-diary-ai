<!-- Быстрые действия (для неавторизованных) -->
<div class="sidebar-menu bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700">
    <h3 class="text-lg font-semibold mb-4 text-purple-600 dark:text-purple-400 flex items-center gap-2">
        <i class="fas fa-bolt"></i> Быстрые действия
    </h3>
    <nav class="space-y-2">
        <a href="{{ route('dream-analyzer.create') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-purple-600 dark:hover:text-purple-400 transition-all {{ request()->routeIs('dream-analyzer.*') ? 'bg-gray-100 dark:bg-gray-700 text-purple-600 dark:text-purple-400 font-medium' : '' }}">
            <i class="fas fa-magic w-5"></i> Толкование снов
        </a>
        <a href="{{ route('reports.search') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-purple-600 dark:hover:text-purple-400 transition-all {{ request()->routeIs('reports.search') ? 'bg-gray-100 dark:bg-gray-700 text-purple-600 dark:text-purple-400 font-medium' : '' }}">
            <i class="fas fa-search w-5"></i> Поиск
        </a>
        <a href="{{ route('activity.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-purple-600 dark:hover:text-purple-400 transition-all {{ request()->routeIs('activity.*') ? 'bg-gray-100 dark:bg-gray-700 text-purple-600 dark:text-purple-400 font-medium' : '' }}">
            <i class="fas fa-home w-5"></i> Лента активности
        </a>
        <a href="{{ route('register') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-purple-600 dark:hover:text-purple-400 transition-all">
            <i class="fas fa-user-plus w-5"></i> Регистрация
        </a>
        <a href="{{ route('login') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-purple-600 dark:hover:text-purple-400 transition-all">
            <i class="fas fa-sign-in-alt w-5"></i> Войти
        </a>
    </nav>
</div>

















