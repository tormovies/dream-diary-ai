<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInterpretationDiaryTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'report_date' => ['required', 'date'],
            'access_level' => ['required', 'in:all,friends,none'],
            'allow_public_linking' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'allow_public_linking' => $this->boolean('allow_public_linking'),
        ]);
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'report_date' => 'дата записи в дневнике',
            'access_level' => 'видимость отчёта',
            'allow_public_linking' => 'публикация страницы толкования',
        ];
    }
}
