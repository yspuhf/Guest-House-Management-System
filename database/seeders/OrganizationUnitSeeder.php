<?php

namespace Database\Seeders;

use App\Models\OrganizationUnit;
use Illuminate\Database\Seeder;

class OrganizationUnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            ['id' => 1, 'code' => 'COH', 'name' => 'College of Horticulture', 'type' => 'College', 'contact_email' => 'dean.coh@yspuniversity.ac.in', 'contact_phone' => '01792-252233'],
            ['id' => 2, 'code' => 'COF', 'name' => 'College of Forestry', 'type' => 'College', 'contact_email' => 'dean.cof@yspuniversity.ac.in', 'contact_phone' => '01792-252244'],
            ['id' => 3, 'code' => 'DOR', 'name' => 'Directorate of Research', 'type' => 'Directorate', 'contact_email' => 'dr@yspuniversity.ac.in', 'contact_phone' => '01792-252255'],
            ['id' => 4, 'code' => 'DEE', 'name' => 'Directorate of Extension Education', 'type' => 'Directorate', 'contact_email' => 'dee@yspuniversity.ac.in', 'contact_phone' => '01792-252266'],
            ['id' => 5, 'code' => 'REG', 'name' => 'Registrar Office & Administration', 'type' => 'Administrative Office', 'contact_email' => 'registrar@yspuniversity.ac.in', 'contact_phone' => '01792-252211'],
            ['id' => 6, 'code' => 'COMP', 'name' => 'Comptroller & Finance Division', 'type' => 'Administrative Office', 'contact_email' => 'comptroller@yspuniversity.ac.in', 'contact_phone' => '01792-252222'],
        ];

        foreach ($units as $u) {
            OrganizationUnit::updateOrCreate(['code' => $u['code']], $u);
        }
    }
}

