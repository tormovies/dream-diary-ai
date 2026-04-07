@extends('layouts.base')

@section('content')
<div class="w-full max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-10 min-w-0">
    @if(session('success'))
        <div class="mb-6 p-4 rounded-lg bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200 border border-green-200 dark:border-green-800">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-6 p-4 rounded-lg bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-200 border border-red-200 dark:border-red-800">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 sm:p-8 card-shadow border border-gray-200 dark:border-gray-700 min-w-0 overflow-hidden">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white mb-2">
            {{ $seo['h1'] ?? 'Обратная связь' }}
        </h1>
        @if(!empty($seo['h1_description']))
            <p class="text-gray-600 dark:text-gray-400 mb-6">{{ $seo['h1_description'] }}</p>
        @endif

        @php
            $hasContacts = $contacts['telegram'] !== '' || $contacts['vk'] !== '' || $contacts['email'] !== '';
        @endphp
        @if($hasContacts)
            <div class="mb-8 p-4 rounded-xl bg-gray-50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700">
                <h2 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-3">Контакты</h2>
                <ul class="flex flex-col gap-2 text-gray-800 dark:text-gray-200 list-none p-0 m-0">
                    @if($contacts['telegram'] !== '')
                        @php
                            $tg = $contacts['telegram'];
                            if (str_starts_with($tg, 'http')) {
                                $tgUrl = $tg;
                            } elseif (str_starts_with($tg, '@')) {
                                $tgUrl = 'https://t.me/' . ltrim($tg, '@');
                            } else {
                                $tgUrl = 'https://t.me/' . preg_replace('/^@+/', '', $tg);
                            }
                        @endphp
                        <li class="flex flex-wrap items-center gap-x-2 gap-y-1 min-w-0"><span class="inline-flex shrink-0 w-6 justify-center"><i class="fab fa-telegram text-blue-500"></i></span> <a href="{{ $tgUrl }}" class="text-purple-600 dark:text-purple-400 hover:underline break-all" target="_blank" rel="noopener noreferrer">Telegram</a></li>
                    @endif
                    @if($contacts['vk'] !== '')
                        @php
                            $vkLabel = str_contains((string) ($contacts['vk_href'] ?? ''), '/write') ? 'Написать в ВК' : 'ВКонтакте';
                        @endphp
                        <li class="flex flex-wrap items-center gap-x-2 gap-y-1 min-w-0"><span class="inline-flex shrink-0 w-6 justify-center"><i class="fab fa-vk text-blue-600"></i></span> <a href="{{ $contacts['vk_href'] }}" class="text-purple-600 dark:text-purple-400 hover:underline break-all" target="_blank" rel="noopener noreferrer">{{ $vkLabel }}</a></li>
                    @endif
                    @if($contacts['email'] !== '')
                        <li class="flex flex-wrap items-center gap-x-2 gap-y-1 min-w-0"><span class="inline-flex shrink-0 w-6 justify-center"><i class="fas fa-envelope text-gray-500"></i></span> <a href="mailto:{{ e($contacts['email']) }}" class="text-purple-600 dark:text-purple-400 hover:underline break-all">{{ $contacts['email'] }}</a></li>
                    @endif
                </ul>
            </div>
        @endif

        @if($pageHtml !== '')
            <div class="feedback-page-html prose prose-gray dark:prose-invert max-w-none mb-8 min-w-0 overflow-x-auto [&_a]:break-all [&_img]:max-w-full [&_img]:h-auto">
                {!! $pageHtml !!}
            </div>
        @endif

        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 mt-2">Написать сообщение</h2>
        {{-- Honeypot: вне потока формы (fixed), не перекрывает поля --}}
        <div class="fixed w-px h-px p-0 -m-px overflow-hidden whitespace-nowrap border-0 opacity-0 pointer-events-none"
             style="clip: rect(0, 0, 0, 0); clip-path: inset(50%);"
             aria-hidden="true">
            <label for="contact_hp">Не заполнять</label>
            <input type="text" name="contact_hp" id="contact_hp" value="" tabindex="-1" autocomplete="off" form="feedback-form">
        </div>
        <form id="feedback-form" method="POST" action="{{ route('feedback.store') }}" class="flex flex-col gap-6 min-w-0">
            @csrf

            <div class="flex flex-col gap-1.5 min-w-0">
                <label for="reply_contact" class="text-sm font-medium text-gray-700 dark:text-gray-300">Куда вам ответить <span class="text-red-500">*</span></label>
                <input type="text" name="reply_contact" id="reply_contact" required maxlength="500"
                       value="{{ old('reply_contact') }}"
                       placeholder="Email, Telegram @ник или другой контакт"
                       class="block w-full min-w-0 max-w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2.5 text-base shadow-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-500/30">
                <x-input-error class="mt-0.5" :messages="$errors->get('reply_contact')" />
            </div>

            <div class="flex flex-col gap-1.5 min-w-0">
                <label for="message" class="text-sm font-medium text-gray-700 dark:text-gray-300">Сообщение <span class="text-red-500">*</span></label>
                <textarea name="message" id="message" required rows="6" maxlength="10000"
                          class="block w-full min-w-0 max-w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2.5 text-base shadow-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-500/30 resize-y"
                          placeholder="Вопрос, предложение или замечание">{{ old('message') }}</textarea>
                <x-input-error class="mt-0.5" :messages="$errors->get('message')" />
            </div>

            <div class="flex flex-col gap-1.5 min-w-0">
                <label for="captcha_answer" class="text-sm font-medium text-gray-700 dark:text-gray-300 leading-snug">
                    Проверка: сколько будет {{ $captchaA }} + {{ $captchaB }}? <span class="text-red-500">*</span>
                </label>
                <input type="number" name="captcha_answer" id="captcha_answer" required inputmode="numeric"
                       value="{{ old('captcha_answer') }}"
                       class="block w-full sm:w-40 sm:max-w-[10rem] min-w-0 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white px-3 py-2.5 text-base shadow-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-500/30"
                       autocomplete="off">
                <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed max-w-prose">Простая защита от автоматических отправок (без внешних сервисов).</p>
                <x-input-error class="mt-0.5" :messages="$errors->get('captcha_answer')" />
            </div>

            <div class="pt-1">
                <button type="submit" class="inline-flex items-center justify-center w-full sm:w-auto min-h-[44px] px-6 py-3 rounded-lg bg-gradient-to-r from-purple-600 to-indigo-600 text-white font-semibold hover:shadow-lg transition-all">
                    <i class="fas fa-paper-plane mr-2"></i> Отправить
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
