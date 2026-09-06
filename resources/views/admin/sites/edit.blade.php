@extends('admin.layouts.app')

@section('title', 'Edit site')

@section('content')
    <div class="card">
        <form method="POST" action="{{ route('admin.sites.update', $site) }}">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="form-group">
                    <label>Owner</label>
                    <select name="user_id" class="form-control" required>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" @selected(old('user_id', $site->user_id) == $user->id)>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Name</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $site->name) }}" required>
                </div>
                <div class="form-group">
                    <label>URL</label>
                    <input type="url" name="url" class="form-control" value="{{ old('url', $site->url) }}" required>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        @foreach ($statuses as $status)
                            <option value="{{ $status->value }}" @selected(old('status', $site->status->value) === $status->value)>{{ $status->value }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Plugin version</label>
                    <input type="text" name="plugin_version" class="form-control" value="{{ old('plugin_version', $site->plugin_version) }}">
                </div>
                <div class="form-group">
                    <label>WordPress version</label>
                    <input type="text" name="wordpress_version" class="form-control" value="{{ old('wordpress_version', $site->wordpress_version) }}">
                </div>
                <div class="form-group">
                    <label>WooCommerce version</label>
                    <input type="text" name="woocommerce_version" class="form-control" value="{{ old('woocommerce_version', $site->woocommerce_version) }}">
                </div>
            </div>
            <div class="card-footer">
                <button class="btn btn-primary">Update</button>
                <a href="{{ route('admin.sites.show', $site) }}" class="btn btn-default">Cancel</a>
            </div>
        </form>
    </div>
@endsection
