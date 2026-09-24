@php
    $isActive = isset($active)
        ? request()->routeIs($active)
        : request()->routeIs($route);
@endphp
<li class="nav-item">
    <a href="{{ route($route) }}" class="nav-link {{ $isActive ? 'active' : '' }}">
        <i class="nav-icon {{ $icon }}"></i>
        <p>{{ $label }}</p>
    </a>
</li>
