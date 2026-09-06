@extends('admin.layouts.app')

@section('title', 'Sites')
@section('actions')
    <a href="{{ route('admin.sites.create') }}" class="btn btn-primary btn-sm">Add site</a>
@endsection

@section('content')
    <div class="card">
        <div class="card-body p-0">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>URL</th>
                        <th>Owner</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sites as $site)
                        <tr>
                            <td><a href="{{ route('admin.sites.show', $site) }}">{{ $site->name }}</a></td>
                            <td>{{ $site->url }}</td>
                            <td>{{ $site->user->name }}</td>
                            <td>{{ $site->status->value }}</td>
                            <td><a href="{{ route('admin.sites.edit', $site) }}">Edit</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $sites->links() }}</div>
    </div>
@endsection
