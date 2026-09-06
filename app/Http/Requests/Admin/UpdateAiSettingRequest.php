<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAiSettingRequest extends FormRequest
{
    use AuthorizesAdmin;

    protected function prepareForValidation(): void
    {
        $phrases = $this->input('greeting_phrases');

        if (is_string($phrases)) {
            $this->merge([
                'greeting_phrases' => collect(preg_split('/\r\n|\r|\n/', $phrases) ?: [])
                    ->map(fn (mixed $line): string => trim((string) $line))
                    ->filter()
                    ->values()
                    ->all(),
            ]);
        }

        if ($this->input('greeting_reply') === '') {
            $this->merge(['greeting_reply' => null]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'openai_api_key' => ['nullable', 'string', 'min:10', 'max:255'],
            'openai_organization' => ['nullable', 'string', 'max:64'],
            'model' => ['required', 'string', 'max:80'],
            'timeout' => ['required', 'integer', 'min:5', 'max:120'],
            'max_tool_iterations' => ['required', 'integer', 'min:1', 'max:10'],
            'max_context_messages' => ['required', 'integer', 'min:4', 'max:50'],
            'greeting_phrases' => ['nullable', 'array'],
            'greeting_phrases.*' => ['string', 'max:80'],
            'greeting_reply' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
