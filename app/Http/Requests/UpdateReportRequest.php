<?php

namespace App\Http\Requests;

use App\Models\Report;
use Illuminate\Foundation\Http\FormRequest;

class UpdateReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'report_date' => ['required', 'date'],
            'access_level' => ['required', 'in:all,none,friends'],
            'status' => ['nullable', 'in:draft,published'],
            'dreams' => ['required', 'array', 'min:1'],
            'dreams.*.title' => ['nullable', 'string', 'max:255'],
            'dreams.*.description' => ['nullable', 'string'],
            'dreams.*.dream_type' => ['required', 'in:Яркий сон,Бледный сон,Пограничное состояние,Паралич,ВТО,Осознанное сновидение,Глюк,Транс / Гипноз,' . Report::BLOCK_TYPE_CONTEXT],
            'tags' => ['nullable'],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Если tags - это строка (JSON), преобразуем в массив
        if ($this->has('tags') && is_string($this->tags)) {
            $decoded = json_decode($this->tags, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $this->merge(['tags' => $decoded]);
            } else {
                // Если не JSON или пустая строка, делаем пустым массивом
                $this->merge(['tags' => []]);
            }
        }
        
        // Если tags не переданы или пустые, делаем пустым массивом
        if (!$this->has('tags') || empty($this->tags)) {
            $this->merge(['tags' => []]);
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        // Убрали проверку наличия названий - контроллер автоматически создаст название для первого сна, если их нет
    }
}
