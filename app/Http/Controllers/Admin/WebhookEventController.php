<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WebhookEvent;
use Illuminate\View\View;

class WebhookEventController extends Controller
{
    public function index(): View
    {
        return view('admin.webhooks.index', [
            'events' => WebhookEvent::query()->latest()->paginate(20),
        ]);
    }

    public function show(WebhookEvent $webhook): View
    {
        return view('admin.webhooks.show', [
            'event' => $webhook,
        ]);
    }
}
