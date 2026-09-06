@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $users }}</h3>
                    <p>Users</p>
                </div>
                <div class="icon"><i class="fas fa-users"></i></div>
                <a href="{{ route('admin.users.index') }}" class="small-box-footer">Manage <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $sites }}</h3>
                    <p>Sites</p>
                </div>
                <div class="icon"><i class="fas fa-store"></i></div>
                <a href="{{ route('admin.sites.index') }}" class="small-box-footer">Manage <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $conversations }}</h3>
                    <p>Conversations</p>
                </div>
                <div class="icon"><i class="fas fa-comments"></i></div>
                <a href="{{ route('admin.conversations.index') }}" class="small-box-footer">Manage <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $activeSubscriptions }}</h3>
                    <p>Active subscriptions</p>
                </div>
                <div class="icon"><i class="fas fa-credit-card"></i></div>
                <a href="{{ route('admin.subscriptions.index') }}" class="small-box-footer">Manage <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Recent sites</h3></div>
                <div class="card-body p-0">
                    <table class="table table-striped">
                        <thead>
                            <tr><th>Name</th><th>Owner</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                            @forelse ($recentSites as $site)
                                <tr>
                                    <td><a href="{{ route('admin.sites.show', $site) }}">{{ $site->name }}</a></td>
                                    <td>{{ $site->user->name }}</td>
                                    <td>{{ $site->status->value }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3">No sites yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Recent conversations</h3></div>
                <div class="card-body p-0">
                    <table class="table table-striped">
                        <thead>
                            <tr><th>Site</th><th>Status</th><th>Messages</th></tr>
                        </thead>
                        <tbody>
                            @forelse ($recentConversations as $conversation)
                                <tr>
                                    <td><a href="{{ route('admin.conversations.show', $conversation) }}">{{ $conversation->site->name }}</a></td>
                                    <td>{{ $conversation->status->value }}</td>
                                    <td>{{ $conversation->message_count }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3">No conversations yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
