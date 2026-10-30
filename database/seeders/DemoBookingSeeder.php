<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\BookingGuest;
use App\Models\BookingRoom;
use App\Models\BookingStatusHistory;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DemoBookingSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Room Allocated (VIP-101)
        $b1 = Booking::updateOrCreate(
            ['booking_reference' => 'UGH-2026-0001'],
            [
                'applicant_id' => 6,
                'submitted_by_id' => 6,
                'guest_house_id' => 1,
                'room_type_id' => 1,
                'guest_type_id' => 1,
                'expenditure_head_id' => 3,
                'approval_workflow_id' => 1,
                'purpose' => 'External Examiner for PhD Oral Comprehensive Examination in Fruit Science',
                'arrival_date' => Carbon::today()->addDays(2),
                'arrival_time' => '12:00:00',
                'departure_date' => Carbon::today()->addDays(4),
                'departure_time' => '11:00:00',
                'billable_nights' => 2,
                'rooms_requested' => 1,
                'total_guests' => 1,
                'meal_preference' => 'Breakfast',
                'special_requests' => 'Requires early breakfast on day 2. Senior Professor.',
                'accepted_terms' => true,
                'status' => 'Room Allocated',
                'current_step_order' => 2,
                'applied_tariff_per_night' => 1000.00,
                'total_room_charges' => 2000.00,
                'total_tax_amount' => 0.00,
                'grand_total' => 2000.00,
                'balance_amount' => 2000.00,
                'payment_stage_applied' => 'check_out',
                'payment_status' => 'unpaid',
            ]
        );

        BookingGuest::updateOrCreate(
            ['booking_id' => $b1->id, 'is_primary' => true],
            [
                'name' => 'Prof. Arvind K. Joshi',
                'designation' => 'Head of Division (Horticulture), IARI New Delhi',
                'phone' => '9419100001',
                'email' => 'arvind.joshi@iari.res.in',
                'id_document_type' => 'Govt Employee ID',
                'id_document_number' => 'IARI-FAC-409',
                'relationship' => 'Examiner',
            ]
        );

        BookingRoom::updateOrCreate(
            ['booking_id' => $b1->id, 'room_id' => 1],
            [
                'allocated_by_id' => 3,
                'allocated_at' => now(),
                'status' => 'Allocated',
            ]
        );

        BookingStatusHistory::updateOrCreate(
            ['booking_id' => $b1->id, 'action' => 'Room Allocated'],
            [
                'from_status' => 'Approved',
                'to_status' => 'Room Allocated',
                'actor_id' => 3,
                'remarks' => 'VIP Suite 101 allocated for external examiner stay.',
                'workflow_stage' => 'Allocation',
            ]
        );

        // 2. Checked In Active Stay (FGH-201)
        $b2 = Booking::updateOrCreate(
            ['booking_reference' => 'UGH-2026-0002'],
            [
                'applicant_id' => 6,
                'submitted_by_id' => 6,
                'guest_house_id' => 2,
                'room_type_id' => 3,
                'guest_type_id' => 2,
                'expenditure_head_id' => 2,
                'approval_workflow_id' => 1,
                'purpose' => 'Collaborative research meeting on Agro-forestry satellite imagery models',
                'arrival_date' => Carbon::today(),
                'arrival_time' => '11:30:00',
                'departure_date' => Carbon::today()->addDays(2),
                'departure_time' => '11:00:00',
                'billable_nights' => 2,
                'rooms_requested' => 1,
                'total_guests' => 1,
                'meal_preference' => 'All Meals (Breakfast, Lunch, Dinner)',
                'special_requests' => 'Vegetarian meals only',
                'accepted_terms' => true,
                'status' => 'Checked In',
                'current_step_order' => 2,
                'applied_tariff_per_night' => 500.00,
                'total_room_charges' => 1000.00,
                'total_tax_amount' => 0.00,
                'grand_total' => 1000.00,
                'balance_amount' => 1000.00,
                'payment_stage_applied' => 'check_out',
                'payment_status' => 'unpaid',
            ]
        );

        BookingGuest::updateOrCreate(
            ['booking_id' => $b2->id, 'is_primary' => true],
            [
                'name' => 'Dr. Sunita Deshmukh',
                'designation' => 'Scientist-E, Forest Research Institute (FRI) Dehradun',
                'phone' => '9897000002',
                'email' => 'deshmukhs@fri.res.in',
                'id_document_type' => 'Aadhaar Card',
                'id_document_number' => 'XXXX-XXXX-8921',
                'relationship' => 'Collaborator',
            ]
        );

        BookingRoom::updateOrCreate(
            ['booking_id' => $b2->id, 'room_id' => 8],
            [
                'allocated_by_id' => 3,
                'allocated_at' => now()->subDay(),
                'checked_in_at' => now()->subHours(2),
                'status' => 'Occupied',
                'room_condition_on_checkin' => 'Clean, fresh linen provided, keys handed over.',
            ]
        );

        // 3. Verification Pending
        $b3 = Booking::updateOrCreate(
            ['booking_reference' => 'UGH-2026-0003'],
            [
                'applicant_id' => 6,
                'submitted_by_id' => 6,
                'guest_house_id' => 2,
                'room_type_id' => 2,
                'guest_type_id' => 4,
                'expenditure_head_id' => 5,
                'approval_workflow_id' => 1,
                'purpose' => 'Personal family visit during annual university flower show',
                'arrival_date' => Carbon::today()->addDays(5),
                'arrival_time' => '14:00:00',
                'departure_date' => Carbon::today()->addDays(7),
                'departure_time' => '11:00:00',
                'billable_nights' => 2,
                'rooms_requested' => 1,
                'total_guests' => 2,
                'meal_preference' => 'None',
                'special_requests' => 'Require quiet room, parents are elderly',
                'accepted_terms' => true,
                'status' => 'Verification Pending',
                'current_step_order' => 1,
                'applied_tariff_per_night' => 1500.00,
                'total_room_charges' => 3000.00,
                'total_tax_amount' => 360.00,
                'grand_total' => 3360.00,
                'balance_amount' => 3360.00,
                'payment_stage_applied' => 'check_out',
                'payment_status' => 'unpaid',
            ]
        );

        BookingGuest::updateOrCreate(
            ['booking_id' => $b3->id, 'name' => 'Sh. O.P. Sharma'],
            [
                'designation' => 'Retired Govt Officer (Applicant Father)',
                'phone' => '9418012345',
                'email' => 'opsharma@gmail.com',
                'id_document_type' => 'Aadhaar Card',
                'id_document_number' => 'XXXX-XXXX-1122',
                'is_primary' => true,
                'relationship' => 'Parent',
            ]
        );

        // 4. Closed & Fully Settled (Invoice + Receipt)
        $b4 = Booking::updateOrCreate(
            ['booking_reference' => 'UGH-2026-0004'],
            [
                'applicant_id' => 6,
                'submitted_by_id' => 6,
                'guest_house_id' => 2,
                'room_type_id' => 2,
                'guest_type_id' => 3,
                'expenditure_head_id' => 5,
                'approval_workflow_id' => 1,
                'purpose' => 'Keynote speaker for National Symposium on Mountain Agro-ecosystems',
                'arrival_date' => Carbon::today()->subDays(3),
                'arrival_time' => '12:00:00',
                'departure_date' => Carbon::today()->subDay(),
                'departure_time' => '10:30:00',
                'billable_nights' => 2,
                'rooms_requested' => 1,
                'total_guests' => 1,
                'meal_preference' => 'Breakfast & Dinner',
                'accepted_terms' => true,
                'status' => 'Closed',
                'current_step_order' => 2,
                'applied_tariff_per_night' => 1200.00,
                'total_room_charges' => 2400.00,
                'total_tax_amount' => 288.00,
                'total_other_charges' => 300.00,
                'grand_total' => 2988.00,
                'paid_amount' => 2988.00,
                'balance_amount' => 0.00,
                'payment_stage_applied' => 'check_out',
                'payment_status' => 'paid',
            ]
        );

        $inv = Invoice::updateOrCreate(
            ['invoice_number' => 'INV-2026-0001'],
            [
                'booking_id' => $b4->id,
                'invoice_date' => Carbon::today()->subDay(),
                'subtotal' => 2400.00,
                'tax_total' => 288.00,
                'additional_charges' => 300.00,
                'grand_total' => 2988.00,
                'paid_amount' => 2988.00,
                'balance_due' => 0.00,
                'status' => 'Paid',
                'is_finalized' => true,
                'generated_by_id' => 7,
            ]
        );

        InvoiceItem::updateOrCreate(
            ['invoice_id' => $inv->id, 'item_description' => 'Deluxe AC Room (FGH-102) Stay (2 nights @ ₹1200)'],
            ['item_type' => 'room_charge', 'unit_rate' => 1200.00, 'quantity' => 2, 'line_total' => 2400.00]
        );

        InvoiceItem::updateOrCreate(
            ['invoice_id' => $inv->id, 'item_description' => 'CGST 6% on Room Charge'],
            ['item_type' => 'tax', 'unit_rate' => 144.00, 'quantity' => 1, 'line_total' => 144.00, 'tax_id' => 2, 'tax_rate_applied' => 6.00]
        );

        InvoiceItem::updateOrCreate(
            ['invoice_id' => $inv->id, 'item_description' => 'SGST 6% on Room Charge'],
            ['item_type' => 'tax', 'unit_rate' => 144.00, 'quantity' => 1, 'line_total' => 144.00, 'tax_id' => 3, 'tax_rate_applied' => 6.00]
        );

        InvoiceItem::updateOrCreate(
            ['invoice_id' => $inv->id, 'item_description' => 'Special Dining / Dinner supplement charges'],
            ['item_type' => 'meal', 'unit_rate' => 300.00, 'quantity' => 1, 'line_total' => 300.00]
        );

        Payment::updateOrCreate(
            ['receipt_number' => 'REC-2026-0001'],
            [
                'invoice_id' => $inv->id,
                'booking_id' => $b4->id,
                'payment_method' => 'UPI / QR',
                'transaction_reference' => 'UPI/2026/891238910',
                'payment_date' => Carbon::today()->subDay(),
                'amount' => 2988.00,
                'status' => 'Successful',
                'notes' => 'Settled via GooglePay QR at front desk',
                'collected_by_id' => 7,
            ]
        );
    }
}

