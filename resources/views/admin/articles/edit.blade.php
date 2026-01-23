<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Редактировать статью') }}
            </h2>
            <a href="{{ route('admin.articles.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Назад
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="profile-form-section card-shadow">
                <form method="POST" action="{{ route('admin.articles.update', $article) }}" enctype="multipart/form-data" id="article-form" class="profile-form">
                    @csrf
                    @method('PUT')

                    <!-- Заголовок -->
                    <div class="form-group">
                        <label for="title" class="form-label required">
                            <i class="fas fa-heading"></i> Заголовок
                        </label>
                        <input type="text" id="title" name="title" class="form-input" value="{{ old('title', $article->title) }}" required />
                        <x-input-error class="mt-2" :messages="$errors->get('title')" />
                    </div>

                    <!-- Slug -->
                    <div class="form-group">
                        <label for="slug" class="form-label">
                            <i class="fas fa-link"></i> URL (slug)
                        </label>
                        <input type="text" id="slug" name="slug" class="form-input" value="{{ old('slug', $article->slug) }}" />
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
                                <option value="guide" {{ old('type', $article->type) === 'guide' ? 'selected' : '' }}>Инструкция</option>
                                <option value="article" {{ old('type', $article->type) === 'article' ? 'selected' : '' }}>Статья</option>
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('type')" />
                        </div>

                        <div class="form-group md:px-4">
                            <label for="status" class="form-label required">
                                <i class="fas fa-toggle-on"></i> Статус
                            </label>
                            <select id="status" name="status" class="form-select" required>
                                <option value="draft" {{ old('status', $article->status) === 'draft' ? 'selected' : '' }}>Черновик</option>
                                <option value="published" {{ old('status', $article->status) === 'published' ? 'selected' : '' }}>Опубликовано</option>
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('status')" />
                        </div>

                        <div class="form-group md:pl-4">
                            <label for="order" class="form-label">
                                <i class="fas fa-sort-numeric-down"></i> Порядок сортировки
                            </label>
                            <input type="number" id="order" name="order" min="0" class="form-input" value="{{ old('order', $article->order) }}" />
                            <x-input-error class="mt-2" :messages="$errors->get('order')" />
                        </div>
                    </div>

                    <!-- Изображение -->
                    <div class="form-group">
                        <label for="image" class="form-label">
                            <i class="fas fa-image"></i> Заголовочное изображение (опционально)
                        </label>
                        @if($article->image)
                            <div class="mb-3">
                                <img src="{{ asset('storage/' . $article->image) }}" alt="Текущее изображение" class="max-w-xs h-auto rounded-lg border border-gray-300 dark:border-gray-600 shadow-sm">
                            </div>
                        @endif
                        <input type="file" id="image" name="image" accept="image/*" class="form-input" />
                        <div class="form-hint">Максимальный размер: 2MB. Используется для OG изображения</div>
                        <x-input-error class="mt-2" :messages="$errors->get('image')" />
                    </div>

                    <!-- Содержимое -->
                    <div class="form-group">
                        <label for="content" class="form-label required">
                            <i class="fas fa-align-left"></i> Текст статьи
                        </label>
                        <textarea id="content" name="content" class="mt-1" style="min-height: 450px;">{!! old('content', $article->content ?? '') !!}</textarea>
                        <script>
                            // Сохраняем контент в глобальную переменную для отладки
                            (function() {
                                var contentFromTextarea = document.getElementById('content');
                                if (contentFromTextarea) {
                                    var contentValue = contentFromTextarea.value || '';
                                    window.articleContent = contentValue;
                                    console.log('=== Article Content Debug ===');
                                    console.log('Textarea found:', !!contentFromTextarea);
                                    console.log('Content length:', contentValue ? contentValue.length : 0);
                                    console.log('Content preview (first 500 chars):', contentValue ? contentValue.substring(0, 500) : 'EMPTY');
                                    console.log('Content preview (last 200 chars):', contentValue && contentValue.length > 200 ? contentValue.substring(contentValue.length - 200) : 'N/A');
                                    console.log('Is empty?', !contentValue || contentValue.trim() === '' || contentValue.trim() === '<p><br></p>');
                                    console.log('Raw textarea value exists:', !!contentFromTextarea.value);
                                    console.log('Textarea innerHTML length:', contentFromTextarea.innerHTML ? contentFromTextarea.innerHTML.length : 0);
                                    console.log('===========================');
                                } else {
                                    console.error('Textarea #content not found when trying to store content!');
                                }
                            })();
                        </script>
                        <x-input-error class="mt-2" :messages="$errors->get('content')" />
                    </div>

                    <!-- Превью вопросов (для инструкций) -->
                    <div class="form-group">
                        <label for="questions_preview" class="form-label">
                            <i class="fas fa-list"></i> Список вопросов (превью для главной страницы)
                        </label>
                        <textarea id="questions_preview" name="questions_preview" rows="6" class="form-textarea" placeholder="Каждый вопрос с новой строки. Например:&#10;Что такое Дневник сновидений?&#10;Зачем регистрироваться?&#10;Как зарегистрироваться?">{{ old('questions_preview', $article->questions_preview) }}</textarea>
                        <div class="form-hint">Укажите список вопросов, которые раскрыты в этой инструкции. Каждый вопрос с новой строки. Это будет отображаться на главной странице /guide как превью.</div>
                        <x-input-error class="mt-2" :messages="$errors->get('questions_preview')" />
                    </div>

                    <!-- SEO настройки -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="form-group">
                            <label for="seo_title" class="form-label">
                                <i class="fas fa-tag"></i> Meta Title
                            </label>
                            <input type="text" id="seo_title" name="seo_title" class="form-input" value="{{ old('seo_title', $seoMeta->title ?? '') }}" maxlength="60" />
                            <div class="form-hint">Рекомендуется 55-60 символов. OG Title будет равен этому полю, если заполнено</div>
                            <x-input-error class="mt-2" :messages="$errors->get('seo_title')" />
                        </div>

                        <div class="form-group">
                            <label for="seo_h1" class="form-label">
                                <i class="fas fa-heading"></i> H1
                            </label>
                            <input type="text" id="seo_h1" name="seo_h1" class="form-input" value="{{ old('seo_h1', $seoMeta->h1 ?? '') }}" />
                            <x-input-error class="mt-2" :messages="$errors->get('seo_h1')" />
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="seo_description" class="form-label">
                            <i class="fas fa-align-left"></i> Meta Description
                        </label>
                        <textarea id="seo_description" name="seo_description" rows="3" class="form-textarea" maxlength="160">{{ old('seo_description', $seoMeta->description ?? '') }}</textarea>
                        <div class="form-hint">Рекомендуется 150-160 символов. OG Description будет равен этому полю, если заполнено</div>
                        <x-input-error class="mt-2" :messages="$errors->get('seo_description')" />
                    </div>

                    <div class="form-group">
                        <label for="seo_h1_description" class="form-label">
                            <i class="fas fa-paragraph"></i> Описание под H1
                        </label>
                        <textarea id="seo_h1_description" name="seo_h1_description" rows="4" class="form-textarea">{{ old('seo_h1_description', $seoMeta->h1_description ?? '') }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('seo_h1_description')" />
                    </div>

                    <div class="form-actions">
                        <a href="{{ route('admin.articles.index') }}" class="btn-form-secondary">
                            Отмена
                        </a>
                        <button type="submit" class="btn-form-primary">
                            <i class="fas fa-save mr-2"></i>Сохранить изменения
                        </button>
                    </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/tinymce/tinymce.min.js') }}"></script>
    <style>
        /* Стилизация TinyMCE редактора */
        .tox-tinymce {
            border-radius: 0.5rem !important;
        }
        .tox .tox-editor-header {
            border-top-left-radius: 0.5rem !important;
            border-top-right-radius: 0.5rem !important;
        }
    </style>
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

        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded - initializing TinyMCE');
            
            // Автогенерация slug
            var titleInput = document.getElementById('title');
            var slugInput = document.getElementById('slug');
            if (titleInput && slugInput && !slugInput.value) {
                titleInput.addEventListener('input', function() {
                    if (!slugInput.value || slugInput.dataset.autoGenerated === 'true') {
                        slugInput.value = transliterate(this.value);
                        slugInput.dataset.autoGenerated = 'true';
                    }
                });
            }
            if (slugInput) {
                slugInput.addEventListener('input', function() {
                    this.dataset.autoGenerated = 'false';
                });
            }
            
            // Инициализация TinyMCE
            function initEditor() {
                if (typeof tinymce !== 'undefined') {
                    console.log('TinyMCE loaded, initializing editor');
                    tinymce.init({
                        license_key: 'gpl',
                        selector: '#content',
                        height: 600,
                        menubar: false,
                        plugins: [
                            'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                            'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                            'insertdatetime', 'media', 'table', 'code', 'help', 'wordcount'
                        ],
                        toolbar: 'undo redo | blocks | ' +
                            'bold italic underline strikethrough | forecolor backcolor | ' +
                            'alignleft aligncenter alignright alignjustify | ' +
                            'bullist numlist | outdent indent | ' +
                            'link image | code | fullscreen | help',
                        content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; font-size: 15px; line-height: 1.6; }',
                        // Важно: сохраняем все теги и атрибуты, включая details и summary
                        valid_elements: '*[*]',
                        extended_valid_elements: '*[*]',
                        valid_children: '*[*]',
                        cleanup: false,
                        verify_html: false,
                        // Отключаем автоматическую очистку HTML
                        remove_trailing_brs: false,
                        // Разрешаем details и summary в схеме
                        schema: 'html5',
                        // Отключаем все фильтры, которые могут удалить details/summary
                        convert_urls: false,
                        remove_script_host: false,
                        // Сохраняем HTML как есть
                        preserve_cdata: true,
                        // Используем p как корневой блок (по умолчанию)
                        forced_root_block: 'p',
                        // Путь к скинам и плагинам
                        skin_url: '{{ asset('js/tinymce/skins/ui/oxide') }}',
                        content_css: '{{ asset('js/tinymce/skins/content/default/content.min.css') }}',
                        // Отключаем загрузку плагина licensekeymanager
                        plugins_url: '{{ asset('js/tinymce') }}',
                        // Настройка загрузки изображений
                        images_upload_handler: function (blobInfo, progress) {
                            return new Promise(function (resolve, reject) {
                                var formData = new FormData();
                                formData.append('file', blobInfo.blob(), blobInfo.filename());
                                
                                var xhr = new XMLHttpRequest();
                                xhr.open('POST', '{{ route('admin.articles.image-upload') }}');
                                xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');
                                
                                xhr.upload.onprogress = function (e) {
                                    progress(e.loaded / e.total * 100);
                                };
                                
                                xhr.onload = function () {
                                    if (xhr.status === 200) {
                                        var response = JSON.parse(xhr.responseText);
                                        if (response.location) {
                                            resolve(response.location);
                                        } else {
                                            reject('Invalid JSON: ' + xhr.responseText);
                                        }
                                    } else {
                                        reject('HTTP Error: ' + xhr.status);
                                    }
                                };
                                
                                xhr.onerror = function () {
                                    reject('Image upload failed');
                                };
                                
                                xhr.send(formData);
                            });
                        },
                        setup: function(editor) {
                            console.log('TinyMCE editor initialized successfully');
                            
                            // При загрузке контента - загружаем напрямую из textarea
                            editor.on('init', function() {
                                var textarea = document.getElementById('content');
                                if (textarea && textarea.value) {
                                    var content = textarea.value;
                                    console.log('Loading content from textarea, length:', content.length);
                                    console.log('Contains details:', content.includes('<details'));
                                    
                                    // Загружаем контент напрямую в body, обходя обработку TinyMCE
                                    editor.getBody().innerHTML = content;
                                    
                                    // Проверяем, что details загрузились
                                    var detailsElements = editor.getBody().querySelectorAll('details');
                                    console.log('Details elements after load:', detailsElements.length);
                                    
                                    if (detailsElements.length === 0 && content.includes('<details')) {
                                        console.warn('Details were removed! Trying to restore...');
                                        // Пытаемся восстановить
                                        editor.getBody().innerHTML = content;
                                    }
                                }
                            });
                            
                            // При получении контента - всегда используем innerHTML
                            editor.on('GetContent', function(e) {
                                // Всегда получаем HTML напрямую из DOM
                                if (editor.getBody().querySelector('details')) {
                                    e.content = editor.getBody().innerHTML;
                                    console.log('Content retrieved from DOM, contains details:', e.content.includes('<details'));
                                }
                            });
                        }
                    });
                } else {
                    console.log('Waiting for TinyMCE to load...');
                    setTimeout(initEditor, 100);
                }
            }
            
            initEditor();
            
            // Обработчик отправки формы - сохраняем содержимое TinyMCE напрямую
            var form = document.getElementById('article-form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    // Получаем редактор
                    if (typeof tinymce !== 'undefined') {
                        var editor = tinymce.get('content');
                        if (editor) {
                            // Получаем HTML напрямую из DOM, обходя возможную очистку TinyMCE
                            // Используем innerHTML напрямую, чтобы получить весь HTML без обработки
                            var content = editor.getBody().innerHTML;
                            
                            console.log('Saving content from editor body, length:', content.length);
                            console.log('Contains details:', content.includes('<details'));
                            console.log('Contains summary:', content.includes('<summary'));
                            
                            // Сохраняем в textarea
                            var textarea = document.getElementById('content');
                            if (textarea) {
                                textarea.value = content;
                                console.log('Content saved to textarea, length:', content.length);
                                console.log('Contains details:', content.includes('<details'));
                            }
                        }
                    }
                });
            }
        });
    </script>
</x-app-layout>
