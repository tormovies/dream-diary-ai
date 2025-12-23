<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Настройки системы') }}
            </h2>
            <a href="{{ route('admin.dashboard') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Назад
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('admin.settings.update') }}">
                        @csrf
                        @method('PATCH')

                        <div class="mb-6">
                            <label class="flex items-center">
                                <input type="checkbox" 
                                       name="allow_report_deletion" 
                                       value="1"
                                       {{ old('allow_report_deletion', $settings['allow_report_deletion']) ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                <span class="ml-2 text-sm text-gray-600">Разрешить пользователям удалять отчеты</span>
                            </label>
                            <p class="mt-1 text-xs text-gray-500">Если отключено, только администраторы смогут удалять отчеты</p>
                        </div>

                        <div class="mb-6">
                            <x-input-label for="edit_dreams_after_days" :value="__('Ограничение редактирования снов (дней)')" />
                            <x-text-input id="edit_dreams_after_days" 
                                         name="edit_dreams_after_days" 
                                         type="number" 
                                         min="0"
                                         class="mt-1 block w-full" 
                                         :value="old('edit_dreams_after_days', $settings['edit_dreams_after_days'])" />
                            <p class="mt-1 text-xs text-gray-500">Укажите количество дней после создания отчета, в течение которых можно редактировать сны. Оставьте пустым, чтобы разрешить редактирование всегда.</p>
                            <x-input-error class="mt-2" :messages="$errors->get('edit_dreams_after_days')" />
                        </div>

                        <div class="mb-6">
                            <x-input-label for="diary_spoiler_min_length" :value="__('Минимальная длина текста для спойлера в дневнике (символов)')" />
                            <x-text-input id="diary_spoiler_min_length" 
                                         name="diary_spoiler_min_length" 
                                         type="number" 
                                         min="0"
                                         class="mt-1 block w-full" 
                                         :value="old('diary_spoiler_min_length', $settings['diary_spoiler_min_length'])" />
                            <p class="mt-1 text-xs text-gray-500">Если общая длина описаний всех снов в отчете превышает это значение, отчет будет скрыт под спойлером. По умолчанию: 1000 символов.</p>
                            <x-input-error class="mt-2" :messages="$errors->get('diary_spoiler_min_length')" />
                        </div>

                        <div class="mb-6 border-t pt-6">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Настройки анализатора снов</h3>
                            <x-input-label for="deepseek_api_key" :value="__('DeepSeek API ключ')" />
                            <x-text-input id="deepseek_api_key" 
                                         name="deepseek_api_key" 
                                         type="password"
                                         class="mt-1 block w-full" 
                                         :value="old('deepseek_api_key', $settings['deepseek_api_key'])" 
                                         placeholder="sk-..." />
                            <p class="mt-1 text-xs text-gray-500">API ключ для работы анализатора сновидений через DeepSeek API.</p>
                            <x-input-error class="mt-2" :messages="$errors->get('deepseek_api_key')" />
                        </div>

                        <div class="flex items-center justify-end">
                            <x-primary-button>
                                {{ __('Сохранить настройки') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>









