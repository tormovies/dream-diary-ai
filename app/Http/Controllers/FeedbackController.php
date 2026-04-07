<?php

namespace App\Http\Controllers;

use App\Helpers\SeoHelper;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class FeedbackController extends Controller
{
    public function index(): View
    {
        $a = random_int(1, 9);
        $b = random_int(1, 9);
        session([
            'feedback_captcha_a' => $a,
            'feedback_captcha_b' => $b,
        ]);

        $seo = SeoHelper::forFeedback();

        $vkRaw = trim((string) Setting::getValue('feedback_contact_vk', ''));

        $contacts = [
            'telegram' => trim((string) Setting::getValue('feedback_contact_telegram', '')),
            'vk' => $vkRaw,
            'vk_href' => self::resolveVkContactHref($vkRaw),
            'email' => trim((string) Setting::getValue('feedback_contact_email', '')),
        ];

        $pageHtml = (string) Setting::getValue('feedback_page_html', '');

        return view('feedback.index', [
            'seo' => $seo,
            'contacts' => $contacts,
            'pageHtml' => $pageHtml,
            'captchaA' => $a,
            'captchaB' => $b,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if (filled($request->input('contact_hp'))) {
            return redirect()->route('feedback.index')->with('success', 'Сообщение отправлено. Спасибо!');
        }

        $validated = $request->validate([
            'reply_contact' => ['required', 'string', 'max:500'],
            'message' => ['required', 'string', 'max:10000'],
            'captcha_answer' => ['required', 'integer'],
        ]);

        $a = (int) session('feedback_captcha_a', 0);
        $b = (int) session('feedback_captcha_b', 0);
        $expected = $a + $b;
        // После validate() значение часто остаётся строкой; строгое !== с int давало ложные ошибки
        $answer = (int) $validated['captcha_answer'];

        if ($a < 1 || $b < 1 || $expected < 2 || $answer !== $expected) {
            return back()
                ->withInput($request->only('reply_contact', 'message'))
                ->withErrors(['captcha_answer' => 'Неверный ответ на проверку. Обновите страницу и введите сумму заново.']);
        }

        $to = trim((string) Setting::getValue('feedback_mail_to', ''));
        if ($to === '') {
            $to = (string) config('mail.from.address', '');
        }
        if ($to === '') {
            Log::warning('Feedback form: no recipient email configured (feedback_mail_to / mail.from.address).');

            return back()
                ->withInput()
                ->with('error', 'Отправка писем сейчас не настроена. Напишите администратору другим способом.');
        }

        $lines = [
            'Новое сообщение с формы «Обратная связь»',
            '',
            'Контакт для ответа:',
            $validated['reply_contact'],
            '',
            'Сообщение:',
            $validated['message'],
            '',
            '---',
            'IP: '.$request->ip(),
            'User-Agent: '.substr((string) $request->userAgent(), 0, 500),
        ];
        $body = implode("\n", $lines);

        try {
            Mail::raw($body, function ($mail) use ($to) {
                $mail->to($to)->subject('Обратная связь — '.config('app.name'));
            });
        } catch (\Throwable $e) {
            Log::error('Feedback mail failed', ['error' => $e->getMessage()]);

            return back()
                ->withInput()
                ->with('error', 'Не удалось отправить письмо. Попробуйте позже или свяжитесь другим способом.');
        }

        session()->forget(['feedback_captcha_a', 'feedback_captcha_b']);

        return redirect()->route('feedback.index')->with('success', 'Сообщение отправлено. Мы ответим, когда сможем.');
    }

    /**
     * Ссылка для блока контактов: только числовой id → диалог vk.com/write{id}.
     * Полный URL, ник или путь — как раньше (профиль или ваша ссылка).
     */
    private static function resolveVkContactHref(string $raw): string
    {
        if ($raw === '') {
            return '';
        }

        if (preg_match('#^https?://#i', $raw)) {
            return $raw;
        }

        if (preg_match('/^\d+$/', $raw)) {
            return 'https://vk.com/write'.$raw;
        }

        if (preg_match('/^id(\d+)$/i', $raw, $m)) {
            return 'https://vk.com/write'.$m[1];
        }

        if (preg_match('/^write(\d+)$/i', $raw, $m)) {
            return 'https://vk.com/write'.$m[1];
        }

        $trim = ltrim($raw, '/');
        if (str_starts_with($trim, 'vk.com/')) {
            return 'https://'.$trim;
        }

        return 'https://vk.com/'.$trim;
    }
}
