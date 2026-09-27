<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

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
            'content' => ['nullable', 'string', 'max:4000'],
            'agent' => ['nullable', 'string', 'max:100'],
            'products' => ['nullable', 'array', 'max:5'],
            'products.*.id' => ['required', 'integer', 'min:1'],
            'products.*.name' => ['nullable', 'string', 'max:255'],
            'products.*.price' => ['nullable', 'string', 'max:50'],
            'products.*.currency' => ['nullable', 'string', 'max:10'],
            'products.*.stock_status' => ['nullable', 'string', 'max:50'],
            'products.*.image' => ['nullable', 'string', 'max:2048'],
            'products.*.url' => ['nullable', 'string', 'max:2048'],
            'products.*.short_description' => ['nullable', 'string', 'max:2000'],
            'products.*.categories' => ['nullable', 'array'],
            'products.*.categories.*' => ['string', 'max:100'],
            'products.*.has_variations' => ['nullable', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $content = trim((string) $this->input('content', ''));
            $products = $this->input('products');
            $hasProducts = is_array($products) && $products !== [];

            if ($content === '' && ! $hasProducts) {
                $validator->errors()->add('content', 'A reply or at least one product is required.');
            }
        });
    }
}
