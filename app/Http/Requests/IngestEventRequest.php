<?php

namespace App\Http\Requests;

use App\Models\Site;
use Illuminate\Foundation\Http\FormRequest;

class IngestEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->attributes->get('site') instanceof Site;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', 'string', 'max:64'],
            'conversation_id' => ['nullable', 'uuid'],
            'units' => ['nullable', 'integer', 'min:1'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
