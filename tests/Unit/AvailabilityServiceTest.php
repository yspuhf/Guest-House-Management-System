<?php

namespace Tests\Unit;

use App\Models\Booking;
use App\Models\BookingRoom;
use App\Models\GuestHouse;
use App\Models\Room;
use App\Models\RoomBlock;
use App\Models\RoomType;
use App\Models\User;
use App\Services\AvailabilityService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AvailabilityServiceTest extends TestCase
{
    use RefreshDatabase;

    protected AvailabilityService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AvailabilityService();
    }

    public function test_room_is_available_when_no_conflicts_exist(): void
    {
        $gh = GuestHouse::create([
            'code' => 'TEST-GH',
            'name' => 'Test Guest House',
            'status' => 'published',
            'is_active' => true,
        ]);

        $rt = RoomType::create([
            'code' => 'DLX',
            'name' => 'Deluxe',
            'default_tariff' => 1000.00,
            'standard_capacity' => 2,
        ]);

        $room = Room::create([
            'guest_house_id' => $gh->id,
            'room_type_id' => $rt->id,
            'room_number' => '101',
            'is_active' => true,
            'publication_status' => 'published',
            'maintenance_status' => 'operational',
        ]);

        $available = $this->service->isRoomAvailable($room->id, '2026-11-01', '2026-11-03');
        $this->assertTrue($available);
    }

    public function test_departure_date_is_not_occupied_night_interval(): void
    {
        // If room is booked Nov 1 to Nov 3 (departure Nov 3), it SHOULD be available for a new stay starting Nov 3 to Nov 5
        $gh = GuestHouse::create(['code' => 'GH-1', 'name' => 'GH 1', 'status' => 'published', 'is_active' => true]);
        $rt = RoomType::create(['code' => 'STD', 'name' => 'Standard', 'default_tariff' => 500.00, 'standard_capacity' => 2]);
        $room = Room::create(['guest_house_id' => $gh->id, 'room_type_id' => $rt->id, 'room_number' => '102', 'is_active' => true, 'publication_status' => 'published', 'maintenance_status' => 'operational']);

        $user = User::create(['name' => 'User 1', 'email' => 'u1@test.com', 'password' => 'secret', 'primary_role' => 'employee']);

        $booking = Booking::create([
            'booking_reference' => 'UGH-TEST-01',
            'applicant_id' => $user->id,
            'submitted_by_id' => $user->id,
            'guest_house_id' => $gh->id,
            'room_type_id' => $rt->id,
            'guest_type_id' => 1,
            'purpose' => 'Test',
            'arrival_date' => '2026-11-01',
            'departure_date' => '2026-11-03',
            'status' => 'Room Allocated',
        ]);

        BookingRoom::create([
            'booking_id' => $booking->id,
            'room_id' => $room->id,
            'allocated_by_id' => $user->id,
            'status' => 'Allocated',
        ]);

        // Nov 3 to Nov 5 should be AVAILABLE because Nov 3 is departure day (interval [check_in, check_out))
        $isAvailableOnDepartureDay = $this->service->isRoomAvailable($room->id, '2026-11-03', '2026-11-05');
        $this->assertTrue($isAvailableOnDepartureDay);

        // Nov 2 to Nov 4 should be UNAVAILABLE because of overlap with Nov 1 - Nov 3
        $isAvailableDuringOverlap = $this->service->isRoomAvailable($room->id, '2026-11-02', '2026-11-04');
        $this->assertFalse($isAvailableDuringOverlap);
    }

    public function test_maintenance_blocks_make_room_unavailable(): void
    {
        $gh = GuestHouse::create(['code' => 'GH-2', 'name' => 'GH 2', 'status' => 'published', 'is_active' => true]);
        $rt = RoomType::create(['code' => 'VIP', 'name' => 'VIP', 'default_tariff' => 2000.00, 'standard_capacity' => 2]);
        $room = Room::create(['guest_house_id' => $gh->id, 'room_type_id' => $rt->id, 'room_number' => '103', 'is_active' => true, 'publication_status' => 'published', 'maintenance_status' => 'operational']);
        $user = User::create(['name' => 'Incharge', 'email' => 'inc@test.com', 'password' => 'secret', 'primary_role' => 'guest_house_incharge']);

        RoomBlock::create([
            'room_id' => $room->id,
            'guest_house_id' => $gh->id,
            'start_date' => '2026-11-10',
            'end_date' => '2026-11-15',
            'block_type' => 'maintenance',
            'reason' => 'Painting & Plumbing',
            'blocked_by_id' => $user->id,
            'is_active' => true,
        ]);

        $available = $this->service->isRoomAvailable($room->id, '2026-11-11', '2026-11-13');
        $this->assertFalse($available);
    }
}

