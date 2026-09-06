@extends('admin.layouts.app')

@section('title', 'Plans')
@section('actions')
    <a href="{{ route('admin.plans.create') }}" class="btn btn-primary btn-sm">Add plan</a>
@endsection

@section('content')
    <div class="card">
        <div class="card-body p-0">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Messages</th>
                        <th>Price</th>
                        <th>Active</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($plans as $plan)
                        <tr>
                            <td><a href="{{ route('admin.plans.show', $plan) }}">{{ $plan->name }}</a></td>
                            <td>{{ $plan->slug }}</td>
                            <td>{{ $plan->monthly_messages }}</td>
                            <td>{{ $plan->monthly_price }} {{ $plan->currency }}</td>
                            <td>{{ $plan->is_active ? 'Yes' : 'No' }}</td>
                            <td><a href="{{ route('admin.plans.edit', $plan) }}">Edit</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $plans->links() }}</div>
    </div>
@endsection
