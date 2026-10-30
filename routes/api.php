<?php

use App\Models\Room;
use App\Services\AvailabilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/availability/check', function (Request $request, AvailabilityService $availabilityService) {
    $guestHouseId = $request->query('guest_house_id');
    $roomTypeId = $request->query('room_type_id');
    $checkIn = $request->query('arrival_date');
    $checkOut = $request->query('departure_date');

    if (!$guestHouseId || !$checkIn || !$checkOut) {
        return response()->json(['error' => 'Missing required query parameters'], 422);
    }

    $rooms = $availabilityService->findAvailableRooms((int)$guestHouseId, $roomTypeId ? (int)$roomTypeId : null, $checkIn, $checkOut);

    return response()->json([
        'available_count' => $rooms->count(),
        'rooms' => $rooms->map(fn($r) => [
            'id' => $r->id,
            'room_number' => $r->room_number,
            'floor' => $r->floor,
            'tariff' => $r->tariff ?? $r->roomType->default_tariff,
            'type' => $r->roomType->name,
        ]),
    ]);
});

