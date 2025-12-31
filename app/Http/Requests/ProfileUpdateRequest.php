<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Rules\NoSpam;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
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
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', new NoSpam()],
            'nickname' => [
                'required', 
                'string', 
                'max:255', 
                'regex:/^[a-zA-Zа-яА-ЯёЁ0-9_.-]+$/u', // Только буквы, цифры, точка, дефис и подчеркивание
                Rule::unique(User::class)->ignore($this->user()->id),
                new NoSpam()
            ],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'bio' => ['nullable', 'string', 'max:1000', new NoSpam()],
            'avatar' => ['nullable', 'string', 'max:255'],
            'diary_privacy' => ['required', 'in:public,private,friends'],
            'comment_privacy' => ['required', 'in:all,friends,only_me,none'],
            'diary_name' => ['nullable', 'string', 'max:160', new NoSpam()],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'nickname.regex' => 'Никнейм может содержать только буквы, цифры, точку (.), дефис (-) и подчеркивание (_).',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Проверяем имя
            if ($this->has('name') && $this->containsForbiddenWord($this->name)) {
                $validator->errors()->add('name', 'Имя не может содержать служебные слова (админ, модератор и т.д.).');
            }
            
            // Проверяем никнейм
            if ($this->has('nickname')) {
                if ($this->containsForbiddenWord($this->nickname)) {
                    $validator->errors()->add('nickname', 'Никнейм не может содержать служебные слова (админ, модератор и т.д.).');
                }
                
                // Проверяем, что после нормализации nickname не пустой
                $normalized = \App\Models\User::normalizeNickname($this->nickname);
                if (empty($normalized)) {
                    $validator->errors()->add('nickname', 'Никнейм должен содержать хотя бы одну букву или цифру.');
                }
                
                // Проверяем уникальность нормализованного варианта для public_link
                $existingUser = User::where('public_link', $normalized)
                    ->where('id', '!=', $this->user()->id)
                    ->first();
                if ($existingUser) {
                    $validator->errors()->add('nickname', 'Этот никнейм уже занят (после нормализации совпадает с существующим).');
                }
            }
            
            // Проверяем название дневника
            if ($this->has('diary_name') && !empty($this->diary_name)) {
                $diaryName = $this->diary_name;
                
                // Проверка на все заглавные буквы
                $withoutSpaces = preg_replace('/\s+/', '', $diaryName);
                $lettersOnly = preg_replace('/[^a-zA-Zа-яА-ЯёЁ]/u', '', $withoutSpaces);
                if (!empty($lettersOnly) && mb_strtoupper($lettersOnly, 'UTF-8') === $lettersOnly && mb_strlen($lettersOnly) > 2) {
                    $validator->errors()->add('diary_name', 'Название дневника не может состоять только из заглавных букв.');
                }
                
                // Подсчет эмодзи (Unicode эмодзи)
                $emojiPattern = '/[\x{1F300}-\x{1F9FF}]|[\x{2600}-\x{26FF}]|[\x{2700}-\x{27BF}]/u';
                preg_match_all($emojiPattern, $diaryName, $emojis);
                $emojiCount = count($emojis[0] ?? []);
                if ($emojiCount > 2) {
                    $validator->errors()->add('diary_name', 'Название дневника не может содержать более 2 эмодзи.');
                }
                
                // Проверка наличия букв или слов
                $hasLetters = preg_match('/[a-zA-Zа-яА-ЯёЁ]/u', $diaryName);
                if (!$hasLetters) {
                    $validator->errors()->add('diary_name', 'Название дневника должно содержать хотя бы одну букву.');
                }
            }
        });
    }
}
