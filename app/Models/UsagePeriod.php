<?php

namespace App\Models;

use Database\Factories\UsagePeriodFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'site_id',
    'period_start',
    'period_end',
    'message_limit',
    'message_count',
    'input_tokens',
    'output_tokens',
])]
class UsagePeriod extends Model
{
    /** @use HasFactory<UsagePeriodFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'message_limit' => 'integer',
            'message_count' => 'integer',
            'input_tokens' => 'integer',
            'output_tokens' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Site, $this>
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function remainingMessages(): int
    {
        return max(0, $this->message_limit - $this->message_count);
    }
}
