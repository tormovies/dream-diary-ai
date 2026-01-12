import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

// Применяем тему ДО инициализации Alpine для предотвращения мерцания
// Это выполняется синхронно, до DOMContentLoaded
(function() {
    const savedTheme = localStorage.getItem('theme') || 'light';
    if (savedTheme === 'dark') {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
})();

Alpine.start();

// Переключение темы
window.toggleTheme = function() {
    const html = document.documentElement;
    const currentTheme = html.classList.contains('dark') ? 'dark' : 'light';
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    
    if (newTheme === 'dark') {
        html.classList.add('dark');
    } else {
        html.classList.remove('dark');
    }
    
    localStorage.setItem('theme', newTheme);
    
    // Сохраняем в базу данных, если пользователь авторизован
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (csrfToken) {
        fetch('/theme/toggle', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ theme: newTheme })
        }).catch(() => {}); // Удаляем console.error для продакшена
    }
};
