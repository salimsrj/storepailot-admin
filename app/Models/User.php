<?php

namespace App\Models;

use App\Enums\SubscriptionStatus;
use App\Enums\UserStatus;
use App\Models\Concerns\HasPublicUuid;
use Database\Factories\UserFactory;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'first_name', 'last_name', 'email', 'password', 'status', 'is_admin'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasPublicUuid, MustVerifyEmailTrait, Notifiable;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'status' => UserStatus::class,
            'is_admin' => 'boolean',
        ];
    }

    /**
     * @return HasMany<Site, $this>
     */
    public function sites(): HasMany
    {
        return $this->hasMany(Site::class);
    }

    /**
     * @return HasMany<Subscription, $this>
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * @return HasOne<Subscription, $this>
     */
    public function currentSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)->ofMany(
            ['id' => 'max'],
            function (Builder $query): void {
                $query->whereIn('status', [
                    SubscriptionStatus::Trialing,
                    SubscriptionStatus::Active,
                    SubscriptionStatus::PastDue,
                ]);
            },
        );
    }

    /**
     * @return HasMany<UsagePeriod, $this>
     */
    public function usagePeriods(): HasMany
    {
        return $this->hasMany(UsagePeriod::class);
    }

    /**
     * @return HasMany<UsageEvent, $this>
     */
    public function usageEvents(): HasMany
    {
        return $this->hasMany(UsageEvent::class);
    }

    /**
     * @param  Builder<User>  $query
     * @return Builder<User>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', UserStatus::Active);
    }

    public function isAdmin(): bool
    {
        return $this->is_admin === true;
    }
}
