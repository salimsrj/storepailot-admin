@extends('admin.layouts.app')

@section('title', $site->name)
@section('actions')
    <a href="{{ route('admin.sites.edit', $site) }}" class="btn btn-primary btn-sm">Edit</a>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <p><strong>Owner:</strong> {{ $site->user->name }}</p>
            <p><strong>URL:</strong> {{ $site->url }}</p>
            <p><strong>Domain:</strong> {{ $site->domain }}</p>
            <p><strong>Status:</strong> {{ $site->status->value }}</p>
            <p><strong>UUID:</strong> {{ $site->uuid }}</p>
            <p><strong>Last seen:</strong> {{ $site->last_seen_at }}</p>
        </div>
        <div class="card-footer d-flex">
            <form method="POST" action="{{ route('admin.sites.token', $site) }}" class="mr-2">
                @csrf
                <button class="btn btn-warning">Rotate token</button>
            </form>
            <form method="POST" action="{{ route('admin.sites.destroy', $site) }}" onsubmit="return confirm('Delete this site?')">
                @csrf
                @method('DELETE')
                <button class="btn btn-danger">Delete</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3 class="card-title">Assistant settings</h3></div>
        <form method="POST" action="{{ route('admin.sites.settings', $site) }}">
            @csrf
            @method('PATCH')
            <div class="card-body">
                <div class="form-group">
                    <label>Assistant name</label>
                    <input type="text" name="assistant_name" class="form-control" value="{{ old('assistant_name', $site->settings->assistant_name ?? 'CommercePilot') }}">
                </div>
                <div class="form-group">
                    <label>Welcome message</label>
                    <textarea name="welcome_message" class="form-control" rows="2">{{ old('welcome_message', $site->settings->welcome_message ?? '') }}</textarea>
                </div>
                <div class="form-group">
                    <label>Language</label>
                    <input type="text" name="language" class="form-control" value="{{ old('language', $site->settings->language ?? 'en') }}">
                </div>
                <div class="form-group">
                    <label>Tone</label>
                    <input type="text" name="tone" class="form-control" value="{{ old('tone', $site->settings->tone ?? 'helpful') }}">
                </div>
                <div class="form-group">
                    <label>System prompt</label>
                    <textarea name="system_prompt" class="form-control" rows="4">{{ old('system_prompt', $site->settings->system_prompt ?? '') }}</textarea>
                </div>
                @foreach (['enable_product_search' => 'Product search', 'enable_recommendations' => 'Recommendations', 'enable_cart' => 'Cart', 'enable_checkout' => 'Checkout', 'enable_order_tracking' => 'Order tracking'] as $field => $label)
                    <div class="form-check">
                        <input type="hidden" name="{{ $field }}" value="0">
                        <input type="checkbox" name="{{ $field }}" value="1" class="form-check-input" id="{{ $field }}" @checked(old($field, $site->settings?->{$field} ?? true))>
                        <label class="form-check-label" for="{{ $field }}">{{ $label }}</label>
                    </div>
                @endforeach
            </div>
            <div class="card-footer">
                <button class="btn btn-primary">Save settings</button>
            </div>
        </form>
    </div>
@endsection
