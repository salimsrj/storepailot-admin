@extends('admin.layouts.app')

@section('title', $plan->name)
@section('actions')
    <a href="{{ route('admin.plans.edit', $plan) }}" class="btn btn-primary btn-sm">Edit</a>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <p><strong>Slug:</strong> {{ $plan->slug }}</p>
            <p><strong>Monthly messages:</strong> {{ $plan->monthly_messages }}</p>
            <p><strong>Max sites:</strong> {{ $plan->max_sites }}</p>
            <p><strong>Price:</strong> {{ $plan->monthly_price }} {{ $plan->currency }}</p>
            <p><strong>Active:</strong> {{ $plan->is_active ? 'Yes' : 'No' }}</p>
            <p><strong>Subscriptions:</strong> {{ $plan->subscriptions_count }}</p>
        </div>
        <div class="card-footer">
            <form method="POST" action="{{ route('admin.plans.destroy', $plan) }}" onsubmit="return confirm('Delete this plan?')">
                @csrf
                @method('DELETE')
                <button class="btn btn-danger">Delete</button>
            </form>
        </div>
    </div>
@endsection
