<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreSiteRequest extends FormRequest
{
    use AuthorizesAdmin;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'exists:users,id'],
            'name' => ['required', 'string', 'max:255'],
            'url' => ['required', 'url', 'max:255'],
            'plugin_version' => ['nullable', 'string', 'max:50'],
            'wordpress_version' => ['nullable', 'string', 'max:50'],
            'woocommerce_version' => ['nullable', 'string', 'max:50'],
        ];
    }
}
