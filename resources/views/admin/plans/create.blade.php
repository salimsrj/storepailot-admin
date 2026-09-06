@extends('admin.layouts.app')

@section('title', 'Create plan')

@section('content')
    <div class="card">
        <form method="POST" action="{{ route('admin.plans.store') }}">
            @csrf
            <div class="card-body">@include('admin.plans.form')</div>
            <div class="card-footer">
                <button class="btn btn-primary">Save</button>
                <a href="{{ route('admin.plans.index') }}" class="btn btn-default">Cancel</a>
            </div>
        </form>
    </div>
@endsection
