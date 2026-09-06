@extends('admin.layouts.app')

@section('title', 'Webhooks')

@section('content')
    <div class="card">
        <div class="card-body p-0">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>When</th>
                        <th>Provider</th>
                        <th>Event</th>
                        <th>Processed</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($events as $event)
                        <tr>
                            <td><a href="{{ route('admin.webhooks.show', $event) }}">{{ $event->created_at }}</a></td>
                            <td>{{ $event->provider->value }}</td>
                            <td>{{ $event->event_type }}</td>
                            <td>{{ $event->processed_at ? 'Yes' : 'No' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $events->links() }}</div>
    </div>
@endsection
