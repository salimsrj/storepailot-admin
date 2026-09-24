<?php

namespace App\Http\Controllers\Signup;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailVerificationController extends Controller
{
    public function notice(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if ($user instanceof User && $user->hasVerifiedEmail()) {
            return redirect()
                ->route('dashboard.credentials')
                ->with('status', 'Your email is already verified.');
        }

        return view('merchant.verify-email', [
            'email' => $user?->email,
        ]);
    }

    public function verify(Request $request, int $id, string $hash): RedirectResponse
    {
        $user = User::query()->findOrFail($id);

        if (! hash_equals(sha1($user->getEmailForVerification()), $hash)) {
            abort(403, 'Invalid verification link.');
        }

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        if ($request->user()?->is($user)) {
            return redirect()
                ->route('dashboard.credentials')
                ->with('status', 'Your email has been verified. You can view your connection credentials.');
        }

        return redirect()
            ->route('login')
            ->with('status', 'Your email has been verified. Please sign in.');
    }

    public function resend(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Sign in to request a verification email.']);
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()
                ->route('dashboard.credentials')
                ->with('status', 'Your email is already verified.');
        }

        $user->sendEmailVerificationNotification();

        return back()->with('status', 'A new verification link has been sent to '.$user->email.'.');
    }
}
