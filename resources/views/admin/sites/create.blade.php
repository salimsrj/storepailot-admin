@extends('admin.layouts.app')

@section('title', 'Create site')

@section('content')
    <div class="card">
        <form method="POST" action="{{ route('admin.sites.store') }}">
            @csrf
            <div class="card-body">
                <div class="form-group">
                    <label>Owner</label>
                    <select name="user_id" class="form-control" required>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" @selected(old('user_id') == $user->id)>{{ $user->name }} ({{ $user->email }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Name</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                </div>
                <div class="form-group">
                    <label>URL</label>
                    <input type="url" name="url" class="form-control" value="{{ old('url') }}" required>
                </div>
                <div class="form-group">
                    <label>Plugin version</label>
                    <input type="text" name="plugin_version" class="form-control" value="{{ old('plugin_version') }}">
                </div>
            </div>
            <div class="card-footer">
                <button class="btn btn-primary">Save</button>
                <a href="{{ route('admin.sites.index') }}" class="btn btn-default">Cancel</a>
            </div>
        </form>
    </div>
@endsection
