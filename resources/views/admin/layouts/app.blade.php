<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') | CommercePilot</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
        </ul>
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button class="btn btn-link nav-link" type="submit">Sign out</button>
                </form>
            </li>
        </ul>
    </nav>

    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="{{ route('admin.dashboard') }}" class="brand-link">
            <span class="brand-text font-weight-light">CommercePilot</span>
        </a>
        <div class="sidebar">
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="info">
                    <a href="#" class="d-block">{{ auth()->user()->name }}</a>
                </div>
            </div>
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                    @include('admin.partials.menu-item', ['route' => 'admin.dashboard', 'icon' => 'fas fa-tachometer-alt', 'label' => 'Dashboard'])
                    @include('admin.partials.menu-item', ['route' => 'admin.users.index', 'icon' => 'fas fa-users', 'label' => 'Users', 'active' => 'admin.users.*'])
                    @include('admin.partials.menu-item', ['route' => 'admin.plans.index', 'icon' => 'fas fa-tags', 'label' => 'Plans', 'active' => 'admin.plans.*'])
                    @include('admin.partials.menu-item', ['route' => 'admin.subscriptions.index', 'icon' => 'fas fa-credit-card', 'label' => 'Subscriptions', 'active' => 'admin.subscriptions.*'])
                    @include('admin.partials.menu-item', ['route' => 'admin.sites.index', 'icon' => 'fas fa-store', 'label' => 'Sites', 'active' => 'admin.sites.*'])
                    @include('admin.partials.menu-item', ['route' => 'admin.conversations.index', 'icon' => 'fas fa-comments', 'label' => 'Conversations', 'active' => 'admin.conversations.*'])
                    @include('admin.partials.menu-item', ['route' => 'admin.usage-periods.index', 'icon' => 'fas fa-chart-bar', 'label' => 'Usage periods'])
                    @include('admin.partials.menu-item', ['route' => 'admin.usage-events.index', 'icon' => 'fas fa-list', 'label' => 'Usage events'])
                    @include('admin.partials.menu-item', ['route' => 'admin.webhooks.index', 'icon' => 'fas fa-bolt', 'label' => 'Webhooks', 'active' => 'admin.webhooks.*'])
                    @include('admin.partials.menu-item', ['route' => 'admin.settings.ai', 'icon' => 'fas fa-cog', 'label' => 'AI settings', 'active' => 'admin.settings.*'])
                </ul>
            </nav>
        </div>
    </aside>

    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>@yield('title', 'Dashboard')</h1>
                    </div>
                    <div class="col-sm-6 text-right">
                        @yield('actions')
                    </div>
                </div>
            </div>
        </section>
        <section class="content">
            <div class="container-fluid">
                @if (session('status'))
                    <div class="alert alert-success">{{ session('status') }}</div>
                @endif
                @if (session('site_token'))
                    <div class="alert alert-warning">
                        <p class="mb-1"><strong>Site token (shown once):</strong> <code>{{ session('site_token') }}</code></p>
                        @if (session('site_secret'))
                            <p class="mb-0"><strong>Site secret (shown once):</strong> <code>{{ session('site_secret') }}</code></p>
                        @endif
                    </div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @yield('content')
            </div>
        </section>
    </div>

    <footer class="main-footer">
        <strong>CommercePilot</strong> admin
    </footer>
</div>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.4/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
</body>
</html>
