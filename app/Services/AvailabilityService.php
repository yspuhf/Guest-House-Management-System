<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingRoom;
use App\Models\GuestHouse;
use App\Models\Room;
use App\Models\RoomBlock;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AvailabilityService
{
    /**
     * Search available rooms for a guest house, room type, and date interval [checkIn, checkOut).
     * Departure day is not counted as an occupied night.
     */
    public function findAvailableRooms(int $guestHouseId, ?int $roomTypeId, string $checkIn, string $checkOut, int $requiredRooms = 1): Collection
    {
        $checkInDate = Carbon::parse($checkIn)->toDateString();
        $checkOutDate = Carbon::parse($checkOut)->toDateString();

        $query = Room::where('guest_house_id', $guestHouseId)
            ->where('is_active', true)
            ->where('publication_status', 'published')
            ->where('maintenance_status', 'operational');

        if ($roomTypeId) {
            $query->where('room_type_id', $roomTypeId);
        }

        // Exclude rooms with maintenance / hold blocks: [start_date, end_date)
        $query->whereDoesntHave('blocks', function ($blockQuery) use ($checkInDate, $checkOutDate) {
            $blockQuery->where('is_active', true)
                ->where('start_date', '<', $checkOutDate)
                ->where('end_date', '>', $checkInDate);
        });

        // Exclude rooms with active/confirmed bookings: [arrival_date, departure_date)
        $query->whereDoesntHave('bookingRooms', function ($brQuery) use ($checkInDate, $checkOutDate) {
            $brQuery->whereIn('status', ['Allocated', 'Occupied'])
                ->whereHas('booking', function ($bQuery) use ($checkInDate, $checkOutDate) {
                    $bQuery->whereNotIn('status', ['Cancelled', 'Rejected', 'Closed', 'Draft', 'Returned for Correction'])
                        ->where('arrival_date', '<', $checkOutDate)
                        ->where('departure_date', '>', $checkInDate);
                });
        });

        return $query->with('roomType')->get();
    }

    /**
     * Check if a specific room is available for the given date range.
     */
    public function isRoomAvailable(int $roomId, string $checkIn, string $checkOut, ?int $ignoreBookingId = null): bool
    {
        $checkInDate = Carbon::parse($checkIn)->toDateString();
        $checkOutDate = Carbon::parse($checkOut)->toDateString();

        $room = Room::find($roomId);
        if (!$room || !$room->is_active || $room->publication_status !== 'published' || $room->maintenance_status !== 'operational') {
            return false;
        }

        // Check blocks
        $hasBlock = RoomBlock::where('room_id', $roomId)
            ->where('is_active', true)
            ->where('start_date', '<', $checkOutDate)
            ->where('end_date', '>', $checkInDate)
            ->exists();

        if ($hasBlock) {
            return false;
        }

        // Check overlapping bookings
        $bookingQuery = BookingRoom::where('room_id', $roomId)
            ->whereIn('status', ['Allocated', 'Occupied'])
            ->whereHas('booking', function ($bQuery) use ($checkInDate, $checkOutDate, $ignoreBookingId) {
                $bQuery->whereNotIn('status', ['Cancelled', 'Rejected', 'Closed', 'Draft', 'Returned for Correction'])
                    ->where('arrival_date', '<', $checkOutDate)
                    ->where('departure_date', '>', $checkInDate);

                if ($ignoreBookingId) {
                    $bQuery->where('id', '!=', $ignoreBookingId);
                }
            });

        return !$bookingQuery->exists();
    }

    /**
     * Concurrency-safe atomic room allocation using row locking.
     */
    public function allocateRoomsSafely(Booking $booking, array $roomIds, int $allocatedById): array
    {
        return DB::transaction(function () use ($booking, $roomIds, $allocatedById) {
            // Lock candidate rooms for update
            $rooms = Room::whereIn('id', $roomIds)->lockForUpdate()->get();

            if ($rooms->count() !== count($roomIds)) {
                throw new \Exception('One or more selected rooms could not be found.');
            }

            $checkIn = $booking->arrival_date->toDateString();
            $checkOut = $booking->departure_date->toDateString();

            foreach ($rooms as $room) {
                if (!$this->isRoomAvailable($room->id, $checkIn, $checkOut, $booking->id)) {
                    throw new \Exception("Room {$room->room_number} is no longer available for the stay duration {$checkIn} to {$checkOut}.");
                }
            }

            // Remove prior allocations if re-allocating
            BookingRoom::where('booking_id', $booking->id)->delete();

            $allocated = [];
            foreach ($rooms as $room) {
                $br = BookingRoom::create([
                    'booking_id' => $booking->id,
                    'room_id' => $room->id,
                    'allocated_by_id' => $allocatedById,
                    'allocated_at' => now(),
                    'status' => 'Allocated',
                ]);
                $allocated[] = $room->room_number;
            }

            return $allocated;
        });
    }

    /**
     * Build availability calendar matrix for a guest house across a date span.
     * Legend states: Available, Booked/Occupied, Unavailable/Maintenance, Pending/Attention.
     */
    public function getCalendarMatrix(int $guestHouseId, Carbon $startDate, int $days = 14): array
    {
        $guestHouse = GuestHouse::with(['rooms.roomType'])->findOrFail($guestHouseId);
        $dates = [];
        for ($i = 0; $i < $days; $i++) {
            $dates[] = $startDate->copy()->addDays($i)->toDateString();
        }

        $endDate = $startDate->copy()->addDays($days)->toDateString();
        $startStr = $startDate->toDateString();

        // Preload blocks and allocations
        $blocks = RoomBlock::where('guest_house_id', $guestHouseId)
            ->where('is_active', true)
            ->where('start_date', '<', $endDate)
            ->where('end_date', '>', $startStr)
            ->get();

        $activeAllocations = BookingRoom::whereHas('room', fn($q) => $q->where('guest_house_id', $guestHouseId))
            ->whereIn('status', ['Allocated', 'Occupied'])
            ->whereHas('booking', function ($bQuery) use ($startStr, $endDate) {
                $bQuery->whereNotIn('status', ['Cancelled', 'Rejected', 'Closed', 'Draft', 'Returned for Correction'])
                    ->where('arrival_date', '<', $endDate)
                    ->where('departure_date', '>', $startStr);
            })
            ->with(['booking.applicant', 'booking.guests'])
            ->get();

        $matrix = [];
        foreach ($guestHouse->rooms as $room) {
            $roomData = [
                'room_id' => $room->id,
                'room_number' => $room->room_number,
                'room_type' => $room->roomType->name,
                'status_daily' => [],
            ];

            foreach ($dates as $date) {
                // Check maintenance
                if ($room->maintenance_status !== 'operational' || !$room->is_active) {
                    $roomData['status_daily'][$date] = [
                        'status' => 'Unavailable/Maintenance',
                        'label' => 'Under Maintenance',
                        'color' => 'secondary',
                    ];
                    continue;
                }

                // Check block
                $matchingBlock = $blocks->first(function ($b) use ($room, $date) {
                    return $b->room_id === $room->id && $date >= $b->start_date && $date < $b->end_date;
                });

                if ($matchingBlock) {
                    $roomData['status_daily'][$date] = [
                        'status' => 'Unavailable/Maintenance',
                        'label' => ucfirst($matchingBlock->block_type),
                        'reason' => $matchingBlock->reason,
                        'color' => 'danger',
                    ];
                    continue;
                }

                // Check allocated or occupied
                $matchingAlloc = $activeAllocations->first(function ($a) use ($room, $date) {
                    if ($a->room_id !== $room->id) return false;
                    $arr = $a->booking->arrival_date->toDateString();
                    $dep = $a->booking->departure_date->toDateString();
                    return $date >= $arr && $date < $dep;
                });

                if ($matchingAlloc) {
                    $status = $matchingAlloc->status === 'Occupied' ? 'Booked/Occupied' : 'Pending/Attention';
                    $roomData['status_daily'][$date] = [
                        'status' => $status,
                        'label' => $matchingAlloc->booking->booking_reference,
                        'guest' => $matchingAlloc->booking->primaryGuest?->name ?? 'Guest',
                        'color' => $matchingAlloc->status === 'Occupied' ? 'primary' : 'warning',
                    ];
                } else {
                    $roomData['status_daily'][$date] = [
                        'status' => 'Available',
                        'label' => 'Available',
                        'color' => 'success',
                    ];
                }
            }

            $matrix[] = $roomData;
        }

        return [
            'guest_house' => $guestHouse,
            'dates' => $dates,
            'matrix' => $matrix,
        ];
    }
}

