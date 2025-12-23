<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\SeoHelper;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        $seo = SeoHelper::get('register');
        return view('auth.register', compact('seo'));
    }

    /**
     * Список запрещенных слов для имени и никнейма
     */
    private function getForbiddenWords(): array
    {
        return [
            // Административные
            'admin', 'админ', 'администратор', 'administrator',
            'moderator', 'модератор', 'mod',
            'root', 'суперпользователь', 'superuser',
            'owner', 'владелец', 'собственник',
            
            // Служебные
            'system', 'система', 'системный',
            'support', 'поддержка', 'техподдержка',
            'service', 'сервис', 'служба',
            'staff', 'персонал', 'сотрудник',
            'manager', 'менеджер', 'управляющий',
            'official', 'официальный',
            'verified', 'верифицированный', 'проверенный',
            
            // Тестовые
            'test', 'тест', 'testing', 'тестирование',
            'user', 'пользователь',
            'guest', 'гость',
            'bot', 'бот',
        ];
    }

    /**
     * Проверяет, содержит ли строка запрещенные слова
     */
    private function containsForbiddenWord(string $value): bool
    {
        $valueLower = mb_strtolower($value, 'UTF-8');
        $forbiddenWords = $this->getForbiddenWords();
        
        foreach ($forbiddenWords as $word) {
            // Проверяем точное совпадение или вхождение слова
            $wordLower = mb_strtolower($word, 'UTF-8');
            if ($valueLower === $wordLower || 
                str_contains($valueLower, $wordLower) ||
                str_contains($valueLower, str_replace(' ', '', $wordLower)) ||
                str_contains($valueLower, str_replace(' ', '_', $wordLower))) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'nickname' => [
                'required', 
                'string', 
                'max:255', 
                'regex:/^[a-zA-Zа-яА-ЯёЁ0-9_.-]+$/u', // Только буквы, цифры, точка, дефис и подчеркивание
                'unique:users,nickname'
            ],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Кастомные сообщения об ошибках
        $validator->setCustomMessages([
            'nickname.regex' => 'Никнейм может содержать только буквы, цифры, точку (.), дефис (-) и подчеркивание (_).',
        ]);

        // Дополнительная проверка на запрещенные слова
        $validator->after(function ($validator) use ($request) {
            // Проверяем имя
            if ($this->containsForbiddenWord($request->name)) {
                $validator->errors()->add('name', 'Имя не может содержать служебные слова (админ, модератор и т.д.).');
            }
            
            // Проверяем никнейм
            if ($this->containsForbiddenWord($request->nickname)) {
                $validator->errors()->add('nickname', 'Никнейм не может содержать служебные слова (админ, модератор и т.д.).');
            }
            
            // Проверяем, что после нормализации nickname не пустой
            $normalized = User::normalizeNickname($request->nickname);
            if (empty($normalized)) {
                $validator->errors()->add('nickname', 'Никнейм должен содержать хотя бы одну букву или цифру.');
            }
            
            // Проверяем уникальность нормализованного варианта для public_link
            $existingUser = User::where('public_link', $normalized)->first();
            if ($existingUser) {
                $validator->errors()->add('nickname', 'Этот никнейм уже занят (после нормализации совпадает с существующим).');
            }
        });

        $validator->validate();

        $user = User::create([
            'name' => $request->name,
            'nickname' => $request->nickname,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user',
            'diary_privacy' => 'public',
            // public_link будет автоматически установлен через boot метод модели
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('notifications.index', absolute: false));
    }
}
