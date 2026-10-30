<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SystemSettingController extends Controller
{
    protected AuditService $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    public function index()
    {
        $settings = SystemSetting::all()->groupBy('group');
        return view('masters.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->except(['_token', '_method']);

        foreach ($data as $key => $value) {
            $setting = SystemSetting::where('key', $key)->first();
            if ($setting) {
                $setting->value = is_array($value) ? json_encode($value) : $value;
                $setting->save();
            }
        }

        $this->auditService->log(Auth::user(), 'Updated System Settings', 'System Settings', SystemSetting::class);

        return back()->with('success', 'Global system settings saved successfully.');
    }
}

