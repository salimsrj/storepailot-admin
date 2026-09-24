@extends('merchant.layouts.app')

@section('title', 'Verify email')

@section('content')
    <div class="card">
        <div class="card-body">
            @if ($email)
                <p>Check <strong>{{ $email }}</strong> for a verification link.</p>
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit" class="btn btn-primary">Resend verification email</button>
                </form>
            @else
                <p>Sign in to request a verification email.</p>
                <a href="{{ route('login') }}" class="btn btn-primary">Sign in</a>
            @endif
        </div>
    </div>
@endsection
