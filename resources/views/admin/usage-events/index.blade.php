@extends('admin.layouts.app')

@section('title', 'Usage events')

@section('content')
    <div class="card">
        <div class="card-body p-0">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>When</th>
                        <th>Site</th>
                        <th>Type</th>
                        <th>Units</th>
                        <th>Tokens</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($events as $event)
                        <tr>
                            <td>{{ $event->created_at }}</td>
                            <td>{{ $event->site->name }}</td>
                            <td>{{ $event->type->value }}</td>
                            <td>{{ $event->units }}</td>
                            <td>{{ $event->input_tokens }} / {{ $event->output_tokens }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $events->links() }}</div>
    </div>
@endsection
