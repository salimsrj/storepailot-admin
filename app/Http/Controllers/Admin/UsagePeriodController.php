<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UsagePeriod;
use Illuminate\View\View;

class UsagePeriodController extends Controller
{
    public function index(): View
    {
        return view('admin.usage-periods.index', [
            'periods' => UsagePeriod::query()
                ->with(['site', 'user'])
                ->latest('period_start')
                ->paginate(20),
        ]);
    }
}
