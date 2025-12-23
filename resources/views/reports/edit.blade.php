<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" 
      x-data="{ theme: 'light' }"
      x-bind:class="{ 'dark': theme === 'dark' }"
      x-init="
        const savedTheme = localStorage.getItem('theme') || 'light';
        theme = savedTheme;
        if (savedTheme === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
      ">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Редактировать отчет - {{ config('app.name', 'Дневник сновидений') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
        <style>
            .card-shadow {
                box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            }
            .dark .card-shadow {
                box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
            }
        </style>
        <x-header-styles />
    </head>
    <body class="font-sans antialiased bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100">
        <x-header />

        <!-- Основной контент -->
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <main class="space-y-6">
                <!-- Заголовок -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700 flex justify-between items-center">
                    <h2 class="text-2xl font-bold text-purple-600 dark:text-purple-400">Редактировать отчет</h2>
                    <a href="{{ route('reports.show', $report) }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                        <i class="fas fa-arrow-left mr-2"></i>Назад к отчету
                    </a>
                </div>

                <!-- Форма -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl overflow-hidden card-shadow border border-gray-200 dark:border-gray-700">
                    <div class="p-6">
                        <form method="POST" action="{{ route('reports.update', $report) }}" id="reportForm">
                            @csrf
                            @method('PUT')

                            <!-- Дата отчета -->
                            <div class="mb-6">
                                <x-input-label for="report_date" :value="__('Дата отчета')" />
                                <x-text-input id="report_date" 
                                             class="block mt-1 w-full dark:bg-gray-700 dark:text-white dark:border-gray-600" 
                                             type="date" 
                                             name="report_date" 
                                             :value="old('report_date', $report->report_date->format('Y-m-d'))" 
                                             required />
                                <x-input-error :messages="$errors->get('report_date')" class="mt-2" />
                            </div>

                            <!-- Уровень доступа -->
                            <div class="mb-6">
                                <x-input-label for="access_level" :value="__('Уровень доступа')" />
                                <select id="access_level" 
                                       name="access_level" 
                                       class="block mt-1 w-full border-gray-300 dark:border-gray-600 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                       required>
                                    <option value="all" {{ old('access_level', $report->access_level) === 'all' ? 'selected' : '' }}>Всем</option>
                                    <option value="friends" {{ old('access_level', $report->access_level) === 'friends' ? 'selected' : '' }}>Только друзьям</option>
                                    <option value="none" {{ old('access_level', $report->access_level) === 'none' ? 'selected' : '' }}>Никому</option>
                                </select>
                                <x-input-error :messages="$errors->get('access_level')" class="mt-2" />
                            </div>

                            <!-- Статус публикации -->
                            <div class="mb-6">
                                <x-input-label for="status" :value="__('Статус публикации')" />
                                <select id="status" 
                                       name="status" 
                                       class="block mt-1 w-full border-gray-300 dark:border-gray-600 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                       required>
                                    <option value="draft" {{ old('status', $report->status) === 'draft' ? 'selected' : '' }}>Черновик (не опубликован)</option>
                                    <option value="published" {{ old('status', $report->status) === 'published' ? 'selected' : '' }}>Опубликован</option>
                                </select>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Черновики видны только вам. Опубликованные отчеты видны другим пользователям согласно уровню доступа.
                                </p>
                                <x-input-error :messages="$errors->get('status')" class="mt-2" />
                            </div>

                            <!-- Сны -->
                            <div class="mb-6">
                                <div class="flex justify-between items-center mb-4">
                                    <x-input-label :value="__('Сны')" />
                                    <button type="button" 
                                            onclick="addDream()" 
                                            class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg transition-colors">
                                        <i class="fas fa-plus mr-2"></i>Добавить сон
                                    </button>
                                </div>
                                
                                <div id="dreams-container">
                                    <!-- Сны будут добавляться динамически -->
                                </div>
                                
                                <x-input-error :messages="$errors->get('dreams')" class="mt-2" />
                            </div>

                            <!-- Теги -->
                            <div class="mb-6">
                                <x-input-label for="tags" :value="__('Теги')" />
                                <div class="relative">
                                    <input type="text" 
                                           id="tags-input" 
                                           class="block mt-1 w-full border-gray-300 dark:border-gray-600 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white" 
                                           placeholder="Начните вводить тег..." 
                                           autocomplete="off" />
                                    <div id="tags-suggestions" class="absolute z-10 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-lg hidden max-h-60 overflow-y-auto"></div>
                                </div>
                                <div id="tags-selected" class="mt-2 flex flex-wrap gap-2"></div>
                                <input type="hidden" id="tags-hidden" name="tags" />
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Введите тег и нажмите Enter или выберите из предложенных</p>
                            </div>

                            <div class="flex items-center justify-end mt-4 gap-4">
                                <a href="{{ route('reports.show', $report) }}" 
                                   class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                                    Отмена
                                </a>
                                <button type="submit" class="bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-bold py-2 px-4 rounded-lg transition-all">
                                    <i class="fas fa-save mr-2"></i>Сохранить изменения
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>

        <script>
            let dreamIndex = 0;
            const dreamTypes = @json($dreamTypes);
            const existingDreams = @json($report->dreams);

            function addDream(dreamData = null) {
                const container = document.getElementById('dreams-container');
                const dreamDiv = document.createElement('div');
                dreamDiv.className = 'dream-item mb-4 p-4 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700';
                dreamDiv.dataset.index = dreamIndex;

                // Обрабатываем title: если null, undefined или строка "null" - используем пустую строку
                let title = '';
                if (dreamData && dreamData.title !== null && dreamData.title !== undefined) {
                    title = String(dreamData.title).trim();
                    // Если это строка "null", очищаем
                    if (title.toLowerCase() === 'null') {
                        title = '';
                    }
                }
                const description = dreamData ? dreamData.description : '';
                // По умолчанию для новых снов используем "Бледный сон"
                const dreamType = dreamData ? dreamData.dream_type : 'Бледный сон';

                dreamDiv.innerHTML = `
                    <div class="flex justify-between items-center mb-2">
                        <h4 class="font-semibold text-gray-900 dark:text-white">Сон #${dreamIndex + 1}</h4>
                        <button type="button" 
                                onclick="removeDream(this)" 
                                class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-3 rounded-lg text-sm transition-colors">
                            <i class="fas fa-trash mr-1"></i>Удалить
                        </button>
                    </div>
                    
                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Название <span class="text-gray-400 text-xs">(необязательно)</span></label>
                        <input type="text" 
                               name="dreams[${dreamIndex}][title]" 
                               value="${title}"
                               class="block w-full border-gray-300 dark:border-gray-600 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white"
                               placeholder="Оставьте пустым, если сон не важен" />
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Хотя бы у одного сна в отчете должно быть название</p>
                    </div>
                    
                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Описание</label>
                        <textarea name="dreams[${dreamIndex}][description]" 
                                  rows="4"
                                  class="block w-full border-gray-300 dark:border-gray-600 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white"
                                  required>${description}</textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Тип сна</label>
                        <select name="dreams[${dreamIndex}][dream_type]" 
                                class="block w-full border-gray-300 dark:border-gray-600 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-white"
                                required>
                            ${dreamTypes.map(type => 
                                `<option value="${type}" ${type === dreamType ? 'selected' : ''}>${type}</option>`
                            ).join('')}
                        </select>
                    </div>
                `;

                container.appendChild(dreamDiv);
                dreamIndex++;
            }

            function removeDream(button) {
                const dreamItem = button.closest('.dream-item');
                dreamItem.remove();
                updateDreamNumbers();
            }

            function updateDreamNumbers() {
                const items = document.querySelectorAll('.dream-item');
                items.forEach((item, index) => {
                    const header = item.querySelector('h4');
                    if (header) {
                        header.textContent = `Сон #${index + 1}`;
                    }
                });
            }

            // Автодополнение тегов
            let selectedTags = @json($report->tags->pluck('name')->toArray());
            const tagsInput = document.getElementById('tags-input');
            const tagsSuggestions = document.getElementById('tags-suggestions');
            const tagsSelected = document.getElementById('tags-selected');
            const tagsHidden = document.getElementById('tags-hidden');
            let autocompleteTimeout;

            function initTags() {
                updateTagsDisplay();
            }

            tagsInput.addEventListener('input', function() {
                const query = this.value.trim();
                
                clearTimeout(autocompleteTimeout);
                
                if (query.length < 2) {
                    tagsSuggestions.classList.add('hidden');
                    return;
                }

                autocompleteTimeout = setTimeout(() => {
                    fetch(`{{ route('tags.autocomplete') }}?q=${encodeURIComponent(query)}`)
                        .then(response => response.json())
                        .then(tags => {
                            displaySuggestions(tags, query);
                        })
                        .catch(error => console.error('Error:', error));
                }, 300);
            });

            tagsInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && this.value.trim()) {
                    e.preventDefault();
                    addTag(this.value.trim());
                    this.value = '';
                    tagsSuggestions.classList.add('hidden');
                } else if (e.key === 'Escape') {
                    tagsSuggestions.classList.add('hidden');
                }
            });

            function displaySuggestions(tags, query) {
                if (tags.length === 0) {
                    tagsSuggestions.innerHTML = `
                        <div class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400">
                            Тег не найден. Нажмите Enter, чтобы добавить "${query}"
                        </div>
                    `;
                } else {
                    tagsSuggestions.innerHTML = tags.map(tag => {
                        const isSelected = selectedTags.some(t => t.toLowerCase() === tag.name.toLowerCase());
                        return `
                            <div class="px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer ${isSelected ? 'bg-gray-200 dark:bg-gray-600' : ''} text-gray-900 dark:text-white" 
                                 onclick="selectTag('${tag.name.replace(/'/g, "\\'")}')">
                                ${tag.name}
                            </div>
                        `;
                    }).join('');
                }
                tagsSuggestions.classList.remove('hidden');
            }

            function selectTag(tagName) {
                addTag(tagName);
                tagsInput.value = '';
                tagsSuggestions.classList.add('hidden');
            }

            function addTag(tagName) {
                const normalizedTag = tagName.trim();
                if (!normalizedTag) return;

                // Проверяем, не добавлен ли уже этот тег
                if (selectedTags.some(t => t.toLowerCase() === normalizedTag.toLowerCase())) {
                    return;
                }

                selectedTags.push(normalizedTag);
                updateTagsDisplay();
            }

            function removeTag(tagName) {
                selectedTags = selectedTags.filter(t => t.toLowerCase() !== tagName.toLowerCase());
                updateTagsDisplay();
            }

            function updateTagsDisplay() {
                tagsSelected.innerHTML = selectedTags.map(tag => `
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-300">
                        ${tag}
                        <button type="button" onclick="removeTag('${tag.replace(/'/g, "\\'")}')" class="ml-2 text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-200">×</button>
                    </span>
                `).join('');
                
                tagsHidden.value = JSON.stringify(selectedTags);
            }

            // Обработка формы для тегов
            document.getElementById('reportForm').addEventListener('submit', function(e) {
                tagsHidden.value = JSON.stringify(selectedTags);
            });

            // Загружаем существующие сны при загрузке страницы
            window.addEventListener('DOMContentLoaded', function() {
                if (existingDreams.length > 0) {
                    existingDreams.forEach(dream => {
                        addDream(dream);
                    });
                } else {
                    addDream();
                }
                initTags();
            });

            function toggleTheme() {
                const html = document.documentElement;
                const currentTheme = html.classList.contains('dark') ? 'dark' : 'light';
                const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                
                if (newTheme === 'dark') {
                    html.classList.add('dark');
                } else {
                    html.classList.remove('dark');
                }
                
                localStorage.setItem('theme', newTheme);
            }
        </script>
    </body>
</html>
