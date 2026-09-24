@extends('merchant.layouts.app')

@section('title', 'Profile')

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('dashboard.profile.update') }}">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label for="first_name">First name</label>
                    <input type="text" name="first_name" id="first_name" class="form-control @error('first_name') is-invalid @enderror" value="{{ old('first_name', $user->first_name) }}" required>
                    @error('first_name')
                        <span class="invalid-feedback d-block">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="last_name">Last name</label>
                    <input type="text" name="last_name" id="last_name" class="form-control @error('last_name') is-invalid @enderror" value="{{ old('last_name', $user->last_name) }}" required>
                    @error('last_name')
                        <span class="invalid-feedback d-block">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                    @error('email')
                        <span class="invalid-feedback d-block">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="password">New password</label>
                    <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" autocomplete="new-password">
                    <small class="form-text text-muted">Leave blank to keep your current password.</small>
                    @error('password')
                        <span class="invalid-feedback d-block">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="password_confirmation">Confirm new password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" autocomplete="new-password">
                </div>
                <button type="submit" class="btn btn-primary">Save profile</button>
            </form>
        </div>
    </div>
@endsection
