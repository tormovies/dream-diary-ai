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
        
        @if(isset($seo))
            <x-seo-head :seo="$seo" />
        @else
            <title>Восстановление пароля — {{ config('app.name', 'Дневник сновидений') }}</title>
        @endif
        
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
        <style>
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
            .form-description {
                color: #495057;
                font-size: 0.95rem;
                margin-bottom: 10px;
            }
            .dark .form-description {
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
        <x-header-styles />
    </head>
    <body class="font-sans antialiased bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100">
        <x-header />

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
                            <h1 class="registration-title">Восстановление пароля</h1>
                            <p class="registration-subtitle">Введите email для восстановления доступа</p>
                        @endif
                    </div>

                    <div class="form-description mb-4">
                        {{ __('Забыли пароль? Не проблема. Просто укажите ваш адрес электронной почты, и мы отправим вам ссылку для сброса пароля, которая позволит вам выбрать новый.') }}
                    </div>

                    <!-- Session Status -->
                    <x-auth-session-status class="mb-4" :status="session('status')" />

                    <form method="POST" action="{{ route('password.email') }}" class="registration-form">
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
                                   placeholder="example@mail.com" />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <button type="submit" class="btn-primary">
                            <i class="fas fa-paper-plane mr-2"></i>{{ __('Отправить ссылку для сброса пароля') }}
                        </button>
                    </form>

                    <div class="registration-footer">
                        <a href="{{ route('login') }}">← Вернуться к входу</a>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
