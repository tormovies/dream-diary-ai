# Анализ CSS по всем публичным страницам (кроме админки)

**Дата:** 2026-01-26

---

## 1. Какие страницы есть и что грузят

### Базово (все страницы на `layouts.base`)

- **app.css** + **app.js** — всегда через `@vite` в layout.
- **@stack('styles')** — опционально, страницы могут добавлять свои `<style>` через `@push('styles')`.

### Доп. CSS по страницам

| Страница | View | Доп. CSS | @push('styles') |
|----------|------|----------|------------------|
| `/` | welcome | — | нет |
| `/activity` | activity/index | — | да, дубли |
| `/search` | search | — | нет |
| `/diary/{link}` | diary/public | — | да, дубли |
| `/diary/user/{user}` | diary/show | — | да, дубли |
| `/users/{user}` | users/profile | — | нет |
| `/users` (поиск) | users/search | — | нет |
| `/dashboard` | dashboard | — | нет |
| `/tolkovanie-snov` | dream-analyzer/create | — | нет |
| `/tolkovanie-snov/{hash}` | dream-analyzer/show | — | да, только #toast |
| `/guide` | articles/guide/index | **articles.css** | нет |
| `/guide/{slug}` | articles/show | **articles.css** | нет |
| `/articles` | articles/articles/index | — | нет |
| `/articles/{slug}` | articles/show | **articles.css** | нет |
| `/reports/{id}` | reports/show | — | нет |
| `/reports/{id}/analysis` | reports/analysis | — | да, только #toast |
| `/reports/create`, edit | reports/create, edit | — | нет |
| `/profile` | profile/edit | — | нет |
| `/statistics` | statistics/index | нет | нет |
| `/notifications` | notifications/index | — | да, дубли |
| Auth (login, register, forgot, verify, confirm, reset) | auth/* | — | да, общие auth-стили |

**articles.css** подключается только там, где есть контент статей/инструкций (prose, спойлеры, вопросы и т.д.) — это сделано оптимально.

---

## 2. Что не так

### 2.1. Дубли в `@push('styles')`

В **app.css** уже есть:

- `.gradient-primary`, `.card-shadow`
- `.main-grid`, `.profile-grid`, `.two-column-grid`
- `.sidebar-menu`
- `#toast`, `#toast.show`
- `.form-group`, `.form-label`, `.form-input`, `.password-container`, `.toggle-password`, `.form-checkbox`, `.checkbox-*` и т.д.

При этом те же правила повторяются в `<style>` на страницах:

| Страница | Что дублируется |
|----------|------------------|
| **activity/index** | gradient-primary, card-shadow, main-grid, sidebar-menu |
| **diary/show** | gradient-primary, card-shadow, profile-grid, sidebar-menu |
| **diary/public** | gradient-primary, card-shadow, profile-grid |
| **notifications/index** | gradient-primary, card-shadow, profile-grid |
| **reports/analysis** | только `#toast.show` |
| **dream-analyzer/show** | только `#toast.show` |

Итого: лишние инлайновые стили, большие блоки дублей, раздутый HTML.

### 2.2. Auth-страницы

На **login, register, forgot-password, verify-email, confirm-password, reset-password** в `@push('styles')`:

- Дубли: `.card-shadow`, `.form-group`, `.form-label`, `.form-input`, `.password-container`, `.toggle-password`, `.form-checkbox`, `.checkbox-*` (всё уже в app.css).
- Уникальное только для auth:  
  `.registration-card`, `.registration-header`, `.registration-title`, `.registration-subtitle`,  
  `.registration-form`, `.registration-footer`, `.form-description`, `.btn-primary`  
  и медиа для `.registration-card` / `.registration-title`.

Эти уникальные стили лучше держать в **app.css**, а дубли из auth-страниц убрать.

---

## 3. Рекомендации (сделано в рамках рефакторинга)

1. **Удалить `@push('styles')`** целиком на:
   - **activity/index**, **diary/show**, **diary/public**, **notifications/index** — всё уже в app.css.
   - **reports/analysis**, **dream-analyzer/show** — используется только `#toast.show` из app.css.

2. **Перенести auth-стили в app.css**:
   - Добавить блок «Auth / registration»:
     - `.registration-card`, `.registration-header`, `.registration-title`, `.registration-subtitle`
     - `.registration-form`, `.registration-footer` (и ссылки в нём)
     - `.form-description`, `.btn-primary` (+ :hover)
     - `@media (max-width: 480px)` для карточки и заголовка.

3. **Удалить `@push('styles')`** на всех auth-страницах.

4. **Tailwind `content`** уже покрывает `./resources/**/*.blade.php` — менять не нужно.

5. **articles.css** оставить как есть: отдельный чанк только для статей/инструкций.

---

## 4. Итог по загрузке CSS

- **app.css** — общий фонд (сетки, карточки, формы, auth, toast и т.д.). Грузится везде.
- **articles.css** — только там, где есть статья/инструкция.
- Никаких дублей в инлайновых `<style>` на перечисленных страницах.
- Единая точка правок для общих и auth-стилей — **app.css**.

---

## 5. Выполненные изменения (2026-01-26)

- Удалён **@push('styles')** с дублями на: **activity**, **diary/show**, **diary/public**, **notifications**, **reports/analysis**, **dream-analyzer/show**.
- Auth-стили (`.registration-*`, `.btn-primary`, `.btn-secondary`, `.button-group`, `.form-description`) перенесены в **app.css**. **@push('styles')** убран на **login**, **register**, **forgot-password**, **confirm-password**, **reset-password**.
- Исправлен **verify-email**: убраны сломанные фрагменты layout из `@push`, восстановлены `@section('content')` и нормальная структура.
