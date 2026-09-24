@extends('merchant.layouts.app')

@section('title', 'Credentials')

@section('content')
    @unless ($verified)
        <div class="alert alert-warning">
            <p class="mb-2">Connection credentials are hidden until you verify your email.</p>
            <p class="mb-2">We sent a verification link to <strong>{{ $user->email }}</strong>.</p>
            <form method="POST" action="{{ route('verification.send') }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-warning btn-sm">Resend verification email</button>
            </form>
        </div>
    @else
        @if (! $site || ! $credentials || blank($credentials['site_token']))
            <div class="alert alert-danger">No site credentials are available for this account.</div>
        @else
            <div class="card">
                <div class="card-body">
                    <p class="text-muted">Paste these into WooCommerce → CommercePilot → Connection.</p>

                    @foreach ([
                        'api_url' => 'API URL',
                        'site_id' => 'Site ID',
                        'site_token' => 'Site token',
                        'site_secret' => 'Site secret',
                    ] as $key => $label)
                        <div>
                            <div class="credential-label">{{ $label }}</div>
                            <div class="credential-row">
                                <input type="text" class="form-control" id="credential-{{ $key }}" value="{{ $credentials[$key] }}" readonly>
                                <button type="button" class="btn btn-outline-secondary copy-btn" data-target="credential-{{ $key }}" title="Copy">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endunless
@endsection

@push('scripts')
<script>
    document.querySelectorAll('.copy-btn').forEach(function (button) {
        button.addEventListener('click', function () {
            var input = document.getElementById(button.getAttribute('data-target'));
            navigator.clipboard.writeText(input.value).then(function () {
                button.innerHTML = '<i class="fas fa-check"></i>';
                setTimeout(function () {
                    button.innerHTML = '<i class="fas fa-copy"></i>';
                }, 1500);
            });
        });
    });
</script>
@endpush
