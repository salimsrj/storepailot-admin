@extends('admin.layouts.app')

@section('title', 'Create subscription')

@section('content')
    <div class="card">
        <form method="POST" action="{{ route('admin.subscriptions.store') }}">
            @csrf
            <div class="card-body">@include('admin.subscriptions.form')</div>
            <div class="card-footer">
                <button class="btn btn-primary">Save</button>
                <a href="{{ route('admin.subscriptions.index') }}" class="btn btn-default">Cancel</a>
            </div>
        </form>
    </div>
@endsection
