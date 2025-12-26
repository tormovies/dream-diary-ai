<section>
    <h3 class="form-section-title">
        <i class="fas fa-id-card"></i> {{ __('Информация профиля') }}
    </h3>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="profile-form">
        @csrf
        @method('patch')

        <div class="form-group">
            <label class="form-label required" for="name">
                <i class="fas fa-user"></i> {{ __('Имя') }}
            </label>
            <input type="text" 
                   id="name" 
                   name="name" 
                   class="form-input" 
                   value="{{ old('name', $user->name) }}" 
                   required 
                   autofocus 
                   autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div class="form-group">
            <label class="form-label required" for="nickname">
                <i class="fas fa-at"></i> {{ __('Никнейм') }}
            </label>
            <input type="text" 
                   id="nickname" 
                   name="nickname" 
                   class="form-input" 
                   value="{{ old('nickname', $user->nickname) }}" 
                   required 
                   autocomplete="nickname" />
            <x-input-error :messages="$errors->get('nickname')" class="mt-2" />
        </div>

        <div class="form-group">
            <label class="form-label required" for="email">
                <i class="fas fa-envelope"></i> {{ __('Электронная почта') }}
            </label>
            <input type="email" 
                   id="email" 
                   name="email" 
                   class="form-input" 
                   value="{{ old('email', $user->email) }}" 
                   required 
                   autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="form-hint" style="margin-top: 10px;">
                    <p class="text-sm text-gray-800 dark:text-gray-200">
                        {{ __('Ваш адрес электронной почты не подтвержден.') }}

                        <button form="send-verification" class="underline text-sm text-purple-600 dark:text-purple-400 hover:text-purple-800 dark:hover:text-purple-300">
                            {{ __('Нажмите здесь, чтобы повторно отправить письмо с подтверждением.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600 dark:text-green-400">
                            {{ __('Новая ссылка для подтверждения отправлена на ваш адрес электронной почты.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="form-group">
            <label class="form-label" for="avatar">
                <i class="fas fa-image"></i> {{ __('Аватар') }}
            </label>
            <div class="grid grid-cols-4 md:grid-cols-8 gap-2 mt-2">
                @php
                    $avatars = [
                        'avatar1.png', 'avatar2.png', 'avatar3.png', 'avatar4.png',
                        'avatar5.png', 'avatar6.png', 'avatar7.png', 'avatar8.png',
                        'avatar9.png', 'avatar10.png', 'avatar11.png', 'avatar12.png',
                        'avatar13.png', 'avatar14.png', 'avatar15.png', 'avatar16.png',
                    ];
                @endphp
                @foreach($avatars as $avatar)
                    @php
                        $emoji = \App\Helpers\AvatarHelper::getEmoji($avatar);
                        $bgColor = \App\Helpers\AvatarHelper::getBackgroundColor($avatar);
                        $darkBgColor = \App\Helpers\AvatarHelper::getDarkBackgroundColor($avatar);
                    @endphp
                    <label class="cursor-pointer">
                        <input type="radio" 
                               name="avatar" 
                               value="{{ $avatar }}" 
                               {{ old('avatar', $user->avatar) === $avatar ? 'checked' : '' }}
                               class="hidden peer">
                        <div class="w-16 h-16 rounded-full border-2 border-gray-300 dark:border-gray-600 peer-checked:border-blue-500 dark:peer-checked:border-blue-400 overflow-hidden {{ $bgColor }} {{ $darkBgColor }} flex items-center justify-center text-2xl">
                            <span>{{ $emoji }}</span>
                        </div>
                    </label>
                @endforeach
            </div>
            <div class="form-hint">Выберите аватар из предустановленных</div>
            <x-input-error :messages="$errors->get('avatar')" class="mt-2" />
        </div>

        <div class="form-group">
            <label class="form-label" for="bio">
                <i class="fas fa-pencil-alt"></i> {{ __('О себе') }}
            </label>
            <textarea id="bio" 
                      name="bio" 
                      class="form-textarea" 
                      rows="3">{{ old('bio', $user->bio) }}</textarea>
            <x-input-error :messages="$errors->get('bio')" class="mt-2" />
        </div>

        <div class="form-group">
            <label class="form-label" for="diary_privacy">
                <i class="fas fa-shield-alt"></i> {{ __('Приватность дневника') }}
            </label>
            <select id="diary_privacy" 
                    name="diary_privacy" 
                    class="form-select">
                <option value="public" {{ old('diary_privacy', $user->diary_privacy) === 'public' ? 'selected' : '' }}>Публичный</option>
                <option value="friends" {{ old('diary_privacy', $user->diary_privacy) === 'friends' ? 'selected' : '' }}>Только друзьям</option>
                <option value="private" {{ old('diary_privacy', $user->diary_privacy) === 'private' ? 'selected' : '' }}>Приватный</option>
            </select>
            <div class="form-hint">Кто может видеть ваши записи о снах</div>
            <x-input-error :messages="$errors->get('diary_privacy')" class="mt-2" />
        </div>

        <div class="form-group">
            <label class="form-label" for="comment_privacy">
                <i class="fas fa-comment"></i> {{ __('Кто может комментировать мои отчёты') }}
            </label>
            <select id="comment_privacy" 
                    name="comment_privacy" 
                    class="form-select">
                <option value="all" {{ old('comment_privacy', $user->comment_privacy) === 'all' ? 'selected' : '' }}>Все</option>
                <option value="friends" {{ old('comment_privacy', $user->comment_privacy) === 'friends' ? 'selected' : '' }}>Только друзья</option>
                <option value="only_me" {{ old('comment_privacy', $user->comment_privacy) === 'only_me' ? 'selected' : '' }}>Только я</option>
                <option value="none" {{ old('comment_privacy', $user->comment_privacy) === 'none' ? 'selected' : '' }}>Никто</option>
            </select>
            <div class="form-hint">Управление доступом к комментированию ваших отчётов</div>
            <x-input-error :messages="$errors->get('comment_privacy')" class="mt-2" />
        </div>

        <div class="form-group">
            <label class="form-label" for="diary_name">
                <i class="fas fa-book"></i> {{ __('Название дневника') }}
            </label>
            <input type="text" 
                   id="diary_name" 
                   name="diary_name" 
                   class="form-input" 
                   value="{{ old('diary_name', $user->diary_name) }}" 
                   placeholder="Дневник пользователя {{ $user->nickname }}" 
                   maxlength="160" />
            <div class="form-hint">Если не указано, будет использовано: "Дневник пользователя {{ $user->nickname }}"</div>
            <x-input-error :messages="$errors->get('diary_name')" class="mt-2" />
        </div>

        @if($user->public_link)
            <div class="form-group">
                <label class="form-label" for="public-link">
                    <i class="fas fa-link"></i> {{ __('Публичная ссылка на дневник') }}
                </label>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <input type="text" 
                           value="{{ route('diary.public', $user->public_link) }}" 
                           readonly 
                           class="form-input" 
                           id="public-link" 
                           style="flex: 1;" />
                    <button type="button" 
                            onclick="copyPublicLink()" 
                            class="btn-form-secondary" 
                            style="padding: 14px 18px;">
                        <i class="fas fa-copy mr-2"></i>Копировать
                    </button>
                </div>
                <div class="form-hint">По этой ссылке будут видны только отчеты с уровнем доступа "Всем"</div>
            </div>
        @endif

        <div class="form-actions">
            <div></div>
            <div class="flex items-center gap-4">
                @if (session('status') === 'profile-updated')
                    <p
                        x-data="{ show: true }"
                        x-show="show"
                        x-transition
                        x-init="setTimeout(() => show = false, 2000)"
                        class="text-sm text-green-600 dark:text-green-400"
                    >{{ __('Сохранено.') }}</p>
                @endif
                <button type="submit" class="btn-form-primary">
                    <i class="fas fa-save mr-2"></i>{{ __('Сохранить') }}
                </button>
            </div>
        </div>
    </form>

    <script>
        function copyPublicLink() {
            const linkInput = document.getElementById('public-link');
            linkInput.select();
            document.execCommand('copy');
            alert('Ссылка скопирована в буфер обмена');
        }
    </script>
</section>
