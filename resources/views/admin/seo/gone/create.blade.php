<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Добавить 410 Gone') }}
            </h2>
            <a href="{{ route('admin.seo.gone.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Назад к списку
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('admin.seo.gone.store') }}">
                        @csrf

                        <div class="mb-4">
                            <x-input-label for="path" :value="__('Путь (без домена)')" />
                            <x-text-input id="path" name="path" type="text" class="mt-1 block w-full" value="{{ old('path') }}" placeholder="/tolkovanie-snov/abc123 или /reports/5" required />
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Как в адресной строке сайта, путь нормализуется так же, как для редиректов.</p>
                            <x-input-error class="mt-2" :messages="$errors->get('path')" />
                        </div>

                        <div class="mb-6">
                            <x-input-label for="note" :value="__('Примечание (необязательно)')" />
                            <textarea id="note" name="note" rows="3" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('note') }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('note')" />
                        </div>

                        <div class="flex gap-2">
                            <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                Сохранить
                            </button>
                            <a href="{{ route('admin.seo.gone.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Отмена
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
