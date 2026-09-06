<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterSiteRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'url' => ['required', 'url', 'max:255'],
            'plugin_version' => ['nullable', 'string', 'max:50'],
            'wordpress_version' => ['nullable', 'string', 'max:50'],
            'woocommerce_version' => ['nullable', 'string', 'max:50'],
        ];
    }
}
