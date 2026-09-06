<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSiteSettingsRequest extends FormRequest
{
    use AuthorizesAdmin;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'assistant_name' => ['required', 'string', 'max:80'],
            'welcome_message' => ['nullable', 'string', 'max:1000'],
            'language' => ['required', 'string', 'max:16'],
            'tone' => ['required', 'string', 'max:64'],
            'system_prompt' => ['nullable', 'string', 'max:5000'],
            'enable_product_search' => ['sometimes', 'boolean'],
            'enable_recommendations' => ['sometimes', 'boolean'],
            'enable_cart' => ['sometimes', 'boolean'],
            'enable_checkout' => ['sometimes', 'boolean'],
            'enable_order_tracking' => ['sometimes', 'boolean'],
        ];
    }
}
