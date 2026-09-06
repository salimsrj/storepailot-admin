<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UsageEvent;
use Illuminate\View\View;

class UsageEventController extends Controller
{
    public function index(): View
    {
        return view('admin.usage-events.index', [
            'events' => UsageEvent::query()
                ->with(['site', 'user', 'conversation'])
                ->latest()
                ->paginate(20),
        ]);
    }
}
