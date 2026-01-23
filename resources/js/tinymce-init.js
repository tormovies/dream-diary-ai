// TinyMCE core (must be first)
import tinymce from 'tinymce/tinymce';

// Icons (required) - используем index.js
import 'tinymce/icons/default';

// Theme - используем index.js
import 'tinymce/themes/silver';

// DOM Model - используем index.js
import 'tinymce/models/dom';

// UI Skin CSS
import 'tinymce/skins/ui/oxide/skin.min.css';

// Content skin CSS
import 'tinymce/skins/content/default/content.min.css';

// Плагины
import 'tinymce/plugins/advlist';
import 'tinymce/plugins/autolink';
import 'tinymce/plugins/lists';
import 'tinymce/plugins/link';
import 'tinymce/plugins/image';
import 'tinymce/plugins/charmap';
import 'tinymce/plugins/preview';
import 'tinymce/plugins/anchor';
import 'tinymce/plugins/searchreplace';
import 'tinymce/plugins/visualblocks';
import 'tinymce/plugins/code';
import 'tinymce/plugins/fullscreen';
import 'tinymce/plugins/insertdatetime';
import 'tinymce/plugins/media';
import 'tinymce/plugins/table';
import 'tinymce/plugins/help';
import 'tinymce/plugins/wordcount';

// Делаем tinymce доступным глобально
window.tinymce = tinymce;

// Функция инициализации редактора
window.initTinyMCE = function(selector, options = {}) {
    const defaultOptions = {
        selector: selector,
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
        // Важно: сохраняем все теги и атрибуты
        valid_elements: '*[*]',
        extended_valid_elements: '*[*]',
        // Разрешаем все атрибуты, включая data-*
        valid_children: '+body[style]',
        // Сохраняем HTML как есть
        cleanup: false,
        verify_html: false,
        skin: 'oxide',
        content_css: false,
    };

    const finalOptions = { ...defaultOptions, ...options };
    
    return tinymce.init(finalOptions);
};
