<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Редактировать SEO-запись') }}
            </h2>
            <a href="{{ route('admin.seo.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Назад
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('admin.seo.update', $seo) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PATCH')

                        <div class="mb-6">
                            <x-input-label for="page_type" :value="__('Тип страницы')" />
                            <select id="page_type" name="page_type" required class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                @foreach($pageTypes as $key => $label)
                                    <option value="{{ $key }}" {{ old('page_type', $seo->page_type) === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('page_type')" />
                        </div>

                        <div class="mb-6">
                            <x-input-label for="page_id" :value="__('ID конкретной страницы (опционально)')" />
                            <x-text-input id="page_id" name="page_id" type="number" class="mt-1 block w-full" :value="old('page_id', $seo->page_id)" />
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Оставьте пустым для применения ко всем страницам этого типа</p>
                            <x-input-error class="mt-2" :messages="$errors->get('page_id')" />
                        </div>

                        <div class="mb-6">
                            <x-input-label for="title" :value="__('Title (шаблон)')" />
                            <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" :value="old('title', $seo->title)" placeholder="Например: {dream_title} — {date} | {site_name}" />
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Используйте плейсхолдеры: {dream_title}, {date}, {nickname}, {site_name}, etc.</p>
                            <x-input-error class="mt-2" :messages="$errors->get('title')" />
                        </div>

                        <div class="mb-6">
                            <x-input-label for="description" :value="__('Description (шаблон)')" />
                            <textarea id="description" name="description" rows="3" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('description', $seo->description) }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('description')" />
                        </div>

                        <div class="mb-6">
                            <x-input-label for="h1" :value="__('H1 (шаблон)')" />
                            <x-text-input id="h1" name="h1" type="text" class="mt-1 block w-full" :value="old('h1', $seo->h1)" />
                            <x-input-error class="mt-2" :messages="$errors->get('h1')" />
                        </div>

                        <div class="mb-6">
                            <x-input-label for="h1_description" :value="__('Описание под H1 (опционально)')" />
                            <textarea id="h1_description" name="h1_description" rows="2" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('h1_description', $seo->h1_description) }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('h1_description')" />
                        </div>

                        <div class="mb-6">
                            <x-input-label for="keywords" :value="__('Keywords (опционально)')" />
                            <x-text-input id="keywords" name="keywords" type="text" class="mt-1 block w-full" :value="old('keywords', $seo->keywords)" placeholder="ключевое слово 1, ключевое слово 2" />
                            <x-input-error class="mt-2" :messages="$errors->get('keywords')" />
                        </div>

                        <hr class="my-6 border-gray-300 dark:border-gray-600">

                        <h3 class="text-lg font-medium mb-4 dark:text-white">Open Graph</h3>

                        <div class="mb-6">
                            <x-input-label for="og_title" :value="__('OG Title (опционально)')" />
                            <x-text-input id="og_title" name="og_title" type="text" class="mt-1 block w-full" :value="old('og_title', $seo->og_title)" />
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Если пусто, будет использован обычный title</p>
                            <x-input-error class="mt-2" :messages="$errors->get('og_title')" />
                        </div>

                        <div class="mb-6">
                            <x-input-label for="og_description" :value="__('OG Description (опционально)')" />
                            <textarea id="og_description" name="og_description" rows="2" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('og_description', $seo->og_description) }}</textarea>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Если пусто, будет использован обычный description</p>
                            <x-input-error class="mt-2" :messages="$errors->get('og_description')" />
                        </div>

                        <div class="mb-6">
                            <x-input-label for="og_image" :value="__('OG Image URL (опционально)')" />
                            <x-text-input id="og_image" name="og_image" type="text" class="mt-1 block w-full" :value="old('og_image', $seo->og_image)" placeholder="https://example.com/image.jpg" />
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Или загрузите новый файл ниже (старое изображение будет заменено)</p>
                            @if($seo->og_image)
                                <div class="mt-2">
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Текущее изображение:</p>
                                    @if(strpos($seo->og_image, 'storage/') === 0)
                                        <img src="{{ asset($seo->og_image) }}" alt="OG Image" class="mt-1 max-w-xs h-auto rounded" loading="lazy">
                                    @else
                                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $seo->og_image }}</p>
                                    @endif
                                </div>
                            @endif
                            <x-input-error class="mt-2" :messages="$errors->get('og_image')" />
                        </div>

                        <div class="mb-6">
                            <x-input-label for="og_image_file" :value="__('Загрузить новое OG Image')" />
                            <input id="og_image_file" name="og_image_file" type="file" accept="image/*" class="mt-1 block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-indigo-900 dark:file:text-indigo-300">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Максимальный размер: 2MB. Рекомендуемый размер: 1200x630px</p>
                            <x-input-error class="mt-2" :messages="$errors->get('og_image_file')" />
                        </div>

                        <hr class="my-6 border-gray-300 dark:border-gray-600">

                        <div class="mb-6">
                            <x-input-label for="priority" :value="__('Приоритет')" />
                            <x-text-input id="priority" name="priority" type="number" min="0" max="100" class="mt-1 block w-full" :value="old('priority', $seo->priority)" />
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Чем выше число, тем выше приоритет (0-100)</p>
                            <x-input-error class="mt-2" :messages="$errors->get('priority')" />
                        </div>

                        <div class="mb-6">
                            <label class="flex items-center">
                                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $seo->is_active) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Активна</span>
                            </label>
                        </div>

                        <div class="flex items-center justify-end gap-3">
                            <a href="{{ route('admin.seo.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
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



























