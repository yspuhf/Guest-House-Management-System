<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            OrganizationUnitSeeder::class,
            RolePermissionSeeder::class,
            UserSeeder::class,
            GuestHouseSeeder::class,
            RoomTypeAndRoomSeeder::class,
            GuestTypeAndTariffSeeder::class,
            TaxAndExpenditureHeadSeeder::class,
            SystemSettingSeeder::class,
            ApprovalWorkflowSeeder::class,
            EmailTemplateSeeder::class,
        ]);
    }
}

