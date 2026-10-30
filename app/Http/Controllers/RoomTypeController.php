<?php

namespace App\Http\Controllers;

use App\Models\RoomType;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoomTypeController extends Controller
{
    protected AuditService $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    public function index()
    {
        $roomTypes = RoomType::withCount('rooms')->get();
        return view('masters.roomtypes.index', compact('roomTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:room_types,code',
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'default_tariff' => 'required|numeric|min:0',
            'standard_capacity' => 'required|integer|min:1|max:20',
        ]);

        $roomType = RoomType::create($validated);

        $this->auditService->log(Auth::user(), 'Created Room Type', 'Room Type', RoomType::class, $roomType->id, null, $roomType->toArray());

        return back()->with('success', "Room Type '{$roomType->name}' created successfully.");
    }

    public function update(Request $request, RoomType $roomtype)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'default_tariff' => 'required|numeric|min:0',
            'standard_capacity' => 'required|integer|min:1|max:20',
            'is_active' => 'required|boolean',
        ]);

        $before = $roomtype->toArray();
        $roomtype->update($validated);

        $this->auditService->log(Auth::user(), 'Updated Room Type', 'Room Type', RoomType::class, $roomtype->id, $before, $roomtype->toArray());

        return back()->with('success', "Room Type '{$roomtype->name}' updated successfully.");
    }

    public function destroy(RoomType $roomtype)
    {
        // Business rule: Do not delete a type referenced by existing history; deactivate it instead.
        $hasHistory = $roomtype->rooms()->exists() || $roomtype->bookings()->exists();
        if ($hasHistory) {
            $roomtype->update(['is_active' => false]);
            return back()->with('info', "Room Type is referenced by existing rooms/bookings and was deactivated instead of deleted.");
        }

        $roomtype->delete();
        return back()->with('success', "Room Type deleted successfully.");
    }
}

