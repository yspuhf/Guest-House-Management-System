<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'institution_name', 'value' => 'Dr. Y.S. Parmar University of Horticulture & Forestry, Nauni', 'group' => 'general', 'label' => 'University / Institution Title', 'type' => 'text'],
            ['key' => 'institution_short', 'value' => 'YSP UHF Nauni', 'group' => 'general', 'label' => 'Short Acronym', 'type' => 'text'],
            ['key' => 'currency_symbol', 'value' => '₹', 'group' => 'finance', 'label' => 'Currency Symbol', 'type' => 'text'],
            ['key' => 'payment_stage', 'value' => 'check_out', 'group' => 'finance', 'label' => 'Configured Payment Stage', 'type' => 'select', 'options' => json_encode(['approval', 'check_in', 'check_out'])],
            ['key' => 'check_in_time', 'value' => '12:00', 'group' => 'booking', 'label' => 'Standard Check-in Time', 'type' => 'time'],
            ['key' => 'check_out_time', 'value' => '11:00', 'group' => 'booking', 'label' => 'Standard Check-out Time', 'type' => 'time'],
            ['key' => 'cancellation_cutoff_hours', 'value' => '24', 'group' => 'booking', 'label' => 'Cancellation Cut-off Window (Hours)', 'type' => 'number'],
            ['key' => 'contact_email', 'value' => 'guesthouse@yspuniversity.ac.in', 'group' => 'general', 'label' => 'Master Desk Email', 'type' => 'text'],
            ['key' => 'allow_same_day_booking', 'value' => '1', 'group' => 'booking', 'label' => 'Allow Same-Day Emergency Reservations', 'type' => 'boolean'],
        ];

        foreach ($settings as $s) {
            SystemSetting::updateOrCreate(['key' => $s['key']], $s);
        }
    }
}

