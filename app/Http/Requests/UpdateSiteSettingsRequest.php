<?php

namespace App\Http\Requests;

use App\Models\Site;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSiteSettingsRequest extends FormRequest
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
            'assistant_name' => ['sometimes', 'string', 'max:80'],
            'welcome_message' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'language' => ['sometimes', 'string', 'max:16'],
            'tone' => ['sometimes', 'string', 'max:64'],
            'system_prompt' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'enable_product_search' => ['sometimes', 'boolean'],
            'enable_recommendations' => ['sometimes', 'boolean'],
            'enable_cart' => ['sometimes', 'boolean'],
            'enable_checkout' => ['sometimes', 'boolean'],
            'enable_order_tracking' => ['sometimes', 'boolean'],
            'settings' => ['sometimes', 'array'],
        ];
    }
}
