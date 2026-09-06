<?php

namespace App\Http\Controllers\Admin;

use App\Enums\SiteStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSiteRequest;
use App\Http\Requests\Admin\UpdateSiteRequest;
use App\Http\Requests\Admin\UpdateSiteSettingsRequest;
use App\Models\Site;
use App\Models\User;
use App\Services\Security\SiteSecretService;
use App\Services\Sites\SiteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SiteController extends Controller
{
    public function __construct(private SiteService $sites) {}

    public function index(): View
    {
        return view('admin.sites.index', [
            'sites' => Site::query()->with('user')->latest()->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.sites.create', [
            'users' => User::query()->orderBy('name')->get(['id', 'name', 'email']),
        ]);
    }

    public function store(StoreSiteRequest $request): RedirectResponse
    {
        $owner = User::query()->findOrFail($request->validated('user_id'));
        $result = $this->sites->register($owner, $request->safe()->except(['user_id']));

        return redirect()
            ->route('admin.sites.show', $result['site'])
            ->with('status', 'Site created. Copy the token and secret now; they will not be shown again.')
            ->with('site_token', $result['token'])
            ->with('site_secret', $result['secret']);
    }

    public function show(Site $site): View
    {
        $site->load(['user', 'settings', 'usagePeriods']);

        return view('admin.sites.show', [
            'site' => $site,
            'statuses' => SiteStatus::cases(),
            'users' => User::query()->orderBy('name')->get(['id', 'name', 'email']),
        ]);
    }

    public function edit(Site $site): View
    {
        return view('admin.sites.edit', [
            'site' => $site,
            'statuses' => SiteStatus::cases(),
            'users' => User::query()->orderBy('name')->get(['id', 'name', 'email']),
        ]);
    }

    public function update(UpdateSiteRequest $request, Site $site): RedirectResponse
    {
        $this->sites->update($site, $request->safe()->except(['status', 'user_id']));
        $site->forceFill([
            'user_id' => $request->validated('user_id'),
            'status' => $request->validated('status'),
        ])->save();

        return redirect()->route('admin.sites.show', $site)->with('status', 'Site updated.');
    }

    public function updateSettings(UpdateSiteSettingsRequest $request, Site $site): RedirectResponse
    {
        $data = $request->validated();
        $data['enable_product_search'] = $request->boolean('enable_product_search');
        $data['enable_recommendations'] = $request->boolean('enable_recommendations');
        $data['enable_cart'] = $request->boolean('enable_cart');
        $data['enable_checkout'] = $request->boolean('enable_checkout');
        $data['enable_order_tracking'] = $request->boolean('enable_order_tracking');

        $this->sites->updateSettings($site, $data);

        return redirect()->route('admin.sites.show', $site)->with('status', 'Site settings updated.');
    }

    public function rotateToken(Site $site, SiteSecretService $secrets): RedirectResponse
    {
        $result = $this->sites->rotateToken($site);

        return redirect()
            ->route('admin.sites.show', $site)
            ->with('status', 'Site token rotated. Copy the new token now. The site secret is unchanged.')
            ->with('site_token', $result['token'])
            ->with('site_secret', $secrets->secretFor($site->refresh()));
    }

    public function destroy(Site $site): RedirectResponse
    {
        $site->delete();

        return redirect()->route('admin.sites.index')->with('status', 'Site deleted.');
    }
}
