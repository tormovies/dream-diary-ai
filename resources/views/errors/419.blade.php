@extends('layouts.base')

@section('content')
    <div class="min-h-screen flex items-center justify-center px-4 py-12">
        <div class="max-w-2xl w-full">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl p-8 md:p-12 border border-gray-200 dark:border-gray-700">
                <!-- Иконка -->
                <div class="flex justify-center mb-6">
                    <div class="w-24 h-24 bg-yellow-100 dark:bg-yellow-900/30 rounded-full flex items-center justify-center">
                        <svg class="w-12 h-12 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                
                <!-- Заголовок -->
                <h1 class="text-3xl font-bold text-center text-gray-900 dark:text-white mb-4">
                    Страница устарела
                </h1>
                
                <!-- Сообщение -->
                <div class="text-center mb-8">
                    <p class="text-lg text-gray-700 dark:text-gray-300 mb-4">
                        Ваша сессия истекла или вы вышли из аккаунта на другой вкладке браузера.
                    </p>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                        Страница автоматически обновится через <span id="countdown">3</span> секунд...
                    </p>
                    
                    <!-- Индикатор загрузки -->
                    <div class="flex justify-center mb-6">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-purple-600"></div>
                    </div>
                    
                    <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center">
                        <button onclick="location.reload()" 
                               class="inline-flex items-center justify-center px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg transition-colors">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Обновить сейчас
                        </button>
                        <a href="{{ route('home') }}" 
                           class="inline-flex items-center justify-center px-6 py-3 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 font-semibold rounded-lg transition-colors">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                            </svg>
                            На главную
                        </a>
                    </div>
                </div>
                
                <!-- Дополнительная информация -->
                <div class="mt-8 pt-8 border-t border-gray-200 dark:border-gray-700">
                    <p class="text-center text-sm text-gray-500 dark:text-gray-400">
                        Ошибка 419: Страница устарела (CSRF токен истек)
                    </p>
                    <p class="text-center text-xs text-gray-400 dark:text-gray-500 mt-2">
                        Это может произойти, если вы долго находились на странице или вышли из аккаунта на другой вкладке
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Обратный отсчет перед автоматическим обновлением
        let countdown = 3;
        const countdownElement = document.getElementById('countdown');
        
        const countdownInterval = setInterval(function() {
            countdown--;
            if (countdownElement) {
                countdownElement.textContent = countdown;
            }
            
            if (countdown <= 0) {
                clearInterval(countdownInterval);
                // Автоматическое обновление страницы
                location.reload();
            }
        }, 1000);
        
        // Обновляем страницу при клике на кнопку "Обновить сейчас"
        // (уже обработано через onclick="location.reload()")
    </script>
@endsection
