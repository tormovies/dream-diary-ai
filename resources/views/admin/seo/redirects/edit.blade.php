<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Редактировать редирект') }}
            </h2>
            <a href="{{ route('admin.seo.redirects.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Назад к списку
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('admin.seo.redirects.update', $redirect) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <x-input-label for="from_path" :value="__('Откуда (путь)')" />
                            <x-text-input id="from_path" name="from_path" type="text" class="mt-1 block w-full" :value="old('from_path', $redirect->from_path)" placeholder="/старая-страница" required />
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Например: /diaries/night/123.html или /old-page</p>
                            <x-input-error class="mt-2" :messages="$errors->get('from_path')" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="to_url" :value="__('Куда (URL или путь)')" />
                            <x-text-input id="to_url" name="to_url" type="text" class="mt-1 block w-full" :value="old('to_url', $redirect->to_url)" placeholder="/новая-страница или https://..." required />
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Относительный путь (/reports/123) или полный URL</p>
                            <x-input-error class="mt-2" :messages="$errors->get('to_url')" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="status_code" :value="__('Код ответа')" />
                            <select id="status_code" name="status_code" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="301" {{ old('status_code', $redirect->status_code) == 301 ? 'selected' : '' }}>301 — постоянный</option>
                                <option value="302" {{ old('status_code', $redirect->status_code) == 302 ? 'selected' : '' }}>302 — временный</option>
                                <option value="307" {{ old('status_code', $redirect->status_code) == 307 ? 'selected' : '' }}>307 — временный (сохранить метод)</option>
                                <option value="308" {{ old('status_code', $redirect->status_code) == 308 ? 'selected' : '' }}>308 — постоянный (сохранить метод)</option>
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('status_code')" />
                        </div>

                        <div class="mb-6">
                            <label class="inline-flex items-center">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $redirect->is_active) ? 'checked' : '' }} class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:ring-indigo-500">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Включён</span>
                            </label>
                        </div>

                        <div class="flex gap-2">
                            <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                Сохранить
                            </button>
                            <a href="{{ route('admin.seo.redirects.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Отмена
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
