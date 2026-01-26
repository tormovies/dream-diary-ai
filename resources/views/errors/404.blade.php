@extends('layouts.base')

@section('title', 'Ошибка 404 — Страница не найдена')

@section('content')
    <div class="min-h-screen flex items-center justify-center px-4 py-12">
        <div class="max-w-2xl w-full">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl p-8 md:p-12 border border-gray-200 dark:border-gray-700">
                <!-- Иконка -->
                <div class="flex justify-center mb-6">
                    <div class="w-24 h-24 bg-amber-100 dark:bg-amber-900/30 rounded-full flex items-center justify-center">
                        <i class="fas fa-search text-4xl text-amber-600 dark:text-amber-400" aria-hidden="true"></i>
                    </div>
                </div>
                
                <!-- Заголовок -->
                <h1 class="text-3xl font-bold text-center text-gray-900 dark:text-white mb-4">
                    Страница не найдена
                </h1>
                
                <!-- Сообщение -->
                <div class="text-center mb-8">
                    <p class="text-lg text-gray-700 dark:text-gray-300 mb-4">
                        Возможно, ссылка устарела или в адресе опечатка.
                    </p>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                        Перейдите на главную или воспользуйтесь ссылками ниже.
                    </p>
                    
                    <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center flex-wrap">
                        <a href="{{ route('home') }}" 
                           class="inline-flex items-center justify-center px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg transition-colors">
                            <i class="fas fa-home mr-2" aria-hidden="true"></i>
                            На главную
                        </a>
                        <a href="{{ route('dream-analyzer.create') }}" 
                           class="inline-flex items-center justify-center px-6 py-3 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 font-semibold rounded-lg transition-colors">
                            <i class="fas fa-magic mr-2" aria-hidden="true"></i>
                            Толкование снов
                        </a>
                        <a href="{{ route('reports.search') }}" 
                           class="inline-flex items-center justify-center px-6 py-3 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 font-semibold rounded-lg transition-colors">
                            <i class="fas fa-search mr-2" aria-hidden="true"></i>
                            Поиск
                        </a>
                        <a href="{{ route('guide.index') }}" 
                           class="inline-flex items-center justify-center px-6 py-3 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 font-semibold rounded-lg transition-colors">
                            <i class="fas fa-list mr-2" aria-hidden="true"></i>
                            Инструкции
                        </a>
                        @guest
                            <a href="{{ route('login') }}" 
                               class="inline-flex items-center justify-center px-6 py-3 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 font-semibold rounded-lg transition-colors">
                                <i class="fas fa-sign-in-alt mr-2" aria-hidden="true"></i>
                                Войти
                            </a>
                            <a href="{{ route('register') }}" 
                               class="inline-flex items-center justify-center px-6 py-3 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 font-semibold rounded-lg transition-colors">
                                <i class="fas fa-user-plus mr-2" aria-hidden="true"></i>
                                Регистрация
                            </a>
                        @endguest
                    </div>
                </div>
                
                <!-- Дополнительная информация -->
                <div class="mt-8 pt-8 border-t border-gray-200 dark:border-gray-700">
                    <p class="text-center text-sm text-gray-500 dark:text-gray-400">
                        Ошибка 404: Страница не найдена
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection
