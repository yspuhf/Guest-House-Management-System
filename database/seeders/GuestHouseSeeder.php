<?php

namespace Database\Seeders;

use App\Models\GuestHouse;
use Illuminate\Database\Seeder;

class GuestHouseSeeder extends Seeder
{
    public function run(): void
    {
        $houses = [
            [
                'id' => 1,
                'code' => 'VIP-GH',
                'name' => 'VIP Guest House',
                'description' => 'Main university executive guest house reserved for dignitaries, examiners, BoM members and official guests.',
                'address' => 'Near Vice Chancellor Secretariat, Main Campus, Nauni Solan',
                'eligibility_rules' => 'Vice Chancellor approval or Dean recommendation required. Priority for State Guests and ICAR officials.',
                'assigned_incharge_id' => 3,
                'contact_person' => 'Sh. Anil Kumar',
                'contact_phone' => '01792-252301',
                'contact_email' => 'vip.guesthouse@yspuniversity.ac.in',
                'status' => 'published',
                'is_active' => true,
                'published_at' => now(),
            ],
            [
                'id' => 2,
                'code' => 'FGH',
                'name' => 'Faculty & International Guest House',
                'description' => 'Comfortable lodging for visiting scientists, guest faculty, conference delegates and scholars.',
                'address' => 'East Campus, Adjacent to Forestry Complex, Nauni Solan',
                'eligibility_rules' => 'Open to university faculty, visiting research fellows and sponsored conference delegates.',
                'assigned_incharge_id' => 3,
                'contact_person' => 'Sh. Anil Kumar',
                'contact_phone' => '01792-252302',
                'contact_email' => 'fgh@yspuniversity.ac.in',
                'status' => 'published',
                'is_active' => true,
                'published_at' => now(),
            ],
            [
                'id' => 3,
                'code' => 'KBH',
                'name' => 'Kisan Bhawan / Farmers Hostel',
                'description' => 'Spacious residential facility for farmers participating in extension training programs, workshops and horti-fairs.',
                'address' => 'Lower Campus, Directorate of Extension Education, Nauni Solan',
                'eligibility_rules' => 'Farmers, trainees, extension participants and student groups.',
                'assigned_incharge_id' => 3,
                'contact_person' => 'Sh. Anil Kumar',
                'contact_phone' => '01792-252303',
                'contact_email' => 'kisan.bhawan@yspuniversity.ac.in',
                'status' => 'draft',
                'is_active' => false,
                'published_at' => now(),
            ],
            [
                'id' => 4,
                'code' => 'ALUMNI-RH',
                'name' => 'Alumni Rest House (Annex)',
                'description' => 'Proposed annex facility for returning alumni and short-stay visitors.',
                'address' => 'West Ridge, Campus Sector 4',
                'eligibility_rules' => 'Alumni Association registered members.',
                'assigned_incharge_id' => 3,
                'contact_person' => 'Sh. Anil Kumar',
                'contact_phone' => '01792-252304',
                'contact_email' => 'alumni.rh@yspuniversity.ac.in',
                'status' => 'draft',
                'is_active' => true,
                'published_at' => null,
            ],
        ];

        foreach ($houses as $h) {
            GuestHouse::updateOrCreate(['code' => $h['code']], $h);
        }
    }
}

