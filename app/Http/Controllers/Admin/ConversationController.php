<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ConversationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateConversationRequest;
use App\Models\Conversation;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ConversationController extends Controller
{
    public function index(): View
    {
        return view('admin.conversations.index', [
            'conversations' => Conversation::query()
                ->with(['site', 'visitor'])
                ->latest('last_message_at')
                ->latest()
                ->paginate(20),
        ]);
    }

    public function show(Conversation $conversation): View
    {
        $conversation->load(['site', 'visitor', 'messages']);

        return view('admin.conversations.show', [
            'conversation' => $conversation,
            'statuses' => ConversationStatus::cases(),
        ]);
    }

    public function update(UpdateConversationRequest $request, Conversation $conversation): RedirectResponse
    {
        $conversation->update($request->validated());

        return redirect()->route('admin.conversations.show', $conversation)->with('status', 'Conversation updated.');
    }

    public function destroy(Conversation $conversation): RedirectResponse
    {
        $conversation->delete();

        return redirect()->route('admin.conversations.index')->with('status', 'Conversation deleted.');
    }
}
