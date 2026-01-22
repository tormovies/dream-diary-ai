<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
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
                <div class="mb-4 bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-300 px-4 py-3 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            <div class="profile-form-section card-shadow">
                <form method="POST" action="{{ route('admin.settings.update') }}" class="profile-form">
                    @csrf
                    @method('PATCH')

                    <!-- Разрешить удаление отчетов -->
                    <div class="form-group">
                        <label class="form-label">
                            <input type="checkbox" 
                                   name="allow_report_deletion" 
                                   value="1"
                                   {{ old('allow_report_deletion', $settings['allow_report_deletion']) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 mr-2">
                            <i class="fas fa-trash-alt"></i> Разрешить пользователям удалять отчеты
                        </label>
                        <div class="form-hint">Если отключено, только администраторы смогут удалять отчеты</div>
                    </div>

                    <!-- Ограничение редактирования снов -->
                    <div class="form-group">
                        <label for="edit_dreams_after_days" class="form-label">
                            <i class="fas fa-edit"></i> Ограничение редактирования снов (дней)
                        </label>
                        <input type="number" 
                               id="edit_dreams_after_days" 
                               name="edit_dreams_after_days" 
                               min="0"
                               class="form-input" 
                               value="{{ old('edit_dreams_after_days', $settings['edit_dreams_after_days']) }}" />
                        <div class="form-hint">Укажите количество дней после создания отчета, в течение которых можно редактировать сны. Оставьте пустым, чтобы разрешить редактирование всегда.</div>
                        <x-input-error class="mt-2" :messages="$errors->get('edit_dreams_after_days')" />
                    </div>

                    <!-- Минимальная длина для спойлера -->
                    <div class="form-group">
                        <label for="diary_spoiler_min_length" class="form-label">
                            <i class="fas fa-eye-slash"></i> Минимальная длина текста для спойлера в дневнике (символов)
                        </label>
                        <input type="number" 
                               id="diary_spoiler_min_length" 
                               name="diary_spoiler_min_length" 
                               min="0"
                               class="form-input" 
                               value="{{ old('diary_spoiler_min_length', $settings['diary_spoiler_min_length']) }}" />
                        <div class="form-hint">Если общая длина описаний всех снов в отчете превышает это значение, отчет будет скрыт под спойлером. По умолчанию: 1000 символов.</div>
                        <x-input-error class="mt-2" :messages="$errors->get('diary_spoiler_min_length')" />
                    </div>

                    <!-- Настройки анализатора снов -->
                    <div class="form-group border-t border-gray-200 dark:border-gray-700 pt-6 mt-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            <i class="fas fa-brain mr-2"></i>Настройки анализатора снов
                        </h3>
                        
                        <div class="form-group">
                            <label for="deepseek_api_key" class="form-label">
                                <i class="fas fa-key"></i> DeepSeek API ключ
                            </label>
                            <input type="password" 
                                   id="deepseek_api_key" 
                                   name="deepseek_api_key" 
                                   class="form-input" 
                                   value="{{ old('deepseek_api_key', $settings['deepseek_api_key']) }}" 
                                   placeholder="sk-..." />
                            <div class="form-hint">API ключ для работы анализатора сновидений через DeepSeek API.</div>
                            <x-input-error class="mt-2" :messages="$errors->get('deepseek_api_key')" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-8">
                            <div class="form-group md:pr-4">
                                <label for="deepseek_http_timeout" class="form-label">
                                    <i class="fas fa-clock"></i> HTTP таймаут запроса (секунды)
                                </label>
                                <input type="number" 
                                       id="deepseek_http_timeout" 
                                       name="deepseek_http_timeout" 
                                       min="60"
                                       max="1800"
                                       class="form-input" 
                                       value="{{ old('deepseek_http_timeout', $settings['deepseek_http_timeout']) }}" />
                                <div class="form-hint">Максимальное время ожидания ответа от DeepSeek API. По умолчанию: 600 сек (10 мин).</div>
                                <x-input-error class="mt-2" :messages="$errors->get('deepseek_http_timeout')" />
                            </div>

                            <div class="form-group md:pl-4">
                                <label for="deepseek_php_execution_timeout" class="form-label">
                                    <i class="fas fa-hourglass-half"></i> PHP таймаут выполнения (секунды)
                                </label>
                                <input type="number" 
                                       id="deepseek_php_execution_timeout" 
                                       name="deepseek_php_execution_timeout" 
                                       min="60"
                                       max="1800"
                                       class="form-input" 
                                       value="{{ old('deepseek_php_execution_timeout', $settings['deepseek_php_execution_timeout']) }}" />
                                <div class="form-hint">Максимальное время выполнения PHP скрипта. Должно быть больше HTTP таймаута. По умолчанию: 660 сек (11 мин).</div>
                                <x-input-error class="mt-2" :messages="$errors->get('deepseek_php_execution_timeout')" />
                            </div>
                        </div>
                    </div>

                    <!-- Часовой пояс для статистики -->
                    <div class="form-group border-t border-gray-200 dark:border-gray-700 pt-6 mt-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            <i class="fas fa-globe mr-2"></i>Часовой пояс для статистики
                        </h3>
                        
                        <div class="form-group">
                            <label for="timezone" class="form-label">
                                <i class="fas fa-clock"></i> Часовой пояс
                            </label>
                            <select id="timezone" 
                                    name="timezone" 
                                    class="form-select">
                                <option value="UTC" {{ old('timezone', $settings['timezone']) === 'UTC' ? 'selected' : '' }}>UTC (время сервера)</option>
                                <option value="Europe/Moscow" {{ old('timezone', $settings['timezone']) === 'Europe/Moscow' ? 'selected' : '' }}>Москва (MSK, UTC+3)</option>
                                <option value="Europe/Kiev" {{ old('timezone', $settings['timezone']) === 'Europe/Kiev' ? 'selected' : '' }}>Киев (EET, UTC+2)</option>
                                <option value="Europe/Minsk" {{ old('timezone', $settings['timezone']) === 'Europe/Minsk' ? 'selected' : '' }}>Минск (MSK, UTC+3)</option>
                                <option value="Asia/Almaty" {{ old('timezone', $settings['timezone']) === 'Asia/Almaty' ? 'selected' : '' }}>Алматы (ALMT, UTC+6)</option>
                                <option value="Asia/Tashkent" {{ old('timezone', $settings['timezone']) === 'Asia/Tashkent' ? 'selected' : '' }}>Ташкент (UZT, UTC+5)</option>
                                <option value="Asia/Yekaterinburg" {{ old('timezone', $settings['timezone']) === 'Asia/Yekaterinburg' ? 'selected' : '' }}>Екатеринбург (YEKT, UTC+5)</option>
                                <option value="Asia/Novosibirsk" {{ old('timezone', $settings['timezone']) === 'Asia/Novosibirsk' ? 'selected' : '' }}>Новосибирск (NOVT, UTC+7)</option>
                                <option value="Asia/Krasnoyarsk" {{ old('timezone', $settings['timezone']) === 'Asia/Krasnoyarsk' ? 'selected' : '' }}>Красноярск (KRAT, UTC+7)</option>
                                <option value="Asia/Irkutsk" {{ old('timezone', $settings['timezone']) === 'Asia/Irkutsk' ? 'selected' : '' }}>Иркутск (IRKT, UTC+8)</option>
                                <option value="Asia/Vladivostok" {{ old('timezone', $settings['timezone']) === 'Asia/Vladivostok' ? 'selected' : '' }}>Владивосток (VLAT, UTC+10)</option>
                            </select>
                            <div class="form-hint">Выберите часовой пояс для отображения статистики толкований сновидений. Влияет на фильтрацию по датам в админ-панели.</div>
                            <x-input-error class="mt-2" :messages="$errors->get('timezone')" />
                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="{{ route('admin.dashboard') }}" class="btn-form-secondary">
                            <i class="fas fa-arrow-left mr-2"></i>Отмена
                        </a>
                        <button type="submit" class="btn-form-primary">
                            <i class="fas fa-save mr-2"></i>Сохранить настройки
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
