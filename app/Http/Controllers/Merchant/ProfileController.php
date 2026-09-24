<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Merchant\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('merchant.profile.edit', [
            'user' => $request->user(),
        ]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->safe()->only(['first_name', 'last_name', 'email']);
        $emailChanged = $data['email'] !== $user->email;

        $user->fill([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'name' => trim($data['first_name'].' '.$data['last_name']),
            'email' => $data['email'],
        ]);

        if ($request->filled('password')) {
            $user->password = $request->validated('password');
        }

        if ($emailChanged) {
            $user->email_verified_at = null;
        }

        $user->save();

        if ($emailChanged) {
            $user->sendEmailVerificationNotification();
        }

        return redirect()
            ->route('dashboard.profile.edit')
            ->with('status', $emailChanged
                ? 'Profile updated. Please verify your new email address.'
                : 'Profile updated.');
    }
}
