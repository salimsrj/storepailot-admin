@extends('admin.layouts.app')

@section('title', 'Subscription')
@section('actions')
    <a href="{{ route('admin.subscriptions.edit', $subscription) }}" class="btn btn-primary btn-sm">Edit</a>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <p><strong>User:</strong> {{ $subscription->user->name }}</p>
            <p><strong>Plan:</strong> {{ $subscription->plan->name }}</p>
            <p><strong>Status:</strong> {{ $subscription->status->value }}</p>
            <p><strong>Provider:</strong> {{ $subscription->provider->value }}</p>
            <p><strong>Provider subscription ID:</strong> {{ $subscription->provider_subscription_id }}</p>
            <p><strong>Starts:</strong> {{ $subscription->starts_at }}</p>
            <p><strong>Ends:</strong> {{ $subscription->ends_at }}</p>
        </div>
        <div class="card-footer">
            <form method="POST" action="{{ route('admin.subscriptions.destroy', $subscription) }}" onsubmit="return confirm('Delete this subscription?')">
                @csrf
                @method('DELETE')
                <button class="btn btn-danger">Delete</button>
            </form>
        </div>
    </div>
@endsection
