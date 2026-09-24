<?php

namespace App\Http\Requests\Merchant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChoosePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'plan_id' => ['required', 'integer', Rule::exists('plans', 'id')->where('is_active', true)],
        ];
    }
}
