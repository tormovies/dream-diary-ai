<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Создать статью или инструкцию') }}
            </h2>
            <a href="{{ route('admin.articles.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Назад
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="profile-form-section card-shadow">
                <form method="POST" action="{{ route('admin.articles.store') }}" enctype="multipart/form-data" id="article-form" class="profile-form">
                    @csrf

                    <!-- Заголовок -->
                    <div class="form-group">
                        <label for="title" class="form-label required">
                            <i class="fas fa-heading"></i> Заголовок
                        </label>
                        <input type="text" id="title" name="title" class="form-input" value="{{ old('title') }}" required />
                        <x-input-error class="mt-2" :messages="$errors->get('title')" />
                    </div>

                    <!-- Slug -->
                    <div class="form-group">
                        <label for="slug" class="form-label">
                            <i class="fas fa-link"></i> URL (slug)
                        </label>
                        <input type="text" id="slug" name="slug" class="form-input" value="{{ old('slug') }}" />
                        <div class="form-hint">Автоматически генерируется из заголовка, можно отредактировать вручную</div>
                        <x-input-error class="mt-2" :messages="$errors->get('slug')" />
                    </div>

                    <!-- Тип, Статус, Порядок -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 md:gap-8">
                        <div class="form-group md:pr-4">
                            <label for="type" class="form-label required">
                                <i class="fas fa-file-alt"></i> Тип
                            </label>
                            <select id="type" name="type" class="form-select" required>
                                <option value="guide" {{ old('type') === 'guide' ? 'selected' : '' }}>Инструкция</option>
                                <option value="article" {{ old('type') === 'article' ? 'selected' : '' }}>Статья</option>
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('type')" />
                        </div>

                        <div class="form-group md:px-4">
                            <label for="status" class="form-label required">
                                <i class="fas fa-toggle-on"></i> Статус
                            </label>
                            <select id="status" name="status" class="form-select" required>
                                <option value="draft" {{ old('status', 'draft') === 'draft' ? 'selected' : '' }}>Черновик</option>
                                <option value="published" {{ old('status') === 'published' ? 'selected' : '' }}>Опубликовано</option>
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('status')" />
                        </div>

                        <div class="form-group md:pl-4">
                            <label for="order" class="form-label">
                                <i class="fas fa-sort-numeric-down"></i> Порядок сортировки
                            </label>
                            <input type="number" id="order" name="order" min="0" class="form-input" value="{{ old('order', 0) }}" />
                            <x-input-error class="mt-2" :messages="$errors->get('order')" />
                        </div>
                    </div>

                    <!-- Изображение -->
                    <div class="form-group">
                        <label for="image" class="form-label">
                            <i class="fas fa-image"></i> Заголовочное изображение (опционально)
                        </label>
                        <input type="file" id="image" name="image" accept="image/*" class="form-input" />
                        <div class="form-hint">Максимальный размер: 2MB. Используется для OG изображения</div>
                        <x-input-error class="mt-2" :messages="$errors->get('image')" />
                    </div>

                    <!-- Содержимое -->
                    <div class="form-group">
                        <label for="content" class="form-label required">
                            <i class="fas fa-align-left"></i> Текст статьи
                        </label>
                        <div id="editor-container" style="min-height: 450px;" class="mt-1 border border-gray-300 dark:border-gray-600 rounded-lg overflow-hidden bg-white dark:bg-gray-700"></div>
                        <textarea id="content" name="content" style="display: none;">{{ old('content') }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('content')" />
                    </div>

                    <!-- Превью вопросов (для инструкций) -->
                    <div class="form-group">
                        <label for="questions_preview" class="form-label">
                            <i class="fas fa-list"></i> Список вопросов (превью для главной страницы)
                        </label>
                        <textarea id="questions_preview" name="questions_preview" rows="6" class="form-textarea" placeholder="Каждый вопрос с новой строки. Например:&#10;Что такое Дневник сновидений?&#10;Зачем регистрироваться?&#10;Как зарегистрироваться?">{{ old('questions_preview') }}</textarea>
                        <div class="form-hint">Укажите список вопросов, которые раскрыты в этой инструкции. Каждый вопрос с новой строки. Это будет отображаться на главной странице /guide как превью.</div>
                        <x-input-error class="mt-2" :messages="$errors->get('questions_preview')" />
                    </div>

                    <!-- SEO настройки -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="form-group">
                            <label for="seo_title" class="form-label">
                                <i class="fas fa-tag"></i> Meta Title
                            </label>
                            <input type="text" id="seo_title" name="seo_title" class="form-input" value="{{ old('seo_title') }}" maxlength="60" />
                            <div class="form-hint">Рекомендуется 55-60 символов</div>
                            <x-input-error class="mt-2" :messages="$errors->get('seo_title')" />
                        </div>

                        <div class="form-group">
                            <label for="seo_h1" class="form-label">
                                <i class="fas fa-heading"></i> H1
                            </label>
                            <input type="text" id="seo_h1" name="seo_h1" class="form-input" value="{{ old('seo_h1') }}" />
                            <x-input-error class="mt-2" :messages="$errors->get('seo_h1')" />
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="seo_description" class="form-label">
                            <i class="fas fa-align-left"></i> Meta Description
                        </label>
                        <textarea id="seo_description" name="seo_description" rows="3" class="form-textarea" maxlength="160">{{ old('seo_description') }}</textarea>
                        <div class="form-hint">Рекомендуется 150-160 символов. OG Description будет равен этому полю, если заполнено</div>
                        <x-input-error class="mt-2" :messages="$errors->get('seo_description')" />
                    </div>

                    <div class="form-group">
                        <label for="seo_h1_description" class="form-label">
                            <i class="fas fa-paragraph"></i> Описание под H1
                        </label>
                        <textarea id="seo_h1_description" name="seo_h1_description" rows="4" class="form-textarea">{{ old('seo_h1_description') }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('seo_h1_description')" />
                    </div>

                    <div class="form-actions">
                        <a href="{{ route('admin.articles.index') }}" class="btn-form-secondary">
                            Отмена
                        </a>
                        <button type="submit" class="btn-form-primary">
                            <i class="fas fa-save mr-2"></i>Создать статью
                        </button>
                    </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <link href="{{ asset('js/quill/quill.snow.css') }}" rel="stylesheet">
    <style>
        /* Стилизация Quill редактора */
        #editor-container .ql-container {
            font-size: 15px;
            line-height: 1.6;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }
        #editor-container .ql-editor {
            min-height: 400px;
            padding: 20px;
        }
        #editor-container .ql-toolbar {
            border-top-left-radius: 0.5rem;
            border-top-right-radius: 0.5rem;
            border-bottom: 2px solid #e5e7eb;
            background-color: #f9fafb;
            padding: 12px;
        }
        .dark #editor-container .ql-toolbar {
            background-color: #374151;
            border-bottom-color: #4b5563;
        }
        #editor-container .ql-toolbar .ql-stroke {
            stroke: #4b5563;
        }
        .dark #editor-container .ql-toolbar .ql-stroke {
            stroke: #d1d5db;
        }
        #editor-container .ql-toolbar .ql-fill {
            fill: #4b5563;
        }
        .dark #editor-container .ql-toolbar .ql-fill {
            fill: #d1d5db;
        }
        #editor-container .ql-toolbar button:hover,
        #editor-container .ql-toolbar button.ql-active {
            color: #6366f1;
        }
        .dark #editor-container .ql-toolbar button:hover,
        .dark #editor-container .ql-toolbar button.ql-active {
            color: #818cf8;
        }
    </style>
    <script>
        console.log('Article create script started');
        
        // Загружаем Quill динамически
        (function() {
            var quillScript = document.createElement('script');
            quillScript.src = '{{ asset('js/quill/quill.min.js') }}';
            quillScript.onload = function() {
                console.log('Quill script loaded');
                if (typeof initQuill === 'function') {
                    initQuill();
                } else {
                    setTimeout(function() {
                        if (typeof initQuill === 'function') {
                            initQuill();
                        }
                    }, 100);
                }
            };
            quillScript.onerror = function() {
                console.error('Failed to load Quill script from:', quillScript.src);
            };
            document.head.appendChild(quillScript);
        })();
    </script>
    <script>
        // Функция транслитерации
        function transliterate(text) {
            var map = {
                'а': 'a', 'б': 'b', 'в': 'v', 'г': 'g', 'д': 'd', 'е': 'e', 'ё': 'yo',
                'ж': 'zh', 'з': 'z', 'и': 'i', 'й': 'y', 'к': 'k', 'л': 'l', 'м': 'm',
                'н': 'n', 'о': 'o', 'п': 'p', 'р': 'r', 'с': 's', 'т': 't', 'у': 'u',
                'ф': 'f', 'х': 'h', 'ц': 'ts', 'ч': 'ch', 'ш': 'sh', 'щ': 'sch',
                'ъ': '', 'ы': 'y', 'ь': '', 'э': 'e', 'ю': 'yu', 'я': 'ya'
            };
            text = text.toLowerCase();
            var result = '';
            for (var i = 0; i < text.length; i++) {
                var char = text[i];
                result += map[char] || (char.match(/[a-z0-9]/) ? char : '-');
            }
            return result.replace(/-+/g, '-').replace(/^-|-$/g, '');
        }

        var quillEditor = null;

        function initQuill() {
            console.log('Initializing Quill...');
            console.log('Quill available:', typeof Quill !== 'undefined');
            
            var editorContainer = document.getElementById('editor-container');
            var contentTextarea = document.getElementById('content');
            
            console.log('Editor container found:', !!editorContainer);
            console.log('Content textarea found:', !!contentTextarea);
            
            if (!editorContainer) {
                console.error('Editor container not found!');
                return;
            }
            
            if (typeof Quill === 'undefined') {
                console.error('Quill library not loaded!');
                setTimeout(initQuill, 100);
                return;
            }
            
            try {
                quillEditor = new Quill('#editor-container', {
                    theme: 'snow',
                    modules: {
                        toolbar: [
                            [{ 'header': [1, 2, 3, false] }],
                            ['bold', 'italic', 'underline', 'strike'],
                            [{ 'color': [] }, { 'background': [] }],
                            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                            [{ 'align': [] }],
                            ['link', 'image', 'video'],
                            ['blockquote', 'code-block'],
                            ['clean']
                        ]
                    },
                    placeholder: 'Введите текст статьи...'
                });

                // Загружаем существующее содержимое
                if (contentTextarea && contentTextarea.value) {
                    quillEditor.root.innerHTML = contentTextarea.value;
                }

                // Обработчик загрузки изображений
                var toolbar = quillEditor.getModule('toolbar');
                toolbar.addHandler('image', function() {
                    var input = document.createElement('input');
                    input.setAttribute('type', 'file');
                    input.setAttribute('accept', 'image/*');
                    input.click();
                    
                    input.onchange = function() {
                        var file = input.files[0];
                        if (file) {
                            var formData = new FormData();
                            formData.append('file', file);
                            
                            var xhr = new XMLHttpRequest();
                            xhr.open('POST', '{{ route('admin.articles.image-upload') }}');
                            xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');
                            
                            xhr.onload = function() {
                                if (xhr.status === 200) {
                                    var response = JSON.parse(xhr.responseText);
                                    if (response.location) {
                                        var range = quillEditor.getSelection(true);
                                        quillEditor.insertEmbed(range.index, 'image', response.location);
                                    }
                                } else {
                                    alert('Ошибка загрузки изображения');
                                }
                            };
                            
                            xhr.onerror = function() {
                                alert('Ошибка загрузки изображения');
                            };
                            
                            xhr.send(formData);
                        }
                    };
                });

                // Обработчик загрузки видео
                toolbar.addHandler('video', function() {
                    var url = prompt('Введите URL видео:');
                    if (url) {
                        var range = quillEditor.getSelection(true);
                        quillEditor.insertEmbed(range.index, 'video', url);
                    }
                });

                console.log('Quill editor initialized successfully');
            } catch (e) {
                console.error('Error initializing Quill:', e);
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded');
            
            // Автогенерация slug
            var titleInput = document.getElementById('title');
            var slugInput = document.getElementById('slug');
            if (titleInput && slugInput) {
                titleInput.addEventListener('input', function() {
                    if (!slugInput.value || slugInput.dataset.autoGenerated === 'true') {
                        slugInput.value = transliterate(this.value);
                        slugInput.dataset.autoGenerated = 'true';
                    }
                });
                slugInput.addEventListener('input', function() {
                    this.dataset.autoGenerated = 'false';
                });
            }
            
            // Инициализация Quill будет вызвана после загрузки скрипта
            // (см. обработчик onload выше)
            
            // Синхронизация Quill с textarea перед отправкой формы
            var form = document.getElementById('article-form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    var contentTextarea = document.getElementById('content');
                    if (quillEditor && contentTextarea) {
                        var html = quillEditor.root.innerHTML;
                        var text = quillEditor.getText().trim();
                        
                        console.log('Form submit - HTML length:', html.length);
                        console.log('Form submit - Text length:', text.length);
                        
                        if (!text) {
                            e.preventDefault();
                            alert('Пожалуйста, заполните поле "Текст статьи"');
                            quillEditor.focus();
                            return false;
                        }
                        
                        // Сохраняем HTML в textarea
                        contentTextarea.value = html;
                        console.log('Content saved to textarea, length:', contentTextarea.value.length);
                    } else {
                        console.error('Quill editor or textarea not found!', {
                            quillEditor: !!quillEditor,
                            contentTextarea: !!contentTextarea
                        });
                    }
                });
            }
        });
    </script>
</x-app-layout>
