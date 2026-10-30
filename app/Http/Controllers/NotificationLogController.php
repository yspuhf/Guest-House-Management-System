<?php

namespace App\Http\Controllers;

use App\Models\NotificationLog;
use Illuminate\Http\Request;

class NotificationLogController extends Controller
{
    public function index(Request $request)
    {
        $query = NotificationLog::with('booking');

        if ($request->filled('delivery_status')) {
            $query->where('delivery_status', $request->delivery_status);
        }

        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        $notifications = $query->latest()->paginate(25)->withQueryString();

        return view('oversight.notification_logs', compact('notifications'));
    }
}

