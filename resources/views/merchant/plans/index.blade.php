@extends('merchant.layouts.app')

@section('title', 'Plans')

@section('content')
    @if ($subscription?->plan)
        <div class="alert alert-info">
            Current plan: <strong>{{ $subscription->plan->name }}</strong>
            ({{ number_format($subscription->plan->monthly_messages) }} messages / month)
        </div>
    @endif

    <div class="row">
        @foreach ($plans as $plan)
            <div class="col-md-3">
                <div class="card {{ $subscription?->plan_id === $plan->id ? 'border-primary' : '' }}">
                    <div class="card-header">
                        <h3 class="card-title">{{ $plan->name }}</h3>
                    </div>
                    <div class="card-body">
                        <p class="h4">
                            {{ $plan->currency }} {{ number_format((float) $plan->monthly_price, 2) }}
                            <small class="text-muted">/ mo</small>
                        </p>
                        <ul class="mb-3 pl-3">
                            <li>{{ number_format($plan->monthly_messages) }} messages</li>
                            <li>{{ $plan->max_sites }} site{{ $plan->max_sites === 1 ? '' : 's' }}</li>
                        </ul>
                        @if ($subscription?->plan_id === $plan->id)
                            <button class="btn btn-outline-secondary btn-block" type="button" disabled>Current plan</button>
                        @else
                            <form method="POST" action="{{ route('dashboard.plans.store') }}">
                                @csrf
                                <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                                <button type="submit" class="btn btn-primary btn-block">
                                    {{ $subscription ? 'Switch to '.$plan->name : 'Choose '.$plan->name }}
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection
