<?php

namespace App\Models;

use Database\Factories\SiteSettingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'site_id',
    'assistant_name',
    'welcome_message',
    'language',
    'tone',
    'system_prompt',
    'enable_product_search',
    'enable_recommendations',
    'enable_cart',
    'enable_checkout',
    'enable_order_tracking',
    'settings',
])]
class SiteSetting extends Model
{
    /** @use HasFactory<SiteSettingFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'enable_product_search' => 'boolean',
            'enable_recommendations' => 'boolean',
            'enable_cart' => 'boolean',
            'enable_checkout' => 'boolean',
            'enable_order_tracking' => 'boolean',
            'settings' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Site, $this>
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
