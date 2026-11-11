<?php

namespace Tests\Feature;

use Tests\TestCase;

class RuTranslationsTest extends TestCase
{
    public function test_password_broker_statuses_are_translated_in_russian(): void
    {
        config(['app.locale' => 'ru', 'app.fallback_locale' => 'ru']);
        $expected = [
            'passwords.sent' => 'На ваш адрес электронной почты отправлена ссылка для сброса пароля.',
            'passwords.reset' => 'Ваш пароль успешно сброшен!',
            'passwords.user' => 'Пользователь с таким адресом электронной почты не найден.',
            'passwords.token' => 'Ссылка для сброса пароля недействительна или устарела.',
            'passwords.throttled' => 'Слишком много запросов на сброс пароля. Повторите попытку через :seconds секунд.',
        ];
        foreach ($expected as $key => $line) {
            $this->assertSame($line, __($key));
            $this->assertNotSame($key, __($key));
        }
    }

    public function test_auth_throttle_messages_are_translated_in_russian(): void
    {
        config(['app.locale' => 'ru', 'app.fallback_locale' => 'ru']);
        $this->assertSame('Некорректный адрес электронной почты или пароль.', trans('auth.failed'));
        $this->assertSame(
            'Слишком много попыток входа. Повторите попытку через 10 секунд.',
            trans('auth.throttle', ['seconds' => 10]),
        );
    }

    public function test_validation_messages_are_translated_in_russian(): void
    {
        config(['app.locale' => 'ru', 'app.fallback_locale' => 'ru']);
        $message = __('validation.required', ['attribute' => 'email']);
        $this->assertSame('Поле email обязательно для заполнения.', $message);
    }

    public function test_russian_fallback_shows_translated_reset_link_status_not_raw_key(): void
    {
        config(['app.locale' => 'ru', 'app.fallback_locale' => 'en']);
        $status = __('passwords.sent');
        $this->assertSame('На ваш адрес электронной почты отправлена ссылка для сброса пароля.', $status);
        $this->assertStringNotContainsString('passwords.', $status);
    }
}
