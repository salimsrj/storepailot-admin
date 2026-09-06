<?php

namespace App\Http\Requests;

use App\Models\Site;
use Illuminate\Foundation\Http\FormRequest;

class RotateSiteTokenRequest extends FormRequest
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
        return [];
    }
}
