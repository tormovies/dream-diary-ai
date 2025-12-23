<section>
    <h3 class="form-section-title">
        <i class="fas fa-key"></i> {{ __('Изменение пароля') }}
    </h3>

    <form method="post" action="{{ route('password.update') }}" class="profile-form">
        @csrf
        @method('put')

        <div class="form-group">
            <label class="form-label required" for="update_password_current_password">
                <i class="fas fa-lock"></i> {{ __('Текущий пароль') }}
            </label>
            <div class="password-container">
                <input type="password" 
                       id="update_password_current_password" 
                       name="current_password" 
                       class="form-input" 
                       autocomplete="current-password" 
                       placeholder="Введите текущий пароль" />
                <button type="button" 
                        class="toggle-password" 
                        onclick="togglePasswordVisibility('update_password_current_password', this)">
                    <i class="far fa-eye"></i>
                </button>
            </div>
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label required" for="update_password_password">
                    <i class="fas fa-lock"></i> {{ __('Новый пароль') }}
                </label>
                <div class="password-container">
                    <input type="password" 
                           id="update_password_password" 
                           name="password" 
                           class="form-input" 
                           autocomplete="new-password" 
                           placeholder="Придумайте новый пароль" />
                    <button type="button" 
                            class="toggle-password" 
                            onclick="togglePasswordVisibility('update_password_password', this)">
                        <i class="far fa-eye"></i>
                    </button>
                </div>
                <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
            </div>

            <div class="form-group">
                <label class="form-label required" for="update_password_password_confirmation">
                    <i class="fas fa-lock"></i> {{ __('Подтвердите пароль') }}
                </label>
                <div class="password-container">
                    <input type="password" 
                           id="update_password_password_confirmation" 
                           name="password_confirmation" 
                           class="form-input" 
                           autocomplete="new-password" 
                           placeholder="Повторите новый пароль" />
                    <button type="button" 
                            class="toggle-password" 
                            onclick="togglePasswordVisibility('update_password_password_confirmation', this)">
                        <i class="far fa-eye"></i>
                    </button>
                </div>
                <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
            </div>
        </div>

        <div class="form-hint" style="margin-top: -10px;">
            <i class="fas fa-info-circle"></i> Пароль должен содержать минимум 8 символов, включая буквы и цифры.
        </div>

        <div class="form-actions">
            <div></div>
            <div class="flex items-center gap-4">
                @if (session('status') === 'password-updated')
                    <p
                        x-data="{ show: true }"
                        x-show="show"
                        x-transition
                        x-init="setTimeout(() => show = false, 2000)"
                        class="text-sm text-green-600 dark:text-green-400"
                    >{{ __('Сохранено.') }}</p>
                @endif
                <button type="submit" class="btn-form-primary">
                    <i class="fas fa-key mr-2"></i>{{ __('Обновить пароль') }}
                </button>
            </div>
        </div>
    </form>

    <script>
        function togglePasswordVisibility(inputId, button) {
            const input = document.getElementById(inputId);
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            button.querySelector('i').classList.toggle('fa-eye');
            button.querySelector('i').classList.toggle('fa-eye-slash');
        }
    </script>
</section>
