<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AgentReplyRequest extends FormRequest
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
            'content' => ['required', 'string', 'min:1', 'max:4000'],
            'agent' => ['nullable', 'string', 'max:100'],
        ];
    }
}
