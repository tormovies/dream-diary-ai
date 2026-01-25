@extends('layouts.base')

@push('styles')
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
            .registration-card {
                background-color: white;
                border-radius: 20px;
                padding: 40px;
                box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
                border: 1px solid #dee2e6;
                width: 100%;
            }
            .dark .registration-card {
                background-color: #1a1a2e;
                border-color: #343a40;
                box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
            }
            .registration-header {
                text-align: center;
                margin-bottom: 30px;
            }
            .registration-title {
                font-size: 2rem;
                color: #667eea;
                margin-bottom: 10px;
                font-weight: 700;
            }
            .dark .registration-title {
                color: #748ffc;
            }
            .registration-subtitle {
                color: #495057;
                font-size: 1.1rem;
            }
            .dark .registration-subtitle {
                color: #adb5bd;
            }
            .registration-form {
                display: flex;
                flex-direction: column;
                gap: 20px;
                width: 100%;
            }
            .form-group {
                display: flex;
                flex-direction: column;
                gap: 8px;
                width: 100%;
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
            .form-input {
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
            .form-input:focus {
                outline: none;
                border-color: #4263eb;
                box-shadow: 0 0 0 3px rgba(116, 143, 252, 0.2);
            }
            .dark .form-input {
                background-color: #2d2d44;
                border-color: #343a40;
                color: #f8f9fa;
            }
            .dark .form-input:focus {
                border-color: #748ffc;
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
                margin-top: 10px;
                width: 100%;
            }
            .checkbox-input {
                margin-top: 3px;
                cursor: pointer;
            }
            .checkbox-label {
                font-size: 0.9rem;
                color: #495057;
                line-height: 1.4;
                flex: 1;
            }
            .dark .checkbox-label {
                color: #adb5bd;
            }
            .btn-primary {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
                padding: 16px;
                border-radius: 10px;
                border: none;
                font-weight: 600;
                cursor: pointer;
                font-size: 1.1rem;
                transition: all 0.2s;
                width: 100%;
            }
            .btn-primary:hover {
                transform: translateY(-2px);
                box-shadow: 0 6px 18px rgba(102, 126, 234, 0.4);
            }
            .registration-footer {
                text-align: center;
                margin-top: 30px;
                padding-top: 20px;
                border-top: 1px solid #dee2e6;
                color: #495057;
                width: 100%;
            }
            .dark .registration-footer {
                border-top-color: #343a40;
                color: #adb5bd;
            }
            .registration-footer a {
                color: #4263eb;
                text-decoration: none;
                font-weight: 600;
            }
            .dark .registration-footer a {
                color: #748ffc;
            }
            .registration-footer a:hover {
                text-decoration: underline;
            }
            @media (max-width: 480px) {
                .registration-card {
                    padding: 25px 20px;
                }
                .registration-title {
                    font-size: 1.6rem;
                }
            }
    </style>
@endpush

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
                                   :value="old('email')" 
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
