<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Реклама') }}
            </h2>
            <a href="{{ route('admin.dashboard') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Назад
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-300 px-4 py-3 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 dark:border-gray-700">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                        <i class="fas fa-ad mr-2"></i>Рекламные блоки на странице толкования
                    </h3>

                    <form method="POST" action="{{ route('admin.ad.update') }}">
                        @csrf
                        @method('PATCH')

                        <div class="form-group mb-6">
                            <label for="dream_analyzer_ad_code" class="form-label">Рекламный код (ожидание результата)</label>
                            <textarea id="dream_analyzer_ad_code"
                                      name="dream_analyzer_ad_code"
                                      rows="6"
                                      class="form-input font-mono text-sm"
                                      placeholder="<ins class='adsbygoogle' ...></ins>">{{ old('dream_analyzer_ad_code', $adCode) }}</textarea>
                            <div class="form-hint mt-1">Показывается во время ожидания ответа от DeepSeek. Оставьте пустым, чтобы не выводить.</div>
                            <x-input-error class="mt-2" :messages="$errors->get('dream_analyzer_ad_code')" />
                        </div>

                        <div class="form-group mb-6">
                            <label for="dream_analyzer_ad_code_results" class="form-label">Рекламный код (результаты)</label>
                            <textarea id="dream_analyzer_ad_code_results"
                                      name="dream_analyzer_ad_code_results"
                                      rows="6"
                                      class="form-input font-mono text-sm"
                                      placeholder="<ins class='adsbygoogle' ...></ins>">{{ old('dream_analyzer_ad_code_results', $adCodeResults) }}</textarea>
                            <div class="form-hint mt-1">Показывается на странице с готовым результатом толкования, перед блоком анализа. Оставьте пустым, чтобы не выводить.</div>
                            <x-input-error class="mt-2" :messages="$errors->get('dream_analyzer_ad_code_results')" />
                        </div>

                        <div class="form-group mb-6">
                            <label for="global_head_ad_code" class="form-label">Рекламный код (в &lt;head&gt;)</label>
                            <textarea id="global_head_ad_code"
                                      name="global_head_ad_code"
                                      rows="6"
                                      class="form-input font-mono text-sm"
                                      placeholder="<script>...</script>">{{ old('global_head_ad_code', $adCodeHead) }}</textarea>
                            <div class="form-hint mt-1">Вставляется между тегами &lt;head&gt; и &lt;/head&gt; на всех страницах сайта, кроме админки. Подходит для счётчиков, тегов ремаркетинга и т.п. Оставьте пустым, чтобы не выводить.</div>
                            <x-input-error class="mt-2" :messages="$errors->get('global_head_ad_code')" />
                        </div>

                        <div class="flex gap-3 mt-6">
                            <button type="submit" class="btn-form-primary">
                                <i class="fas fa-save mr-2"></i>Сохранить
                            </button>
                            <a href="{{ route('admin.dashboard') }}" class="btn-form-secondary">Отмена</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
