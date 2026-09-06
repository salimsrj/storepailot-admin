<?php

namespace App\Http\Requests\Admin;

use App\Enums\PaymentProvider;
use App\Enums\SubscriptionStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSubscriptionRequest extends FormRequest
{
    use AuthorizesAdmin;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'exists:users,id'],
            'plan_id' => ['required', 'exists:plans,id'],
            'provider' => ['required', Rule::enum(PaymentProvider::class)],
            'status' => ['required', Rule::enum(SubscriptionStatus::class)],
            'provider_customer_id' => ['nullable', 'string', 'max:191'],
            'provider_subscription_id' => ['nullable', 'string', 'max:191'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'cancel_at_period_end' => ['sometimes', 'boolean'],
        ];
    }
}
