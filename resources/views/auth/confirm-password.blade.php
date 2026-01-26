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
                            <h1 class="registration-title">Подтверждение пароля</h1>
                            <p class="registration-subtitle">Введите пароль для продолжения</p>
                        @endif
                    </div>

                    <div class="form-description">
                        {{ __('Это защищенная область приложения. Пожалуйста, подтвердите ваш пароль перед продолжением.') }}
                    </div>

                    <form method="POST" action="{{ route('password.confirm') }}" class="registration-form">
                        @csrf

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
                                       placeholder="Введите ваш пароль" />
                                <button type="button" 
                                        class="toggle-password" 
                                        onclick="togglePasswordVisibility('password', this)">
                                    <i class="far fa-eye"></i>
                                </button>
                            </div>
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <button type="submit" class="btn-primary">
                            <i class="fas fa-check mr-2"></i>{{ __('Подтвердить') }}
                        </button>
                    </form>
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
