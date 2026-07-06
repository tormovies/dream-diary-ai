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
                            <h1 class="registration-title">Регистрация</h1>
                            <p class="registration-subtitle">Создайте свой аккаунт</p>
                        @endif
                    </div>

                    <form method="POST" action="{{ route('register') }}" class="registration-form">
                        @csrf

                        <!-- Name -->
                        <div class="form-group">
                            <label class="form-label" for="name">
                                <i class="fas fa-user"></i> {{ __('Имя') }}
                            </label>
                            <input type="text" 
                                   id="name" 
                                   name="name" 
                                   class="form-input" 
                                   value="{{ old('name') }}" 
                                   required 
                                   autofocus 
                                   autocomplete="name" 
                                   placeholder="Введите ваше имя" />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <!-- Nickname -->
                        <div class="form-group">
                            <label class="form-label" for="nickname">
                                <i class="fas fa-at"></i> {{ __('Никнейм') }}
                            </label>
                            <input type="text" 
                                   id="nickname" 
                                   name="nickname" 
                                   class="form-input" 
                                   value="{{ old('nickname') }}" 
                                   required 
                                   autocomplete="nickname" 
                                   placeholder="Придумайте уникальный никнейм" />
                            <x-input-error :messages="$errors->get('nickname')" class="mt-2" />
                        </div>

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
                                       autocomplete="new-password" 
                                       placeholder="Создайте надежный пароль" />
                                <button type="button" 
                                        class="toggle-password" 
                                        onclick="togglePasswordVisibility('password', this)">
                                    <i class="far fa-eye"></i>
                                </button>
                            </div>
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <!-- Confirm Password -->
                        <div class="form-group">
                            <label class="form-label" for="password_confirmation">
                                <i class="fas fa-lock"></i> {{ __('Подтвердите пароль') }}
                            </label>
                            <div class="password-container">
                                <input type="password" 
                                       id="password_confirmation" 
                                       name="password_confirmation"
                                       class="form-input" 
                                       required 
                                       autocomplete="new-password" 
                                       placeholder="Повторите пароль" />
                                <button type="button" 
                                        class="toggle-password" 
                                        onclick="togglePasswordVisibility('password_confirmation', this)">
                                    <i class="far fa-eye"></i>
                                </button>
                            </div>
                            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                        </div>

                        <div class="form-group">
                            <label class="flex cursor-pointer items-start gap-3 text-sm text-gray-700 dark:text-gray-300">
                                <input type="checkbox"
                                       name="terms_accepted"
                                       value="1"
                                       class="mt-1 h-4 w-4 shrink-0 rounded border-gray-300 text-purple-600 focus:ring-purple-500 dark:border-gray-600 dark:bg-gray-700"
                                       {{ old('terms_accepted') ? 'checked' : '' }}
                                       required />
                                <span>
                                    Принимаю
                                    <a href="{{ route('legal.terms') }}" target="_blank" rel="noopener noreferrer" class="text-purple-600 underline hover:text-purple-700 dark:text-purple-400">пользовательское соглашение</a>.
                                </span>
                            </label>
                            <x-input-error :messages="$errors->get('terms_accepted')" class="mt-2" />
                        </div>

                        <div class="form-group">
                            <label class="flex cursor-pointer items-start gap-3 text-sm text-gray-700 dark:text-gray-300">
                                <input type="checkbox"
                                       name="personal_data_consent"
                                       value="1"
                                       class="mt-1 h-4 w-4 shrink-0 rounded border-gray-300 text-purple-600 focus:ring-purple-500 dark:border-gray-600 dark:bg-gray-700"
                                       {{ old('personal_data_consent') ? 'checked' : '' }}
                                       required />
                                <span>
                                    Ознакомлен(а) с
                                    <a href="{{ route('legal.personal-data') }}" target="_blank" rel="noopener noreferrer" class="text-purple-600 underline hover:text-purple-700 dark:text-purple-400">политикой обработки персональных данных</a>
                                    и даю согласие на обработку моих персональных данных в целях регистрации и использования сервиса.
                                </span>
                            </label>
                            <x-input-error :messages="$errors->get('personal_data_consent')" class="mt-2" />
                        </div>

                        <button type="submit" class="btn-primary">
                            <i class="fas fa-user-plus mr-2"></i>{{ __('Зарегистрироваться') }}
                        </button>
                    </form>

                    <div class="registration-footer">
                        <p>Уже есть аккаунт? <a href="{{ route('login') }}">Войти</a></p>
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
