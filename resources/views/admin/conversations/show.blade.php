@extends('admin.layouts.app')

@section('title', 'Conversation')

@section('content')
    <div class="card">
        <div class="card-body">
            <p><strong>Site:</strong> {{ $conversation->site->name }}</p>
            <p><strong>Visitor:</strong> {{ $conversation->visitor->uuid }}</p>
            <p><strong>Messages:</strong> {{ $conversation->message_count }}</p>
            <form method="POST" action="{{ route('admin.conversations.update', $conversation) }}" class="mb-3">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        @foreach ($statuses as $status)
                            <option value="{{ $status->value }}" @selected(old('status', $conversation->status->value) === $status->value)>{{ $status->value }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Summary</label>
                    <textarea name="summary" class="form-control" rows="3">{{ old('summary', $conversation->summary) }}</textarea>
                </div>
                <button class="btn btn-primary">Update</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3 class="card-title">Messages</h3></div>
        <div class="card-body">
            @foreach ($conversation->messages as $message)
                <div class="mb-3">
                    <strong>{{ $message->role->value }}</strong>
                    @if ($message->tool_name)
                        <span class="badge badge-secondary">{{ $message->tool_name }}</span>
                    @endif
                    <p class="mb-0">{{ $message->content }}</p>
                </div>
            @endforeach
        </div>
        <div class="card-footer">
            <form method="POST" action="{{ route('admin.conversations.destroy', $conversation) }}" onsubmit="return confirm('Delete this conversation?')">
                @csrf
                @method('DELETE')
                <button class="btn btn-danger">Delete</button>
            </form>
        </div>
    </div>
@endsection
