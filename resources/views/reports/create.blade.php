@extends('layouts.base')

@section('content')
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

                        <!-- Блоки: сны и контекст -->
                        <div class="form-group">
                            <div class="flex justify-between items-center">
                                <label class="form-label">
                                    <i class="fas fa-moon"></i> Блоки
                                </label>
                                <button type="button" 
                                        onclick="addDream()" 
                                        class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg transition-colors">
                                    <i class="fas fa-plus mr-2"></i>Добавить блок
                                </button>
                            </div>
                            <div class="form-hint mb-2">Добавляйте сны и при необходимости один блок «Контекст» — ваши мысли по поводу сна.</div>
                            <div id="dreams-container">
                                <!-- Блоки добавляются динамически -->
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
            </main>
        </div>

        <script>
            let dreamIndex = 0;
            const dreamTypes = @json($dreamTypes);
            const blockTypes = @json($blockTypes);
            const BLOCK_TYPE_CONTEXT = @json(\App\Models\Report::BLOCK_TYPE_CONTEXT);

            // Автоматически добавляем первый блок при загрузке страницы
            document.addEventListener('DOMContentLoaded', function() {
                addDream();
            });

            function getContextBlockCount() {
                let count = 0;
                document.querySelectorAll('.dream-item select.block-type-select').forEach(sel => {
                    if (sel.value === BLOCK_TYPE_CONTEXT) count++;
                });
                return count;
            }

            function buildBlockTypeOptions(selectedType) {
                const hasContext = getContextBlockCount() > 0;
                return blockTypes.map(type => {
                    if (type === BLOCK_TYPE_CONTEXT && hasContext) return '';
                    return `<option value="${type}" ${type === (selectedType || 'Бледный сон') ? 'selected' : ''}>${type}</option>`;
                }).filter(Boolean).join('');
            }

            function toggleBlockFields(blockEl, isContext) {
                const titleRow = blockEl.querySelector('.block-title-row');
                const descLabel = blockEl.querySelector('.block-description-label');
                const descInput = blockEl.querySelector('textarea[name^="dreams["]');
                const seriesHint = blockEl.querySelector('.dream-series-hint');
                if (titleRow) titleRow.style.display = isContext ? 'none' : '';
                if (descLabel) {
                    descLabel.textContent = isContext ? 'Контекст (ваши мысли, идеи по поводу сна)' : 'Описание';
                    descLabel.classList.toggle('required', !isContext);
                }
                if (descInput) descInput.required = !isContext;
                if (seriesHint) seriesHint.style.display = isContext ? 'none' : '';
            }

            function addDream(dreamData = null) {
                const container = document.getElementById('dreams-container');
                const dreamDiv = document.createElement('div');
                dreamDiv.className = 'dream-item mb-4 p-4 border border-gray-300 dark:border-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700';
                dreamDiv.dataset.index = dreamIndex;

                const isContext = dreamData && dreamData.dream_type === BLOCK_TYPE_CONTEXT;
                const description = dreamData ? (dreamData.description || '') : '';
                const dreamType = dreamData ? (dreamData.dream_type || 'Бледный сон') : 'Бледный сон';
                const title = dreamData && dreamData.title !== undefined && dreamData.title !== null && String(dreamData.title).trim().toLowerCase() !== 'null' ? String(dreamData.title).trim() : '';

                dreamDiv.innerHTML = `
                    <div class="flex justify-between items-center mb-4">
                        <h4 class="block-header font-semibold text-gray-900 dark:text-white">Блок #${dreamIndex + 1}</h4>
                        <button type="button" 
                                onclick="removeDream(this)" 
                                class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-3 rounded-lg text-sm transition-colors">
                            <i class="fas fa-trash mr-1"></i>Удалить
                        </button>
                    </div>
                    
                    <div class="form-group mb-3 block-title-row" style="${isContext ? 'display:none' : ''}">
                        <label class="form-label">Название <span class="text-gray-400 text-xs">(необязательно)</span></label>
                        <input type="text" 
                               name="dreams[${dreamIndex}][title]" 
                               class="form-input"
                               value="${(title || '').replace(/"/g, '&quot;')}"
                               placeholder="Оставьте пустым, если не придумать" />
                        <div class="form-hint">Хотя бы у одного сна в отчете должно быть название</div>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label class="form-label block-description-label ${isContext ? '' : 'required'}">${isContext ? 'Контекст (ваши мысли, идеи по поводу сна)' : 'Описание'}</label>
                        <textarea name="dreams[${dreamIndex}][description]" 
                                  rows="4"
                                  class="form-textarea dream-description"
                                  data-dream-index="${dreamIndex}"
                                  oninput="checkDreamSeries(this)"
                                  ${isContext ? '' : 'required'}>${(description || '').replace(/</g, '&lt;').replace(/>/g, '&gt;')}</textarea>
                        <div class="form-hint dream-series-hint" style="${isContext ? 'display:none' : ''}">Если хотите написать несколько снов в одно окно - используйте разделитель три и более тире (-----)</div>
                        <div id="dream-count-${dreamIndex}" class="mt-2 text-sm font-semibold" x-cloak></div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label required">Тип блока</label>
                        <select name="dreams[${dreamIndex}][dream_type]" 
                                class="form-select block-type-select"
                                data-block-index="${dreamIndex}"
                                required>
                            ${buildBlockTypeOptions(dreamType)}
                        </select>
                    </div>
                `;

                container.appendChild(dreamDiv);
                dreamIndex++;

                container.querySelectorAll('.block-type-select').forEach(sel => {
                    sel.removeEventListener('change', onBlockTypeChange);
                    sel.addEventListener('change', onBlockTypeChange);
                });
            }

            function onBlockTypeChange(e) {
                const select = e.target;
                const blockEl = select.closest('.dream-item');
                const isContext = select.value === BLOCK_TYPE_CONTEXT;
                toggleBlockFields(blockEl, isContext);
                updateBlockTypeOptions();
            }

            function updateBlockTypeOptions() {
                document.querySelectorAll('.dream-item').forEach(blockEl => {
                    const select = blockEl.querySelector('select.block-type-select');
                    if (!select) return;
                    const currentVal = select.value;
                    const hasContext = getContextBlockCount() > (currentVal === BLOCK_TYPE_CONTEXT ? 1 : 0);
                    const options = blockTypes.map(type => {
                        if (type === BLOCK_TYPE_CONTEXT && hasContext) return null;
                        return `<option value="${type}" ${type === currentVal ? 'selected' : ''}>${type}</option>`;
                    }).filter(Boolean).join('');
                    select.innerHTML = options;
                });
            }

            // Переиндексация блоков после удаления
            function reindexDreams() {
                const items = document.querySelectorAll('.dream-item');
                items.forEach((item, newIndex) => {
                    item.dataset.index = newIndex;
                    const titleInput = item.querySelector(`input[name^="dreams["]`);
                    const descriptionInput = item.querySelector(`textarea[name^="dreams["]`);
                    const typeSelect = item.querySelector(`select.block-type-select`);
                    const header = item.querySelector('.block-header');
                    if (titleInput) titleInput.name = `dreams[${newIndex}][title]`;
                    if (descriptionInput) descriptionInput.name = `dreams[${newIndex}][description]`;
                    if (typeSelect) {
                        typeSelect.name = `dreams[${newIndex}][dream_type]`;
                        typeSelect.dataset.blockIndex = newIndex;
                    }
                    if (header) header.textContent = `Блок #${newIndex + 1}`;
                });
                updateBlockTypeOptions();
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
                
                // Проверяем, что есть хотя бы один блок и хотя бы один сон (не только контекст)
                const blocks = document.querySelectorAll('.dream-item');
                if (blocks.length === 0) {
                    e.preventDefault();
                    alert('Пожалуйста, добавьте хотя бы один блок');
                    return false;
                }

                let dreamCount = 0;
                let hasErrors = false;
                let hasAnyTitle = false;

                blocks.forEach((block) => {
                    const typeSelect = block.querySelector('select.block-type-select');
                    const isContext = typeSelect && typeSelect.value === BLOCK_TYPE_CONTEXT;
                    const title = block.querySelector(`input[name^="dreams["]`);
                    const description = block.querySelector(`textarea[name^="dreams["]`);

                    if (!isContext) {
                        dreamCount++;
                        if (title && title.value.trim()) hasAnyTitle = true;
                        const req = description && description.value.trim();
                        if (!req) {
                            hasErrors = true;
                            description?.classList.add('border-red-500');
                        } else {
                            description?.classList.remove('border-red-500');
                        }
                    } else {
                        description?.classList.remove('border-red-500');
                    }
                });

                if (dreamCount === 0) {
                    e.preventDefault();
                    alert('Добавьте хотя бы один сон (блок с типом сна, не только «Контекст»)');
                    return false;
                }
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

            // Проверка на серию снов (разделитель ---)
            function checkDreamSeries(textarea) {
                const text = textarea.value;
                const dreamIndex = textarea.dataset.dreamIndex;
                const countDiv = document.getElementById(`dream-count-${dreamIndex}`);
                
                if (!countDiv) return;
                
                // Проверяем наличие разделителя (3 и более дефисов)
                const hasSeparator = /---+/.test(text);
                
                if (hasSeparator) {
                    // Разбиваем текст по разделителю
                    const parts = text.split(/---+/).filter(part => part.trim() !== '');
                    const dreamCount = parts.length;
                    
                    if (dreamCount > 1) {
                        countDiv.style.display = 'block';
                        countDiv.className = 'mt-2 text-sm font-semibold text-green-600 dark:text-green-400';
                        countDiv.innerHTML = `<i class="fas fa-info-circle mr-1"></i>Обнаружено ${dreamCount} снов в этом окне (будут разделены при сохранении)`;
                    } else {
                        countDiv.style.display = 'none';
                    }
                } else {
                    countDiv.style.display = 'none';
                }
            }
        </script>
@endsection
