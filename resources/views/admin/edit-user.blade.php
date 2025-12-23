<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Редактирование пользователя') }}: {{ $user->nickname }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('admin.users.update', $user) }}">
                        @csrf
                        @method('PATCH')

                        <div class="mb-4">
                            <x-input-label for="name" :value="__('Имя')" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required />
                            <x-input-error class="mt-2" :messages="$errors->get('name')" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="nickname" :value="__('Никнейм')" />
                            <x-text-input id="nickname" name="nickname" type="text" class="mt-1 block w-full" :value="old('nickname', $user->nickname)" required />
                            <x-input-error class="mt-2" :messages="$errors->get('nickname')" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="email" :value="__('Email')" />
                            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required />
                            <x-input-error class="mt-2" :messages="$errors->get('email')" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="role" :value="__('Роль')" />
                            <select id="role" name="role" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                <option value="user" {{ old('role', $user->role) === 'user' ? 'selected' : '' }}>Пользователь</option>
                                <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Администратор</option>
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('role')" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="diary_privacy" :value="__('Приватность дневника')" />
                            <select id="diary_privacy" name="diary_privacy" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                <option value="public" {{ old('diary_privacy', $user->diary_privacy) === 'public' ? 'selected' : '' }}>Публичный</option>
                                <option value="friends" {{ old('diary_privacy', $user->diary_privacy) === 'friends' ? 'selected' : '' }}>Только друзьям</option>
                                <option value="private" {{ old('diary_privacy', $user->diary_privacy) === 'private' ? 'selected' : '' }}>Приватный</option>
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('diary_privacy')" />
                        </div>

                        <div class="flex items-center justify-end gap-4">
                            <a href="{{ route('admin.users') }}" class="text-gray-600 hover:text-gray-800">
                                Отмена
                            </a>
                            <x-primary-button>
                                {{ __('Сохранить') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>









