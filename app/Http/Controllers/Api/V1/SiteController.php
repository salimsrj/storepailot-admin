<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterSiteRequest;
use App\Http\Requests\RotateSiteTokenRequest;
use App\Http\Requests\UpdateSiteRequest;
use App\Http\Requests\UpdateSiteSettingsRequest;
use App\Http\Resources\SiteResource;
use App\Models\Site;
use App\Services\Sites\SiteService;
use Illuminate\Http\JsonResponse;

class SiteController extends Controller
{
    public function store(RegisterSiteRequest $request, SiteService $sites): JsonResponse
    {
        $user = $request->user();
        abort_if($user === null, 401);

        $this->authorize('create', Site::class);

        $result = $sites->register($user, $request->validated());

        return (new SiteResource($result['site']))
            ->additional([
                'token' => $result['token'],
            ])
            ->response()
            ->setStatusCode(201);
    }

    public function show(UpdateSiteRequest $request): SiteResource
    {
        $site = $request->attributes->get('site');
        assert($site instanceof Site);

        return new SiteResource($site);
    }

    public function update(UpdateSiteRequest $request, SiteService $sites): SiteResource
    {
        $site = $request->attributes->get('site');
        assert($site instanceof Site);

        return new SiteResource($sites->update($site, $request->validated()));
    }

    public function updateSettings(UpdateSiteSettingsRequest $request, SiteService $sites): JsonResponse
    {
        $site = $request->attributes->get('site');
        assert($site instanceof Site);

        $settings = $sites->updateSettings($site, $request->validated());

        return response()->json([
            'data' => [
                'assistant_name' => $settings->assistant_name,
                'welcome_message' => $settings->welcome_message,
                'language' => $settings->language,
                'tone' => $settings->tone,
                'enable_product_search' => $settings->enable_product_search,
                'enable_recommendations' => $settings->enable_recommendations,
                'enable_cart' => $settings->enable_cart,
                'enable_checkout' => $settings->enable_checkout,
                'enable_order_tracking' => $settings->enable_order_tracking,
            ],
        ]);
    }

    public function rotateToken(RotateSiteTokenRequest $request, SiteService $sites): JsonResponse
    {
        $site = $request->attributes->get('site');
        assert($site instanceof Site);

        return response()->json($sites->rotateToken($site));
    }
}
