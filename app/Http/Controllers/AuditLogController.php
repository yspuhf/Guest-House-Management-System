<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('actor');

        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }

        if ($request->filled('action')) {
            $query->where('action', 'like', "%{$request->action}%");
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('actor_name', 'like', "%{$request->search}%")
                  ->orWhere('action', 'like', "%{$request->search}%")
                  ->orWhere('ip_address', 'like', "%{$request->search}%");
            });
        }

        $logs = $query->orderBy('logged_at', 'desc')->paginate(30)->withQueryString();
        $modules = AuditLog::select('module')->distinct()->pluck('module');

        return view('oversight.audit_logs', compact('logs', 'modules'));
    }
}

