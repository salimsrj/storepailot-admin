<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign Up | CommercePilot</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
</head>
<body class="hold-transition login-page">
<div class="login-box" style="width: 420px;">
    <div class="login-logo">
        <b>Commerce</b>Pilot
    </div>
    <div class="card">
        <div class="card-body login-card-body">
            <p class="login-box-msg">Create your CommercePilot account</p>
            <p class="text-muted text-sm text-center mb-3">
                Connect your WooCommerce store to get API credentials for the plugin.
            </p>

            <form method="POST" action="{{ route('signup.store') }}">
                @csrf
                <div class="input-group mb-3">
                    <input
                        type="url"
                        name="website_url"
                        class="form-control @error('website_url') is-invalid @enderror"
                        value="{{ old('website_url') }}"
                        placeholder="Website URL (https://example.com)"
                        required
                        autofocus
                    >
                    <div class="input-group-append">
                        <div class="input-group-text"><span class="fas fa-globe"></span></div>
                    </div>
                    @error('website_url')
                        <span class="invalid-feedback d-block">{{ $message }}</span>
                    @enderror
                </div>

                <div class="input-group mb-3">
                    <input
                        type="text"
                        name="first_name"
                        class="form-control @error('first_name') is-invalid @enderror"
                        value="{{ old('first_name') }}"
                        placeholder="First name"
                        required
                    >
                    <div class="input-group-append">
                        <div class="input-group-text"><span class="fas fa-user"></span></div>
                    </div>
                    @error('first_name')
                        <span class="invalid-feedback d-block">{{ $message }}</span>
                    @enderror
                </div>

                <div class="input-group mb-3">
                    <input
                        type="text"
                        name="last_name"
                        class="form-control @error('last_name') is-invalid @enderror"
                        value="{{ old('last_name') }}"
                        placeholder="Last name"
                        required
                    >
                    <div class="input-group-append">
                        <div class="input-group-text"><span class="fas fa-user"></span></div>
                    </div>
                    @error('last_name')
                        <span class="invalid-feedback d-block">{{ $message }}</span>
                    @enderror
                </div>

                <div class="input-group mb-3">
                    <input
                        type="email"
                        name="email"
                        class="form-control @error('email') is-invalid @enderror"
                        value="{{ old('email') }}"
                        placeholder="Email"
                        required
                    >
                    <div class="input-group-append">
                        <div class="input-group-text"><span class="fas fa-envelope"></span></div>
                    </div>
                    @error('email')
                        <span class="invalid-feedback d-block">{{ $message }}</span>
                    @enderror
                </div>

                <div class="input-group mb-3">
                    <input
                        type="password"
                        name="password"
                        class="form-control @error('password') is-invalid @enderror"
                        placeholder="Password"
                        required
                    >
                    <div class="input-group-append">
                        <div class="input-group-text"><span class="fas fa-lock"></span></div>
                    </div>
                    @error('password')
                        <span class="invalid-feedback d-block">{{ $message }}</span>
                    @enderror
                </div>

                <div class="input-group mb-3">
                    <input
                        type="password"
                        name="password_confirmation"
                        class="form-control"
                        placeholder="Confirm password"
                        required
                    >
                    <div class="input-group-append">
                        <div class="input-group-text"><span class="fas fa-lock"></span></div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Sign Up</button>
            </form>

            <p class="mb-0 mt-3 text-center">
                <a href="{{ route('login') }}">Already have an account? Sign in</a>
            </p>
        </div>
    </div>
</div>
</body>
</html>
