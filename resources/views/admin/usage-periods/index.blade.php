@extends('admin.layouts.app')

@section('title', 'Usage periods')

@section('content')
    <div class="card">
        <div class="card-body p-0">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Site</th>
                        <th>User</th>
                        <th>Period</th>
                        <th>Used</th>
                        <th>Limit</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($periods as $period)
                        <tr>
                            <td>{{ $period->site->name }}</td>
                            <td>{{ $period->user->name }}</td>
                            <td>{{ $period->period_start?->toDateString() }} – {{ $period->period_end?->toDateString() }}</td>
                            <td>{{ $period->message_count }}</td>
                            <td>{{ $period->message_limit }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $periods->links() }}</div>
    </div>
@endsection
