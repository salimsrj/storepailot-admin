<?php

namespace App\Policies;

use App\Models\Conversation;
use App\Models\Site;
use App\Models\User;

class ConversationPolicy
{
    public function view(User $user, Conversation $conversation): bool
    {
        return $conversation->site()->where('user_id', $user->id)->exists();
    }

    public function viewForSite(Site $site, Conversation $conversation): bool
    {
        return $conversation->site_id === $site->id;
    }
}
