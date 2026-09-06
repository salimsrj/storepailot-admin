@extends('admin.layouts.app')

@section('title', 'Webhook event')

@section('content')
    <div class="card">
        <div class="card-body">
            <p><strong>Provider:</strong> {{ $event->provider->value }}</p>
            <p><strong>Event ID:</strong> {{ $event->event_id }}</p>
            <p><strong>Type:</strong> {{ $event->event_type }}</p>
            <p><strong>Processed:</strong> {{ $event->processed_at }}</p>
            <pre class="bg-light p-3">{{ json_encode($event->payload, JSON_PRETTY_PRINT) }}</pre>
        </div>
    </div>
@endsection
