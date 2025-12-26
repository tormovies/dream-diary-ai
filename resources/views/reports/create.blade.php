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
        <title>Создать отчет - {{ config('app.name', 'Дневник сновидений') }}</title>
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
            /* Стили форм из профиля */
            .profile-form-section {
                background-color: white;
                border-radius: 15px;
                padding: 30px;
                box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
                border: 1px solid #dee2e6;
            }
            .dark .profile-form-section {
                background-color: #1a1a2e;
                border-color: #343a40;
                box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
            }
            .profile-form {
                display: flex;
                flex-direction: column;
                gap: 20px;
            }
            .form-group {
                display: flex;
                flex-direction: column;
                gap: 8px;
            }
            .form-label {
                font-weight: 600;
                color: #212529;
                display: flex;
                align-items: center;
                gap: 8px;
            }
            .dark .form-label {
                color: #f8f9fa;
            }
            .form-label.required:after {
                content: '*';
                color: #fa5252;
                margin-left: 4px;
            }
            .form-input, .form-select, .form-textarea {
                padding: 14px 18px;
                border-radius: 10px;
                border: 1px solid #dee2e6;
                background-color: white;
                color: #212529;
                font-family: inherit;
                font-size: 1rem;
                transition: all 0.2s;
                width: 100%;
            }
            .form-input:focus, .form-select:focus, .form-textarea:focus {
                outline: none;
                border-color: #4263eb;
                box-shadow: 0 0 0 3px rgba(116, 143, 252, 0.2);
            }
            .dark .form-input, .dark .form-select, .dark .form-textarea {
                background-color: #2d2d44;
                border-color: #343a40;
                color: #f8f9fa;
            }
            .dark .form-input:focus, .dark .form-select:focus, .dark .form-textarea:focus {
                border-color: #748ffc;
            }
            .form-textarea {
                min-height: 120px;
                resize: vertical;
            }
            .form-hint {
                font-size: 0.85rem;
                color: #495057;
                margin-top: 5px;
            }
            .dark .form-hint {
                color: #adb5bd;
            }
            .form-actions {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-top: 30px;
                padding-top: 20px;
                border-top: 1px solid #dee2e6;
            }
            .dark .form-actions {
                border-top-color: #343a40;
            }
            .btn-form-primary {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
                padding: 12px 24px;
                border-radius: 8px;
                border: none;
                font-weight: 600;
                cursor: pointer;
                font-size: 1rem;
                transition: all 0.2s;
            }
            .btn-form-primary:hover {
                transform: translateY(-2px);
                box-shadow: 0 6px 18px rgba(102, 126, 234, 0.4);
            }
            .btn-form-secondary {
                background-color: transparent;
                color: #495057;
                border: 2px solid #dee2e6;
                padding: 12px 24px;
                border-radius: 8px;
                font-weight: 600;
                cursor: pointer;
                font-size: 1rem;
                transition: all 0.2s;
                text-decoration: none;
                display: inline-block;
            }
            .dark .btn-form-secondary {
                color: #adb5bd;
                border-color: #343a40;
            }
            .btn-form-secondary:hover {
                background-color: #f8f9fa;
            }
            .dark .btn-form-secondary:hover {
                background-color: #2d2d44;
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
                    <h2 class="text-2xl font-bold text-purple-600 dark:text-purple-400">Создать отчет</h2>
                    <a href="{{ route('dashboard') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                        <i class="fas fa-arrow-left mr-2"></i>Назад к списку
                    </a>
                </div>

                <!-- Форма -->
                <div class="profile-form-section card-shadow">
                    @if ($errors->any())
                        <div class="mb-4 bg-red-100 dark:bg-red-900 border border-red-400 dark:border-red-700 text-red-700 dark:text-red-300 px-4 py-3 rounded-lg">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="mb-4 bg-red-100 dark:bg-red-900 border border-red-400 dark:border-red-700 text-red-700 dark:text-red-300 px-4 py-3 rounded-lg">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('reports.store') }}" id="reportForm" class="profile-form">
                        @csrf

                        <!-- Дата отчета -->
                        <div class="form-group">
                            <label for="report_date" class="form-label required">
                                <i class="fas fa-calendar"></i> Дата отчета
                            </label>
                            <input type="date" 
                                   id="report_date" 
                                   name="report_date" 
                                   class="form-input" 
                                   value="{{ old('report_date', date('Y-m-d')) }}" 
                                   required />
                            <x-input-error :messages="$errors->get('report_date')" class="mt-2" />
                        </div>

                        <!-- Уровень доступа -->
                        <div class="form-group">
                            <label for="access_level" class="form-label required">
                                <i class="fas fa-lock"></i> Уровень доступа
                            </label>
                            <select id="access_level" 
                                   name="access_level" 
                                   class="form-select"
                                   required>
                                <option value="all" {{ old('access_level', 'all') === 'all' ? 'selected' : '' }}>Всем</option>
                                <option value="friends" {{ old('access_level') === 'friends' ? 'selected' : '' }}>Только друзьям</option>
                                <option value="none" {{ old('access_level') === 'none' ? 'selected' : '' }}>Никому</option>
                            </select>
                            <x-input-error :messages="$errors->get('access_level')" class="mt-2" />
                        </div>

                        <!-- Сны -->
                        <div class="form-group">
                            <div class="flex justify-between items-center">
                                <label class="form-label">
                                    <i class="fas fa-moon"></i> Сны
                                </label>
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
                        <div class="form-group">
                            <label for="tags-input" class="form-label">
                                <i class="fas fa-tags"></i> Теги
                            </label>
                            <div class="relative">
                                <input type="text" 
                                       id="tags-input" 
                                       class="form-input" 
                                       placeholder="Начните вводить тег..." 
                                       autocomplete="off" />
                                <div id="tags-suggestions" class="absolute z-10 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-lg hidden max-h-60 overflow-y-auto"></div>
                            </div>
                            <div id="tags-selected" class="mt-2 flex flex-wrap gap-2"></div>
                            <input type="hidden" id="tags-hidden" name="tags" value="[]" />
                            <div class="form-hint">Введите тег и нажмите Enter или выберите из предложенных</div>
                        </div>

                        <div class="form-actions">
                            <a href="{{ route('dashboard') }}" class="btn-form-secondary">
                                Отмена
                            </a>
                            <button type="submit" class="btn-form-primary">
                                <i class="fas fa-save mr-2"></i>Создать отчет
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

            // Автоматически добавляем первый сон при загрузке страницы
            document.addEventListener('DOMContentLoaded', function() {
                addDream();
            });

            function addDream() {
                const container = document.getElementById('dreams-container');
                const dreamDiv = document.createElement('div');
                dreamDiv.className = 'dream-item mb-4 p-4 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700';
                dreamDiv.dataset.index = dreamIndex;

                dreamDiv.innerHTML = `
                    <div class="flex justify-between items-center mb-4">
                        <h4 class="font-semibold text-gray-900 dark:text-white">Сон #${dreamIndex + 1}</h4>
                        <button type="button" 
                                onclick="removeDream(this)" 
                                class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-3 rounded-lg text-sm transition-colors">
                            <i class="fas fa-trash mr-1"></i>Удалить
                        </button>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label class="form-label">Название <span class="text-gray-400 text-xs">(необязательно)</span></label>
                        <input type="text" 
                               name="dreams[${dreamIndex}][title]" 
                               class="form-input"
                               placeholder="Оставьте пустым, если сон не важен" />
                        <div class="form-hint">Хотя бы у одного сна в отчете должно быть название</div>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label class="form-label required">Описание</label>
                        <textarea name="dreams[${dreamIndex}][description]" 
                                  rows="4"
                                  class="form-textarea"
                                  required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label required">Тип сна</label>
                        <select name="dreams[${dreamIndex}][dream_type]" 
                                class="form-select"
                                required>
                            ${dreamTypes.map(type => `<option value="${type}" ${type === 'Бледный сон' ? 'selected' : ''}>${type}</option>`).join('')}
                        </select>
                    </div>
                `;

                container.appendChild(dreamDiv);
                dreamIndex++;
            }

            // Переиндексация снов после удаления
            function reindexDreams() {
                const items = document.querySelectorAll('.dream-item');
                items.forEach((item, newIndex) => {
                    const oldIndex = item.dataset.index;
                    item.dataset.index = newIndex;
                    
                    // Обновляем имена полей
                    const titleInput = item.querySelector(`input[name^="dreams["]`);
                    const descriptionInput = item.querySelector(`textarea[name^="dreams["]`);
                    const typeSelect = item.querySelector(`select[name^="dreams["]`);
                    const header = item.querySelector('h4');
                    
                    if (titleInput) titleInput.name = `dreams[${newIndex}][title]`;
                    if (descriptionInput) descriptionInput.name = `dreams[${newIndex}][description]`;
                    if (typeSelect) typeSelect.name = `dreams[${newIndex}][dream_type]`;
                    if (header) header.textContent = `Сон #${newIndex + 1}`;
                });
            }

            function removeDream(button) {
                const dreamItem = button.closest('.dream-item');
                if (dreamItem) {
                    dreamItem.remove();
                    reindexDreams();
                }
            }

            // Автодополнение тегов
            let selectedTags = [];
            const tagsInput = document.getElementById('tags-input');
            const tagsSuggestions = document.getElementById('tags-suggestions');
            const tagsSelected = document.getElementById('tags-selected');
            const tagsHidden = document.getElementById('tags-hidden');
            let autocompleteTimeout;

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
                
                // Всегда устанавливаем валидный JSON массив (даже если пустой)
                tagsHidden.value = JSON.stringify(selectedTags.length > 0 ? selectedTags : []);
            }
            
            // Инициализируем скрытое поле при загрузке
            document.addEventListener('DOMContentLoaded', function() {
                updateTagsDisplay();
            });

            // Обработка формы перед отправкой
            document.getElementById('reportForm').addEventListener('submit', function(e) {
                // Обновляем скрытое поле с тегами (всегда валидный JSON массив)
                tagsHidden.value = JSON.stringify(selectedTags.length > 0 ? selectedTags : []);
                
                // Проверяем, что есть хотя бы один сон
                const dreams = document.querySelectorAll('.dream-item');
                if (dreams.length === 0) {
                    e.preventDefault();
                    alert('Пожалуйста, добавьте хотя бы один сон');
                    return false;
                }
                
                // Проверяем, что все обязательные поля снов заполнены
                let hasErrors = false;
                let hasAnyTitle = false;
                
                dreams.forEach((dream, index) => {
                    const title = dream.querySelector(`input[name^="dreams["]`);
                    const description = dream.querySelector(`textarea[name^="dreams["]`);
                    
                    // Название необязательно, но проверяем наличие хотя бы одного
                    if (title && title.value.trim()) {
                        hasAnyTitle = true;
                    }
                    
                    if (!description || !description.value.trim()) {
                        hasErrors = true;
                        description?.classList.add('border-red-500');
                    } else {
                        description?.classList.remove('border-red-500');
                    }
                });
                
                if (hasErrors) {
                    e.preventDefault();
                    alert('Пожалуйста, заполните описание для всех снов');
                    return false;
                }
                
                // Проверка на наличие хотя бы одного названия будет на сервере
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
