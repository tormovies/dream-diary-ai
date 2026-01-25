@extends('layouts.base')

@section('content')
    <div class="min-h-screen flex items-center justify-center px-4 py-12">
        <div class="max-w-2xl w-full">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl p-8 md:p-12 border border-gray-200 dark:border-gray-700">
                <!-- Иконка -->
                <div class="flex justify-center mb-6">
                    <div class="w-24 h-24 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center">
                        <svg class="w-12 h-12 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </div>
                </div>
                
                <!-- Заголовок -->
                <h1 class="text-3xl font-bold text-center text-gray-900 dark:text-white mb-4">
                    Доступ ограничен
                </h1>
                
                <!-- Сообщение -->
                @php
                    $reason = session('access_reason', 'default');
                    $ownerName = session('owner_name');
                    $ownerId = session('owner_id');
                @endphp
                
                <div class="text-center mb-8">
                    @if($reason === 'not_authenticated')
                        {{-- Неавторизованный пользователь - дневник/отчет может быть доступен после входа --}}
                        <p class="text-lg text-gray-700 dark:text-gray-300 mb-4">
                            Этот контент доступен только авторизованным пользователям
                        </p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Войдите в систему, чтобы просмотреть этот дневник или отчет
                        </p>
                        
                        <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center">
                            <a href="{{ route('login') }}?redirect={{ urlencode(request()->fullUrl()) }}" 
                               class="inline-flex items-center justify-center px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg transition-colors">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                                </svg>
                                Войти
                            </a>
                            <a href="{{ route('register') }}" 
                               class="inline-flex items-center justify-center px-6 py-3 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 font-semibold rounded-lg transition-colors">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                                </svg>
                                Регистрация
                            </a>
                        </div>
                        
                    @elseif($reason === 'friends_only')
                        {{-- Контент только для друзей --}}
                        <p class="text-lg text-gray-700 dark:text-gray-300 mb-4">
                            Этот контент доступен только друзьям@if($ownerName) пользователя <strong>{{ $ownerName }}</strong>@endif
                        </p>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                            Добавьте владельца в друзья, чтобы получить доступ
                        </p>
                        
                        <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center">
                            @if($ownerId)
                                <a href="{{ route('users.profile', $ownerId) }}" 
                                   class="inline-flex items-center justify-center px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg transition-colors">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                                    </svg>
                                    Перейти в профиль
                                </a>
                            @endif
                            <a href="{{ route('home') }}" 
                               class="inline-flex items-center justify-center px-6 py-3 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 font-semibold rounded-lg transition-colors">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                </svg>
                                На главную
                            </a>
                        </div>
                        
                    @elseif($reason === 'private_diary')
                        {{-- Приватный дневник --}}
                        <p class="text-lg text-gray-700 dark:text-gray-300 mb-4">
                            Этот дневник приватный
                        </p>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                            Только владелец@if($ownerName) (<strong>{{ $ownerName }}</strong>)@endif может просматривать этот контент
                        </p>
                        
                        <div class="mt-8 flex justify-center">
                            <a href="{{ route('home') }}" 
                               class="inline-flex items-center justify-center px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg transition-colors">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                </svg>
                                На главную
                            </a>
                        </div>
                        
                    @else
                        {{-- Общее сообщение --}}
                        <p class="text-lg text-gray-700 dark:text-gray-300 mb-4">
                            У вас нет доступа к этому контенту
                        </p>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                            {{ $exception->getMessage() ?: 'Запрашиваемый ресурс недоступен' }}
                        </p>
                        
                        <div class="mt-8 flex justify-center">
                            <a href="{{ route('home') }}" 
                               class="inline-flex items-center justify-center px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg transition-colors">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                </svg>
                                На главную
                            </a>
                        </div>
                    @endif
                </div>
                
                <!-- Дополнительная информация -->
                <div class="mt-8 pt-8 border-t border-gray-200 dark:border-gray-700">
                    <p class="text-center text-sm text-gray-500 dark:text-gray-400">
                        Ошибка 403: Доступ запрещен
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection












