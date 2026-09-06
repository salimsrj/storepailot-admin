@extends('admin.layouts.app')

@section('title', 'Edit plan')

@section('content')
    <div class="card">
        <form method="POST" action="{{ route('admin.plans.update', $plan) }}">
            @csrf
            @method('PUT')
            <div class="card-body">@include('admin.plans.form', ['plan' => $plan])</div>
            <div class="card-footer">
                <button class="btn btn-primary">Update</button>
                <a href="{{ route('admin.plans.show', $plan) }}" class="btn btn-default">Cancel</a>
            </div>
        </form>
    </div>
@endsection
