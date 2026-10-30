<?php

namespace App\Http\Controllers;

use App\Models\GuestHouse;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GuestHouseController extends Controller
{
    protected AuditService $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    public function index()
    {
        $guestHouses = GuestHouse::with(['incharge', 'rooms'])->get();
        return view('masters.guesthouses.index', compact('guestHouses'));
    }

    public function create()
    {
        $incharges = User::whereIn('primary_role', ['guest_house_incharge', 'guest_house_admin', 'system_admin'])->get();
        return view('masters.guesthouses.create', compact('incharges'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:guest_houses,code',
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'address' => 'nullable|string',
            'eligibility_rules' => 'nullable|string',
            'assigned_incharge_id' => 'nullable|exists:users,id',
            'contact_person' => 'nullable|string|max:100',
            'contact_phone' => 'nullable|string|max:50',
            'contact_email' => 'nullable|email|max:150',
            'status' => 'required|in:draft,published,deactivated',
        ]);

        $validated['published_at'] = ($validated['status'] === 'published') ? now() : null;

        $guestHouse = GuestHouse::create($validated);

        $this->auditService->log(Auth::user(), 'Created Guest House', 'Guest House', GuestHouse::class, $guestHouse->id, null, $guestHouse->toArray());

        return redirect()->route('masters.guesthouses.index')->with('success', "Guest House '{$guestHouse->name}' successfully created.");
    }

    public function show(GuestHouse $guesthouse)
    {
        $guesthouse->load(['incharge', 'rooms.roomType', 'roomBlocks']);
        return view('masters.guesthouses.show', compact('guesthouse'));
    }

    public function edit(GuestHouse $guesthouse)
    {
        $incharges = User::whereIn('primary_role', ['guest_house_incharge', 'guest_house_admin', 'system_admin'])->get();
        return view('masters.guesthouses.edit', compact('guesthouse', 'incharges'));
    }

    public function update(Request $request, GuestHouse $guesthouse)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'address' => 'nullable|string',
            'eligibility_rules' => 'nullable|string',
            'assigned_incharge_id' => 'nullable|exists:users,id',
            'contact_person' => 'nullable|string|max:100',
            'contact_phone' => 'nullable|string|max:50',
            'contact_email' => 'nullable|email|max:150',
            'status' => 'required|in:draft,published,deactivated',
            'is_active' => 'required|boolean',
        ]);

        $before = $guesthouse->toArray();

        if ($validated['status'] === 'published' && !$guesthouse->published_at) {
            $validated['published_at'] = now();
        }

        $guesthouse->update($validated);

        $this->auditService->log(Auth::user(), 'Updated Guest House', 'Guest House', GuestHouse::class, $guesthouse->id, $before, $guesthouse->toArray());

        return redirect()->route('masters.guesthouses.index')->with('success', "Guest House '{$guesthouse->name}' updated successfully.");
    }

    public function togglePublish(GuestHouse $guesthouse)
    {
        $before = ['status' => $guesthouse->status];
        $newStatus = ($guesthouse->status === 'published') ? 'draft' : 'published';
        $guesthouse->status = $newStatus;
        if ($newStatus === 'published' && !$guesthouse->published_at) {
            $guesthouse->published_at = now();
        }
        $guesthouse->save();

        $this->auditService->log(Auth::user(), 'Toggled Guest House Publication', 'Guest House', GuestHouse::class, $guesthouse->id, $before, ['status' => $newStatus]);

        return back()->with('success', "Guest House status changed to {$newStatus}.");
    }
}

