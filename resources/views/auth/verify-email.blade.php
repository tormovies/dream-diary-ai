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
            <title>Подтверждение email — {{ config('app.name', 'Дневник сновидений') }}</title>
        @endif
        
        @vite(['resources/css/app.css', 'resources/js/app.js'])
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
            .form-description {
                color: #495057;
                font-size: 0.95rem;
                margin-bottom: 20px;
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
            .btn-secondary {
                background-color: transparent;
                color: #495057;
                border: 2px solid #dee2e6;
                padding: 16px;
                border-radius: 10px;
                font-weight: 600;
                cursor: pointer;
                font-size: 1rem;
                transition: all 0.2s;
                text-decoration: none;
                display: inline-block;
                width: 100%;
                text-align: center;
            }
            .dark .btn-secondary {
                color: #adb5bd;
                border-color: #343a40;
            }
            .btn-secondary:hover {
                background-color: #f8f9fa;
            }
            .dark .btn-secondary:hover {
                background-color: #2d2d44;
            }
            .button-group {
                display: flex;
                gap: 15px;
                width: 100%;
            }
            .button-group form {
                flex: 1;
            }
            @media (max-width: 480px) {
                .registration-card {
                    padding: 25px 20px;
                }
                .registration-title {
                    font-size: 1.6rem;
                }
                .button-group {
                    flex-direction: column;
                }
            }
        </style>
        <x-header-styles />
        
        <x-yandex-metrika />
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
                            <h1 class="registration-title">Подтверждение email</h1>
                            <p class="registration-subtitle">Проверьте свою почту</p>
                        @endif
                    </div>

                    <div class="form-description">
                        {{ __('Спасибо за регистрацию! Перед началом работы, пожалуйста, подтвердите свой адрес электронной почты, перейдя по ссылке, которую мы только что отправили вам на email. Если вы не получили письмо, мы с удовольствием отправим вам новое.') }}
                    </div>

                    @if (session('status') == 'verification-link-sent')
                        <div class="mb-4 font-medium text-sm text-green-600 dark:text-green-400">
                            {{ __('Новая ссылка для подтверждения была отправлена на адрес электронной почты, указанный при регистрации.') }}
                        </div>
                    @endif

                    <div class="button-group">
                        <form method="POST" action="{{ route('verification.send') }}">
                            @csrf
                            <button type="submit" class="btn-primary">
                                <i class="fas fa-envelope mr-2"></i>{{ __('Отправить письмо повторно') }}
                            </button>
                        </form>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn-secondary">
                                {{ __('Выйти') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
