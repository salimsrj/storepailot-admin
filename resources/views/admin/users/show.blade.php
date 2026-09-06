@extends('admin.layouts.app')

@section('title', $user->name)
@section('actions')
    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary btn-sm">Edit</a>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <p><strong>Email:</strong> {{ $user->email }}</p>
            <p><strong>Status:</strong> {{ $user->status->value }}</p>
            <p><strong>Admin:</strong> {{ $user->is_admin ? 'Yes' : 'No' }}</p>
            <p><strong>UUID:</strong> {{ $user->uuid }}</p>
        </div>
        <div class="card-footer">
            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Delete this user?')">
                @csrf
                @method('DELETE')
                <button class="btn btn-danger">Delete</button>
            </form>
        </div>
    </div>
@endsection
