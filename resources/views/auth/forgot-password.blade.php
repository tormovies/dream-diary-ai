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
@endsection
