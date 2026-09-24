<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $user->load(['sites', 'currentSubscription.plan']);

        return view('merchant.dashboard', [
            'user' => $user,
            'site' => $user->sites->first(),
            'subscription' => $user->currentSubscription,
        ]);
    }
}
