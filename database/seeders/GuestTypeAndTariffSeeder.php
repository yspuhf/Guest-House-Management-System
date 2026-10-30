<?php

namespace Database\Seeders;

use App\Models\GuestType;
use App\Models\GuestTypeTariff;
use Illuminate\Database\Seeder;

class GuestTypeAndTariffSeeder extends Seeder
{
    public function run(): void
    {
        $guestTypes = [
            ['id' => 1, 'code' => 'CAT-A', 'name' => 'Category A: Official University Dignitary & Examiner', 'description' => 'BoM members, external examiners, UGC/ICAR peer audit teams', 'priority_order' => 1],
            ['id' => 2, 'code' => 'CAT-B', 'name' => 'Category B: Other University / Govt Official on Duty', 'description' => 'Officials from other universities and govt departments on duty', 'priority_order' => 2],
            ['id' => 3, 'code' => 'CAT-C', 'name' => 'Category C: Seminar / Training / Workshop Delegate', 'description' => 'Delegates attending university sponsored events', 'priority_order' => 3],
            ['id' => 4, 'code' => 'CAT-D', 'name' => 'Category D: University Staff Personal / Private Guest', 'description' => 'Family members and personal guests of university staff', 'priority_order' => 4],
        ];

        foreach ($guestTypes as $gt) {
            GuestType::updateOrCreate(['code' => $gt['code']], $gt);
        }

        $matrix = [
            // Cat A
            ['guest_type_id' => 1, 'room_type_id' => 1, 'rate_per_night' => 1000.00],
            ['guest_type_id' => 1, 'room_type_id' => 2, 'rate_per_night' => 600.00],
            ['guest_type_id' => 1, 'room_type_id' => 3, 'rate_per_night' => 300.00],
            ['guest_type_id' => 1, 'room_type_id' => 4, 'rate_per_night' => 200.00],
            ['guest_type_id' => 1, 'room_type_id' => 5, 'rate_per_night' => 100.00],
            // Cat B
            ['guest_type_id' => 2, 'room_type_id' => 1, 'rate_per_night' => 1500.00],
            ['guest_type_id' => 2, 'room_type_id' => 2, 'rate_per_night' => 1000.00],
            ['guest_type_id' => 2, 'room_type_id' => 3, 'rate_per_night' => 500.00],
            ['guest_type_id' => 2, 'room_type_id' => 4, 'rate_per_night' => 350.00],
            ['guest_type_id' => 2, 'room_type_id' => 5, 'rate_per_night' => 150.00],
            // Cat C
            ['guest_type_id' => 3, 'room_type_id' => 1, 'rate_per_night' => 2000.00],
            ['guest_type_id' => 3, 'room_type_id' => 2, 'rate_per_night' => 1200.00],
            ['guest_type_id' => 3, 'room_type_id' => 3, 'rate_per_night' => 600.00],
            ['guest_type_id' => 3, 'room_type_id' => 4, 'rate_per_night' => 400.00],
            ['guest_type_id' => 3, 'room_type_id' => 5, 'rate_per_night' => 200.00],
            // Cat D
            ['guest_type_id' => 4, 'room_type_id' => 1, 'rate_per_night' => 2500.00],
            ['guest_type_id' => 4, 'room_type_id' => 2, 'rate_per_night' => 1500.00],
            ['guest_type_id' => 4, 'room_type_id' => 3, 'rate_per_night' => 800.00],
            ['guest_type_id' => 4, 'room_type_id' => 4, 'rate_per_night' => 500.00],
            ['guest_type_id' => 4, 'room_type_id' => 5, 'rate_per_night' => 250.00],
        ];

        foreach ($matrix as $m) {
            GuestTypeTariff::updateOrCreate(
                ['guest_type_id' => $m['guest_type_id'], 'room_type_id' => $m['room_type_id']],
                $m
            );
        }
    }
}

