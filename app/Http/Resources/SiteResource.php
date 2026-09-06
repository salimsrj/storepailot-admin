<?php

namespace App\Http\Resources;

use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Site
 */
class SiteResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'name' => $this->name,
            'url' => $this->url,
            'domain' => $this->domain,
            'status' => $this->status->value,
            'plugin_version' => $this->plugin_version,
            'wordpress_version' => $this->wordpress_version,
            'woocommerce_version' => $this->woocommerce_version,
            'last_seen_at' => $this->last_seen_at,
        ];
    }
}
