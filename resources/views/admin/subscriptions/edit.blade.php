@extends('admin.layouts.app')

@section('title', 'Edit subscription')

@section('content')
    <div class="card">
        <form method="POST" action="{{ route('admin.subscriptions.update', $subscription) }}">
            @csrf
            @method('PUT')
            <div class="card-body">@include('admin.subscriptions.form', ['subscription' => $subscription])</div>
            <div class="card-footer">
                <button class="btn btn-primary">Update</button>
                <a href="{{ route('admin.subscriptions.show', $subscription) }}" class="btn btn-default">Cancel</a>
            </div>
        </form>
    </div>
@endsection
