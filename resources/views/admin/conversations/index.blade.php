@extends('admin.layouts.app')

@section('title', 'Conversations')

@section('content')
    <div class="card">
        <div class="card-body p-0">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Site</th>
                        <th>Visitor</th>
                        <th>Status</th>
                        <th>Messages</th>
                        <th>Last message</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($conversations as $conversation)
                        <tr>
                            <td><a href="{{ route('admin.conversations.show', $conversation) }}">{{ $conversation->site->name }}</a></td>
                            <td>{{ $conversation->visitor->uuid }}</td>
                            <td>{{ $conversation->status->value }}</td>
                            <td>{{ $conversation->message_count }}</td>
                            <td>{{ $conversation->last_message_at }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $conversations->links() }}</div>
    </div>
@endsection
