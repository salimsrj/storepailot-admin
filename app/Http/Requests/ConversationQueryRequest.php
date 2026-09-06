<?php

namespace App\Http\Requests;

use App\Enums\ConversationMode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ConversationQueryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->attributes->get('site') !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:200'],
            'after_id' => ['nullable', 'integer', 'min:0'],
            'visitor_id' => ['nullable', 'uuid'],
            'mode' => ['nullable', Rule::enum(ConversationMode::class)],
        ];
    }
}
