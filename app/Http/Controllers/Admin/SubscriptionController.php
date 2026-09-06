<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PaymentProvider;
use App\Enums\SubscriptionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSubscriptionRequest;
use App\Http\Requests\Admin\UpdateSubscriptionRequest;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function index(): View
    {
        return view('admin.subscriptions.index', [
            'subscriptions' => Subscription::query()
                ->with(['user', 'plan'])
                ->latest()
                ->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.subscriptions.create', $this->formData());
    }

    public function store(StoreSubscriptionRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['cancel_at_period_end'] = $request->boolean('cancel_at_period_end');

        $subscription = Subscription::query()->create($data);
        Cache::forget('plan-limit:'.$subscription->user_id);

        return redirect()->route('admin.subscriptions.index')->with('status', 'Subscription created.');
    }

    public function show(Subscription $subscription): View
    {
        $subscription->load(['user', 'plan']);

        return view('admin.subscriptions.show', ['subscription' => $subscription]);
    }

    public function edit(Subscription $subscription): View
    {
        return view('admin.subscriptions.edit', [
            'subscription' => $subscription,
            ...$this->formData(),
        ]);
    }

    public function update(UpdateSubscriptionRequest $request, Subscription $subscription): RedirectResponse
    {
        $data = $request->validated();
        $data['cancel_at_period_end'] = $request->boolean('cancel_at_period_end');

        $subscription->update($data);
        Cache::forget('plan-limit:'.$subscription->user_id);

        return redirect()->route('admin.subscriptions.show', $subscription)->with('status', 'Subscription updated.');
    }

    public function destroy(Subscription $subscription): RedirectResponse
    {
        Cache::forget('plan-limit:'.$subscription->user_id);
        $subscription->delete();

        return redirect()->route('admin.subscriptions.index')->with('status', 'Subscription deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(): array
    {
        return [
            'users' => User::query()->orderBy('name')->get(['id', 'name', 'email']),
            'plans' => Plan::query()->orderBy('name')->get(['id', 'name', 'slug']),
            'statuses' => SubscriptionStatus::cases(),
            'providers' => PaymentProvider::cases(),
        ];
    }
}
