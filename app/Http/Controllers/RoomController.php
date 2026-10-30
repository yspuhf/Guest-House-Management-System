<?php

namespace App\Http\Controllers;

use App\Models\GuestHouse;
use App\Models\Room;
use App\Models\RoomType;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoomController extends Controller
{
    protected AuditService $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    public function index(Request $request)
    {
        $query = Room::with(['guestHouse', 'roomType']);

        if ($request->filled('guest_house_id')) {
            $query->where('guest_house_id', $request->guest_house_id);
        }

        if ($request->filled('room_type_id')) {
            $query->where('room_type_id', $request->room_type_id);
        }

        if ($request->filled('maintenance_status')) {
            $query->where('maintenance_status', $request->maintenance_status);
        }

        $rooms = $query->paginate(25);
        $guestHouses = GuestHouse::where('is_active', true)->get();
        $roomTypes = RoomType::where('is_active', true)->get();

        return view('masters.rooms.index', compact('rooms', 'guestHouses', 'roomTypes'));
    }

    public function create()
    {
        $guestHouses = GuestHouse::where('is_active', true)->get();
        $roomTypes = RoomType::where('is_active', true)->get();
        return view('masters.rooms.create', compact('guestHouses', 'roomTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'guest_house_id' => 'required|exists:guest_houses,id',
            'room_type_id' => 'required|exists:room_types,id',
            'room_number' => 'required|string|max:50',
            'floor' => 'nullable|string|max:50',
            'tariff' => 'nullable|numeric|min:0',
            'max_occupancy' => 'required|integer|min:1|max:20',
            'facilities' => 'nullable|array',
            'publication_status' => 'required|in:draft,published',
            'maintenance_status' => 'required|in:operational,under_maintenance,cleaning_in_progress',
            'notes' => 'nullable|string',
        ]);

        // Unique check within guest house
        $exists = Room::where('guest_house_id', $validated['guest_house_id'])
            ->where('room_number', $validated['room_number'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['room_number' => "Room number '{$validated['room_number']}' already exists in this guest house."])->withInput();
        }

        $room = Room::create($validated);

        $this->auditService->log(Auth::user(), 'Created Room', 'Rooms', Room::class, $room->id, null, $room->toArray());

        return redirect()->route('masters.rooms.index')->with('success', "Room '{$room->room_number}' created successfully.");
    }

    public function edit(Room $room)
    {
        $guestHouses = GuestHouse::where('is_active', true)->get();
        $roomTypes = RoomType::where('is_active', true)->get();
        return view('masters.rooms.edit', compact('room', 'guestHouses', 'roomTypes'));
    }

    public function update(Request $request, Room $room)
    {
        $validated = $request->validate([
            'guest_house_id' => 'required|exists:guest_houses,id',
            'room_type_id' => 'required|exists:room_types,id',
            'room_number' => 'required|string|max:50',
            'floor' => 'nullable|string|max:50',
            'tariff' => 'nullable|numeric|min:0',
            'max_occupancy' => 'required|integer|min:1|max:20',
            'facilities' => 'nullable|array',
            'is_active' => 'required|boolean',
            'publication_status' => 'required|in:draft,published',
            'maintenance_status' => 'required|in:operational,under_maintenance,cleaning_in_progress',
            'notes' => 'nullable|string',
        ]);

        // Duplicate room check within same guest house
        $exists = Room::where('guest_house_id', $validated['guest_house_id'])
            ->where('room_number', $validated['room_number'])
            ->where('id', '!=', $room->id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['room_number' => "Room number '{$validated['room_number']}' already exists in this guest house."])->withInput();
        }

        $before = $room->toArray();
        $room->update($validated);

        $this->auditService->log(Auth::user(), 'Updated Room', 'Rooms', Room::class, $room->id, $before, $room->toArray());

        return redirect()->route('masters.rooms.index')->with('success', "Room '{$room->room_number}' updated successfully.");
    }

    public function toggleMaintenance(Request $request, Room $room)
    {
        $request->validate(['status' => 'required|in:operational,under_maintenance,cleaning_in_progress']);

        $before = ['maintenance_status' => $room->maintenance_status];
        $room->maintenance_status = $request->status;
        $room->save();

        $this->auditService->log(Auth::user(), 'Updated Room Maintenance Status', 'Rooms', Room::class, $room->id, $before, ['maintenance_status' => $request->status]);

        return back()->with('success', "Room '{$room->room_number}' status set to '{$request->status}'.");
    }
}

