<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateAiSettingRequest;
use App\Services\Ai\AiSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AiSettingController extends Controller
{
    public function edit(AiSettingsService $settings): View
    {
        return view('admin.settings.ai', [
            'settings' => $settings->current(),
            'envKeyConfigured' => filled(config('services.openai.api_key')),
        ]);
    }

    public function update(UpdateAiSettingRequest $request, AiSettingsService $settings): RedirectResponse
    {
        $settings->update($request->validated());

        return redirect()->route('admin.settings.ai')->with('status', 'AI settings saved.');
    }
}
