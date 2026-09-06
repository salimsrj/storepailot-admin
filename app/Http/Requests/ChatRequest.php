<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChatRequest extends FormRequest
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
            'conversation_id' => ['nullable', 'uuid'],
            'visitor_id' => ['required', 'uuid'],
            'message' => ['required', 'string', 'min:1', 'max:4000'],
        ];
    }
}
