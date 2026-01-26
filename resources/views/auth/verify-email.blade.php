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
@endsection
