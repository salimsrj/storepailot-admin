<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Merchant\ChoosePlanRequest;
use App\Models\Plan;
use App\Services\Subscription\SubscriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlanController extends Controller
{
    public function __construct(private SubscriptionService $subscriptions) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $user->load('currentSubscription.plan');

        return view('merchant.plans.index', [
            'plans' => Plan::query()->active()->orderBy('monthly_price')->orderBy('id')->get(),
            'subscription' => $user->currentSubscription,
        ]);
    }

    public function store(ChoosePlanRequest $request): RedirectResponse
    {
        $user = $request->user();
        $plan = Plan::query()->active()->findOrFail($request->validated('plan_id'));
        $subscription = $user->currentSubscription;

        if ($subscription) {
            $this->subscriptions->changePlan($subscription, $plan);
        } else {
            $this->subscriptions->activate($user, $plan);
        }

        return redirect()
            ->route('dashboard.plans.index')
            ->with('status', 'Plan updated to '.$plan->name.'.');
    }
}
