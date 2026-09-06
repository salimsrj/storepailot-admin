<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UsageResource;
use App\Models\Site;
use App\Services\Usage\UsageService;
use Illuminate\Http\Request;

class UsageController extends Controller
{
    public function show(Request $request, UsageService $usage): UsageResource
    {
        $site = $request->attributes->get('site');
        assert($site instanceof Site);

        return new UsageResource($usage->snapshot($site));
    }
}
