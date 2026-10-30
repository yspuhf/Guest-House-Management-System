<?php

namespace Database\Seeders;

use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Database\Seeder;

class RoomTypeAndRoomSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['id' => 1, 'code' => 'VIP-SUITE', 'name' => 'VIP Presidential Suite', 'description' => 'Luxury suite with living lounge, smart TV, mini-fridge', 'default_tariff' => 2500.00, 'standard_capacity' => 2],
            ['id' => 2, 'code' => 'DLX-AC', 'name' => 'Deluxe AC Double Room', 'description' => 'Air-conditioned room with 2 twin/queen beds, balcony', 'default_tariff' => 1500.00, 'standard_capacity' => 2],
            ['id' => 3, 'code' => 'STD-NON-AC', 'name' => 'Standard Non-AC Double Room', 'description' => 'Hill-climate non-AC double room with geyser', 'default_tariff' => 800.00, 'standard_capacity' => 2],
            ['id' => 4, 'code' => 'STD-SINGLE', 'name' => 'Standard Single Room', 'description' => 'Single occupancy room with study desk', 'default_tariff' => 500.00, 'standard_capacity' => 1],
            ['id' => 5, 'code' => 'DORM-BED', 'name' => 'Dormitory Bed (Shared)', 'description' => 'Clean bed in 6-bed dormitory with locker', 'default_tariff' => 200.00, 'standard_capacity' => 1],
        ];

        foreach ($types as $t) {
            RoomType::updateOrCreate(['code' => $t['code']], $t);
        }

        $rooms = [
            // VIP Guest House
            ['guest_house_id' => 1, 'room_type_id' => 1, 'room_number' => 'VIP-101', 'floor' => 'Ground Floor', 'tariff' => 2500.00, 'max_occupancy' => 2, 'facilities' => ['AC', 'Living Lounge', 'Smart TV', 'Geyser', 'Wi-Fi', 'Refrigerator'], 'maintenance_status' => 'operational'],
            ['guest_house_id' => 1, 'room_type_id' => 1, 'room_number' => 'VIP-102', 'floor' => 'Ground Floor', 'tariff' => 2500.00, 'max_occupancy' => 2, 'facilities' => ['AC', 'Living Lounge', 'Smart TV', 'Geyser', 'Wi-Fi', 'Refrigerator'], 'maintenance_status' => 'operational'],
            ['guest_house_id' => 1, 'room_type_id' => 2, 'room_number' => 'VIP-201', 'floor' => 'First Floor', 'tariff' => 1600.00, 'max_occupancy' => 2, 'facilities' => ['AC', 'LED TV', 'Geyser', 'Wi-Fi', 'Balcony'], 'maintenance_status' => 'operational'],
            ['guest_house_id' => 1, 'room_type_id' => 2, 'room_number' => 'VIP-202', 'floor' => 'First Floor', 'tariff' => 1600.00, 'max_occupancy' => 2, 'facilities' => ['AC', 'LED TV', 'Geyser', 'Wi-Fi', 'Balcony'], 'maintenance_status' => 'operational'],
            ['guest_house_id' => 1, 'room_type_id' => 2, 'room_number' => 'VIP-203', 'floor' => 'First Floor', 'tariff' => 1600.00, 'max_occupancy' => 2, 'facilities' => ['AC', 'LED TV', 'Geyser', 'Wi-Fi'], 'maintenance_status' => 'operational'],

            // Faculty Guest House
            ['guest_house_id' => 2, 'room_type_id' => 2, 'room_number' => 'FGH-101', 'floor' => 'Ground Floor', 'tariff' => 1400.00, 'max_occupancy' => 2, 'facilities' => ['AC', 'TV', 'Geyser', 'Wi-Fi', 'Study Desk'], 'maintenance_status' => 'operational'],
            ['guest_house_id' => 2, 'room_type_id' => 2, 'room_number' => 'FGH-102', 'floor' => 'Ground Floor', 'tariff' => 1400.00, 'max_occupancy' => 2, 'facilities' => ['AC', 'TV', 'Geyser', 'Wi-Fi', 'Study Desk'], 'maintenance_status' => 'operational'],
            ['guest_house_id' => 2, 'room_type_id' => 3, 'room_number' => 'FGH-201', 'floor' => 'First Floor', 'tariff' => 800.00, 'max_occupancy' => 2, 'facilities' => ['Geyser', 'Wi-Fi', 'Study Table', 'Mountain View'], 'maintenance_status' => 'operational'],
            ['guest_house_id' => 2, 'room_type_id' => 3, 'room_number' => 'FGH-202', 'floor' => 'First Floor', 'tariff' => 800.00, 'max_occupancy' => 2, 'facilities' => ['Geyser', 'Wi-Fi', 'Study Table'], 'maintenance_status' => 'operational'],
            ['guest_house_id' => 2, 'room_type_id' => 4, 'room_number' => 'FGH-203', 'floor' => 'First Floor', 'tariff' => 500.00, 'max_occupancy' => 1, 'facilities' => ['Geyser', 'Wi-Fi', 'Study Table', 'Single Bed'], 'maintenance_status' => 'operational'],
            ['guest_house_id' => 2, 'room_type_id' => 4, 'room_number' => 'FGH-204', 'floor' => 'First Floor', 'tariff' => 500.00, 'max_occupancy' => 1, 'facilities' => ['Geyser', 'Wi-Fi', 'Study Table', 'Single Bed'], 'maintenance_status' => 'under_maintenance'],

            // Kisan Bhawan
            ['guest_house_id' => 3, 'room_type_id' => 3, 'room_number' => 'KB-101', 'floor' => 'Ground Floor', 'tariff' => 600.00, 'max_occupancy' => 2, 'facilities' => ['Attached Bath', 'Solar Geyser', 'Ceiling Fan'], 'maintenance_status' => 'operational'],
            ['guest_house_id' => 3, 'room_type_id' => 3, 'room_number' => 'KB-102', 'floor' => 'Ground Floor', 'tariff' => 600.00, 'max_occupancy' => 2, 'facilities' => ['Attached Bath', 'Solar Geyser', 'Ceiling Fan'], 'maintenance_status' => 'operational'],
            ['guest_house_id' => 3, 'room_type_id' => 5, 'room_number' => 'KB-DORM-A', 'floor' => 'First Floor', 'tariff' => 200.00, 'max_occupancy' => 6, 'facilities' => ['Solar Water', 'Common Bathroom', 'Lockers'], 'maintenance_status' => 'operational'],
            ['guest_house_id' => 3, 'room_type_id' => 5, 'room_number' => 'KB-DORM-B', 'floor' => 'First Floor', 'tariff' => 200.00, 'max_occupancy' => 6, 'facilities' => ['Solar Water', 'Common Bathroom', 'Lockers'], 'maintenance_status' => 'operational'],
        ];

        foreach ($rooms as $r) {
            Room::updateOrCreate(
                ['guest_house_id' => $r['guest_house_id'], 'room_number' => $r['room_number']],
                $r
            );
        }
    }
}

