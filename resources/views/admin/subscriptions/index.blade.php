@extends('admin.layouts.app')

@section('title', 'Subscriptions')
@section('actions')
    <a href="{{ route('admin.subscriptions.create') }}" class="btn btn-primary btn-sm">Add subscription</a>
@endsection

@section('content')
    <div class="card">
        <div class="card-body p-0">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Plan</th>
                        <th>Status</th>
                        <th>Provider</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($subscriptions as $subscription)
                        <tr>
                            <td>{{ $subscription->user->name }}</td>
                            <td>{{ $subscription->plan->name }}</td>
                            <td>{{ $subscription->status->value }}</td>
                            <td>{{ $subscription->provider->value }}</td>
                            <td>
                                <a href="{{ route('admin.subscriptions.show', $subscription) }}">View</a>
                                ·
                                <a href="{{ route('admin.subscriptions.edit', $subscription) }}">Edit</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $subscriptions->links() }}</div>
    </div>
@endsection
