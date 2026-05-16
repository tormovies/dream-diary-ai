@extends('layouts.base')

@section('title', 'Перенос в дневник — '.config('app.name'))

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 card-shadow border border-gray-200 dark:border-gray-700 mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-purple-600 dark:text-purple-400">Перенос в дневник</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Будет создан опубликованный отчёт с текстом сна и результатом толкования.</p>
        </div>
        <a href="{{ route('dream-interpretations.index') }}" class="inline-flex items-center text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white shrink-0">
            <i class="fas fa-arrow-left mr-2"></i>К списку толкований
        </a>
    </div>

    <div class="profile-form-section card-shadow">
        @if ($errors->any())
            <div class="mb-4 bg-red-100 dark:bg-red-900 border border-red-400 dark:border-red-700 text-red-700 dark:text-red-300 px-4 py-3 rounded-lg">
                <ul class="list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 bg-red-100 dark:bg-red-900 border border-red-400 dark:border-red-700 text-red-700 dark:text-red-300 px-4 py-3 rounded-lg text-sm">
                {{ session('error') }}
            </div>
        @endif

        <div class="mb-6 rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-900/40 p-4">
            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Фрагмент описания сна</p>
            <p class="text-sm text-gray-800 dark:text-gray-200 whitespace-pre-wrap">{{ \Illuminate\Support\Str::limit(trim($interpretation->dream_description), 600) }}</p>
            <p class="mt-3 text-sm">
                <a href="{{ route('dream-analyzer.show', $interpretation->hash) }}" class="text-purple-600 underline dark:text-purple-400">Открыть полное толкование</a>
            </p>
        </div>

        <form method="POST" action="{{ route('dream-interpretations.transfer.store', $interpretation->hash) }}" class="profile-form">
            @csrf

            <div class="form-group">
                <label for="report_date" class="form-label required">
                    <i class="fas fa-calendar"></i> Дата записи в дневнике
                </label>
                <input type="date"
                       id="report_date"
                       name="report_date"
                       class="form-input"
                       value="{{ old('report_date', now()->format('Y-m-d')) }}"
                       required />
                <x-input-error :messages="$errors->get('report_date')" class="mt-2" />
                <p class="form-hint mt-1">Только дата; можно указать любой день, в том числе в прошлом.</p>
            </div>

            <div class="form-group">
                <label for="access_level" class="form-label required">
                    <i class="fas fa-lock"></i> Видимость отчёта в дневнике
                </label>
                <select id="access_level"
                        name="access_level"
                        class="form-select"
                        required>
                    <option value="all" {{ old('access_level', $defaultAccess) === 'all' ? 'selected' : '' }}>Всем</option>
                    <option value="friends" {{ old('access_level', $defaultAccess) === 'friends' ? 'selected' : '' }}>Только друзьям</option>
                    <option value="none" {{ old('access_level', $defaultAccess) === 'none' ? 'selected' : '' }}>Никому</option>
                </select>
                <x-input-error :messages="$errors->get('access_level')" class="mt-2" />
            </div>

            <div class="form-group">
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="hidden" name="allow_public_linking" value="0" />
                    <input type="checkbox"
                           name="allow_public_linking"
                           value="1"
                           class="mt-1 rounded border-gray-300 text-purple-600 shadow-sm focus:ring-purple-500 dark:border-gray-600 dark:bg-gray-900"
                           {{ old('allow_public_linking', $interpretation->allow_public_linking) ? 'checked' : '' }} />
                    <span>
                        <span class="form-label mb-0">Разрешить публичную страницу толкования по ссылке</span>
                        <span class="form-hint block mt-1">Если отметить, сохранённые настройки для отдельной страницы толкования (шаринг по хешу) останутся доступны по желанию; снимите, если хотите только запись в дневнике.</span>
                    </span>
                </label>
                <x-input-error :messages="$errors->get('allow_public_linking')" class="mt-2" />
            </div>

            <div class="flex flex-col sm:flex-row gap-3 pt-2">
                <button type="submit" class="inline-flex justify-center items-center rounded-lg bg-purple-600 px-6 py-3 text-sm font-semibold text-white hover:bg-purple-700">
                    <i class="fas fa-book mr-2"></i>Перенести в дневник
                </button>
                <a href="{{ route('dream-analyzer.show', $interpretation->hash) }}" class="inline-flex justify-center items-center rounded-lg border border-gray-300 dark:border-gray-600 px-6 py-3 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">
                    Отмена
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
