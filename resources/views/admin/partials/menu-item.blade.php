@php
    $activePattern = $active ?? $route;
@endphp
<li class="nav-item">
    <a href="{{ route($route) }}" class="nav-link {{ request()->routeIs($activePattern) ? 'active' : '' }}">
        <i class="nav-icon {{ $icon }}"></i>
        <p>{{ $label }}</p>
    </a>
</li>
