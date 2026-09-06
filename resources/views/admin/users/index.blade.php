@extends('admin.layouts.app')

@section('title', 'Users')
@section('actions')
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">Add user</a>
@endsection

@section('content')
    <div class="card">
        <div class="card-body p-0">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Admin</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td><a href="{{ route('admin.users.show', $user) }}">{{ $user->name }}</a></td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->status->value }}</td>
                            <td>{{ $user->is_admin ? 'Yes' : 'No' }}</td>
                            <td><a href="{{ route('admin.users.edit', $user) }}">Edit</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $users->links() }}</div>
    </div>
@endsection
