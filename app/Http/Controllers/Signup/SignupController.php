<?php

namespace App\Http\Controllers\Signup;

use App\Http\Controllers\Controller;
use App\Http\Requests\Signup\StoreSignupRequest;
use App\Services\Signup\SignupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SignupController extends Controller
{
    public function create(): View
    {
        return view('signup.create');
    }

    public function store(StoreSignupRequest $request, SignupService $signup): RedirectResponse
    {
        $signup->register($request->validated());

        return redirect()
            ->route('login')
            ->with('status', 'Account created. Check your email to verify, then sign in.');
    }
}
