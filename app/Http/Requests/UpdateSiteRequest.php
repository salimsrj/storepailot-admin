<?php

namespace App\Http\Requests;

use App\Models\Site;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSiteRequest extends FormRequest
{
    public function authorize(): bool
    {
        $site = $this->attributes->get('site');

        return $site instanceof Site;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'plugin_version' => ['nullable', 'string', 'max:50'],
            'wordpress_version' => ['nullable', 'string', 'max:50'],
            'woocommerce_version' => ['nullable', 'string', 'max:50'],
        ];
    }
}
