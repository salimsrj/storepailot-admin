@extends('merchant.layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Account</h3></div>
                <div class="card-body">
                    <p class="mb-1"><strong>{{ $user->name }}</strong></p>
                    <p class="mb-1">{{ $user->email }}</p>
                    @if ($user->hasVerifiedEmail())
                        <span class="badge badge-success">Email verified</span>
                    @else
                        <span class="badge badge-warning">Email not verified</span>
                        <p class="mt-2 mb-0">
                            <a href="{{ route('verification.notice') }}">Verify your email</a> to view connection credentials.
                        </p>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Plan</h3></div>
                <div class="card-body">
                    @if ($subscription?->plan)
                        <p class="mb-1"><strong>{{ $subscription->plan->name }}</strong></p>
                        <p class="mb-0 text-muted">{{ number_format($subscription->plan->monthly_messages) }} messages / month</p>
                    @else
                        <p class="mb-2">No plan selected yet.</p>
                        <a href="{{ route('dashboard.plans.index') }}" class="btn btn-sm btn-primary">Choose a plan</a>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Site</h3></div>
                <div class="card-body">
                    @if ($site)
                        <p class="mb-1"><strong>{{ $site->name }}</strong></p>
                        <p class="mb-2 text-muted">{{ $site->url }}</p>
                        <a href="{{ route('dashboard.credentials') }}" class="btn btn-sm btn-secondary">View credentials</a>
                    @else
                        <p class="mb-0 text-muted">No site registered.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
