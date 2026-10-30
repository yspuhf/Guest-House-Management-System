<?php

namespace Database\Seeders;

use App\Models\ExpenditureHead;
use App\Models\Tax;
use Illuminate\Database\Seeder;

class TaxAndExpenditureHeadSeeder extends Seeder
{
    public function run(): void
    {
        $taxes = [
            ['id' => 1, 'name' => 'GST Exempt (Official Tariffs <= ₹1000)', 'code' => 'GST-EXEMPT', 'calculation_method' => 'percentage', 'rate' => 0.00, 'description' => 'Statutory exemption'],
            ['id' => 2, 'name' => 'CGST (6%)', 'code' => 'CGST-6', 'calculation_method' => 'percentage', 'rate' => 6.00, 'description' => 'Central GST on accommodation above ₹1000/night'],
            ['id' => 3, 'name' => 'SGST (6%)', 'code' => 'SGST-6', 'calculation_method' => 'percentage', 'rate' => 6.00, 'description' => 'State GST on accommodation above ₹1000/night'],
        ];

        foreach ($taxes as $t) {
            Tax::updateOrCreate(['code' => $t['code']], $t);
        }

        $heads = [
            ['id' => 1, 'code' => 'GUF-01', 'name' => 'General University Administration Fund', 'department' => 'Registrar Office', 'requires_approval_document' => false],
            ['id' => 2, 'code' => 'ICAR-PROJECT-2026', 'name' => 'ICAR All India Coordinated Research Project (AICRP)', 'department' => 'Directorate of Research', 'requires_approval_document' => true],
            ['id' => 3, 'code' => 'DEAN-HORT-EXAM', 'name' => 'College of Horticulture Examination & Viva Head', 'department' => 'College of Horticulture', 'requires_approval_document' => true],
            ['id' => 4, 'code' => 'KISAN-MELA-EXT', 'name' => 'Directorate of Extension Training & Kisan Mela', 'department' => 'Directorate of Extension', 'requires_approval_document' => true],
            ['id' => 5, 'code' => 'SELF-PAID', 'name' => 'Self-Paid by Guest / Sponsoring Faculty', 'department' => 'Not Applicable', 'requires_approval_document' => false],
        ];

        foreach ($heads as $h) {
            ExpenditureHead::updateOrCreate(['code' => $h['code']], $h);
        }
    }
}

