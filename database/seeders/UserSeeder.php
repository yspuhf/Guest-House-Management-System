<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'id' => 1,
                'organization_unit_id' => 5,
                'employee_id' => 'EMP-001',
                'name' => 'Dr. Rajesh Sharma',
                'email' => 'admin@yspuniversity.ac.in',
                'password' => Hash::make('password123'),
                'designation' => 'System Administrator & Incharge IT',
                'department' => 'Computer Centre',
                'phone' => '9816011111',
                'primary_role' => 'system_admin',
            ],
            [
                'id' => 2,
                'organization_unit_id' => 5,
                'employee_id' => 'EMP-002',
                'name' => 'Dr. Dev Raj',
                'email' => 'estate.officer@yspuniversity.ac.in',
                'password' => Hash::make('password123'),
                'designation' => 'Estate Officer (Handles All Sets except VVIP)',
                'department' => 'Estate Office',
                'phone' => '9816022222',
                'primary_role' => 'estate_office',
            ],
            [
                'id' => 3,
                'organization_unit_id' => 5,
                'employee_id' => 'EMP-003',
                'name' => 'Secretary to Vice-Chancellor',
                'email' => 'vc.secretariat@yspuniversity.ac.in',
                'password' => Hash::make('password123'),
                'designation' => 'Secretary to VC (Handles VVIP Requests Only)',
                'department' => 'Vice-Chancellor Secretariat',
                'phone' => '9816099999',
                'primary_role' => 'vc_secretariat',
            ],
            [
                'id' => 4,
                'organization_unit_id' => 5,
                'employee_id' => 'EMP-004',
                'name' => 'Sh. Anil Kumar',
                'email' => 'incharge@yspuniversity.ac.in',
                'password' => Hash::make('password123'),
                'designation' => 'Guest House Supervisor / Incharge',
                'department' => 'VIP Guest House Desk',
                'phone' => '9816033333',
                'primary_role' => 'guest_house_incharge',
            ],
            [
                'id' => 4,
                'organization_unit_id' => 1,
                'employee_id' => 'EMP-004',
                'name' => 'Dr. Meenakshi Thakur',
                'email' => 'verifier@yspuniversity.ac.in',
                'password' => Hash::make('password123'),
                'designation' => 'Associate Professor & Verifier',
                'department' => 'Department of Fruit Science',
                'phone' => '9816044444',
                'primary_role' => 'verifier',
            ],
            [
                'id' => 5,
                'organization_unit_id' => 1,
                'employee_id' => 'EMP-005',
                'name' => 'Dr. Sanjeev Chauhan',
                'email' => 'approver@yspuniversity.ac.in',
                'password' => Hash::make('password123'),
                'designation' => 'Dean & Approving Authority',
                'department' => 'College of Horticulture',
                'phone' => '9816055555',
                'primary_role' => 'approver',
            ],
            [
                'id' => 6,
                'organization_unit_id' => 2,
                'employee_id' => 'EMP-006',
                'name' => 'Dr. Vikas Sharma',
                'email' => 'employee@yspuniversity.ac.in',
                'password' => Hash::make('password123'),
                'designation' => 'Assistant Professor / Applicant',
                'department' => 'Department of Silviculture',
                'phone' => '9816066666',
                'primary_role' => 'employee',
            ],
            [
                'id' => 7,
                'organization_unit_id' => 6,
                'employee_id' => 'EMP-007',
                'name' => 'Sh. Ramesh Chand',
                'email' => 'finance@yspuniversity.ac.in',
                'password' => Hash::make('password123'),
                'designation' => 'Section Officer (Cash & Accounts)',
                'department' => 'Comptroller Office',
                'phone' => '9816077777',
                'primary_role' => 'accounts_finance',
            ],
            [
                'id' => 8,
                'organization_unit_id' => 6,
                'employee_id' => 'EMP-008',
                'name' => 'Dr. Neena Kumari',
                'email' => 'auditor@yspuniversity.ac.in',
                'password' => Hash::make('password123'),
                'designation' => 'Internal Auditor',
                'department' => 'Audit Cell',
                'phone' => '9816088888',
                'primary_role' => 'report_auditor',
            ],
        ];

        foreach ($users as $data) {
            $user = User::updateOrCreate(['email' => $data['email']], $data);
            $role = Role::where('name', $data['primary_role'])->first();
            if ($role) {
                $user->roles()->sync([$role->id]);
            }
        }
    }
}

