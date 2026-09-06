<?php

namespace App\Http\Requests\Admin;

use App\Enums\ConversationStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateConversationRequest extends FormRequest
{
    use AuthorizesAdmin;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(ConversationStatus::class)],
            'summary' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
