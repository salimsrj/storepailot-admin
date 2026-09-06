<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Site;
use App\Models\Subscription;
use App\Models\UsageEvent;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'users' => User::query()->count(),
            'sites' => Site::query()->count(),
            'conversations' => Conversation::query()->count(),
            'activeSubscriptions' => Subscription::query()->billable()->count(),
            'messagesToday' => UsageEvent::query()
                ->where('type', 'message')
                ->whereDate('created_at', now()->toDateString())
                ->count(),
            'recentSites' => Site::query()->with('user')->latest()->limit(5)->get(),
            'recentConversations' => Conversation::query()
                ->with(['site', 'visitor'])
                ->latest()
                ->limit(5)
                ->get(),
        ]);
    }
}
