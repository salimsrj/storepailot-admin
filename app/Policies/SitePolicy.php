<?php

namespace App\Policies;

use App\Enums\UserStatus;
use App\Models\Site;
use App\Models\User;

class SitePolicy
{
    public function view(User $user, Site $site): bool
    {
        return $site->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->status === UserStatus::Active;
    }

    public function update(User $user, Site $site): bool
    {
        return $this->view($user, $site);
    }

    public function delete(User $user, Site $site): bool
    {
        return $this->view($user, $site);
    }
}
