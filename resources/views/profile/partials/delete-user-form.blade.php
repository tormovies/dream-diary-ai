<section>
    <h3 class="form-section-title">
        <i class="fas fa-exclamation-triangle"></i> {{ __('Удаление аккаунта') }}
    </h3>

    <div class="delete-warning">
        <i class="fas fa-exclamation-circle"></i> Внимание: Это действие необратимо!
    </div>

    <p class="delete-description">
        Удаление необратимо. Сохраните важную информацию вручную или
        <a href="{{ route('feedback.index') }}" class="text-purple-600 underline hover:text-purple-700 dark:text-purple-400 dark:hover:text-purple-300">обратитесь в службу поддержки</a>.
    </p>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        class="btn-form-danger"
    >
        <i class="fas fa-trash-alt mr-2"></i>{{ __('Удалить аккаунт') }}
    </x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-medium text-gray-900 dark:text-white">
                {{ __('Вы уверены, что хотите удалить свой аккаунт?') }}
            </h2>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Удаление необратимо. Введите пароль для подтверждения. При необходимости
                <a href="{{ route('feedback.index') }}" class="text-purple-600 underline dark:text-purple-400">обратитесь в поддержку</a>.
            </p>

            <div class="mt-6">
                <x-input-label for="password" value="{{ __('Пароль') }}" class="sr-only" />

                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-full dark:bg-gray-700 dark:text-white dark:border-gray-600"
                    placeholder="{{ __('Пароль') }}"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    {{ __('Отмена') }}
                </x-secondary-button>

                <x-danger-button class="ms-3">
                    {{ __('Удалить аккаунт') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
