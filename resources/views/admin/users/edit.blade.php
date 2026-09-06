@extends('admin.layouts.app')

@section('title', 'Edit user')

@section('content')
    <div class="card">
        <form method="POST" action="{{ route('admin.users.update', $user) }}">
            @csrf
            @method('PUT')
            <div class="card-body">
                @include('admin.users.form', ['user' => $user])
            </div>
            <div class="card-footer">
                <button class="btn btn-primary">Update</button>
                <a href="{{ route('admin.users.show', $user) }}" class="btn btn-default">Cancel</a>
            </div>
        </form>
    </div>
@endsection
