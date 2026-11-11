@extends('layouts.base')

@section('content')
    <!-- Основной контент -->
    <div class="min-h-[calc(100vh-200px)] flex items-center justify-center px-4 sm:px-6 lg:px-8 py-12">
            <div class="w-full" style="max-width: 500px;">
                <div class="registration-card card-shadow">
                    <div class="registration-header">
                        @if(isset($seo) && isset($seo['h1']))
                            <h1 class="registration-title">{{ $seo['h1'] }}</h1>
                            @if(isset($seo['h1_description']))
                                <p class="registration-subtitle">{{ $seo['h1_description'] }}</p>
                            @endif
                        @else
                            <h1 class="registration-title">Вход</h1>
                            <p class="registration-subtitle">Войдите в свой аккаунт</p>
                        @endif
                    </div>

                    <!-- Уведомление о сбое хостинга (закрывается, скрытие запоминается в localStorage) -->
                    <div id="hosting-notice" class="relative mt-4 mb-5 rounded-lg border border-amber-300 bg-amber-50 px-4 py-3.5 text-[13px] leading-relaxed text-amber-900 dark:border-amber-600/60 dark:bg-amber-900/30 dark:text-amber-100">
                        <button type="button" onclick="dismissHostingNotice()"
                                class="absolute top-1.5 right-1.5 flex h-6 w-6 items-center justify-center rounded text-amber-700 hover:bg-amber-200/70 dark:text-amber-200 dark:hover:bg-amber-800/50"
                                aria-label="Скрыть уведомление">
                            <i class="fas fa-times"></i>
                        </button>
                        <p class="font-semibold pr-7">
                            <i class="fas fa-exclamation-triangle mr-1"></i> Внимание
                        </p>
                        <p class="mt-1.5">
                            2 ноября наш хостинг-провайдер Beget подвергся хакерской атаке — часть данных за период с 12 октября по 10 ноября утеряна. Если сайт сообщает, что вашего аккаунта нет — <a href="{{ route('register') }}" class="font-medium underline hover:opacity-80">зарегистрируйтесь заново</a>. Записи сновидений за эти даты тоже утеряны. Мы работаем над тем, чтобы лучше защитить данные.
                        </p>
                        <p class="mt-1.5">
                            Не можете авторизоваться или что-то не работает — <a href="{{ route('feedback.index') }}" class="font-medium underline hover:opacity-80">напишите в поддержку</a>.
                        </p>
                        <script>
                            function dismissHostingNotice() {
                                try { localStorage.setItem('hosting_notice_dismissed', '1'); } catch (e) {}
                                var n = document.getElementById('hosting-notice');
                                if (n) { n.style.display = 'none'; }
                            }
                            (function () {
                                try {
                                    if (localStorage.getItem('hosting_notice_dismissed') === '1') {
                                        var n = document.getElementById('hosting-notice');
                                        if (n) { n.style.display = 'none'; }
                                    }
                                } catch (e) {}
                            })();
                        </script>
                    </div>

                    <!-- Session Status -->
                    <x-auth-session-status class="mb-4" :status="session('status')" />

                    <form method="POST" action="{{ route('login') }}" class="registration-form">
                        @csrf

                        <!-- Email Address -->
                        <div class="form-group">
                            <label class="form-label" for="email">
                                <i class="fas fa-envelope"></i> {{ __('Электронная почта') }}
                            </label>
                            <input type="email" 
                                   id="email" 
                                   name="email" 
                                   class="form-input" 
                                   value="{{ old('email') }}" 
                                   required 
                                   autofocus 
                                   autocomplete="username" 
                                   placeholder="example@mail.com" />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <!-- Password -->
                        <div class="form-group">
                            <label class="form-label" for="password">
                                <i class="fas fa-lock"></i> {{ __('Пароль') }}
                            </label>
                            <div class="password-container">
                                <input type="password" 
                                       id="password" 
                                       name="password"
                                       class="form-input" 
                                       required 
                                       autocomplete="current-password" 
                                       placeholder="Введите пароль" />
                                <button type="button" 
                                        class="toggle-password" 
                                        onclick="togglePasswordVisibility('password', this)">
                                    <i class="far fa-eye"></i>
                                </button>
                            </div>
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <!-- Remember Me -->
                        <div class="form-checkbox">
                            <input type="checkbox" 
                                   id="remember_me" 
                                   name="remember" 
                                   class="checkbox-input" />
                            <label class="checkbox-label" for="remember_me">
                                {{ __('Запомнить меня') }}
                            </label>
                        </div>

                        <div class="flex items-center justify-between mt-4">
                            @if (Route::has('password.request'))
                                <a class="text-sm text-purple-600 dark:text-purple-400 hover:text-purple-800 dark:hover:text-purple-300" href="{{ route('password.request') }}">
                                    {{ __('Забыли пароль?') }}
                                </a>
                            @endif
                        </div>

                        <button type="submit" class="btn-primary">
                            <i class="fas fa-sign-in-alt mr-2"></i>{{ __('Войти') }}
                        </button>
                    </form>

                    <div class="registration-footer">
                        <p>Нет аккаунта? <a href="{{ route('register') }}">Зарегистрироваться</a></p>
                    </div>
                </div>
            </div>
        </div>

        <script>
            function togglePasswordVisibility(inputId, button) {
                const input = document.getElementById(inputId);
                const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                input.setAttribute('type', type);
                button.querySelector('i').classList.toggle('fa-eye');
                button.querySelector('i').classList.toggle('fa-eye-slash');
            }
        </script>
@endsection
