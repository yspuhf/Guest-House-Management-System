<?php

namespace Tests\Unit;

use App\Models\Booking;
use App\Models\GuestHouse;
use App\Models\GuestType;
use App\Models\GuestTypeTariff;
use App\Models\RoomType;
use App\Models\SystemSetting;
use App\Models\Tax;
use App\Models\User;
use App\Services\AuditService;
use App\Services\BillingService;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingServiceTest extends TestCase
{
    use RefreshDatabase;

    protected BillingService $billingService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->billingService = new BillingService(new AuditService(), new NotificationService());
    }

    public function test_category_tariff_calculation_and_night_count(): void
    {
        $gh = GuestHouse::create(['code' => 'GH-1', 'name' => 'Main GH', 'status' => 'published', 'is_active' => true]);
        $rt = RoomType::create(['code' => 'DLX', 'name' => 'Deluxe Room', 'default_tariff' => 1500.00, 'standard_capacity' => 2]);
        $gt = GuestType::create(['code' => 'CAT-A', 'name' => 'Official Guest', 'priority_order' => 1]);

        // Specific category-wise tariff ₹800 instead of ₹1500
        GuestTypeTariff::create([
            'guest_type_id' => $gt->id,
            'room_type_id' => $rt->id,
            'rate_per_night' => 800.00,
            'is_active' => true,
        ]);

        $user = User::create(['name' => 'Applicant', 'email' => 'app@test.com', 'password' => 'secret', 'primary_role' => 'employee']);

        // 3 Nights stay, 2 rooms
        $booking = Booking::create([
            'booking_reference' => 'UGH-BILL-01',
            'applicant_id' => $user->id,
            'submitted_by_id' => $user->id,
            'guest_house_id' => $gh->id,
            'room_type_id' => $rt->id,
            'guest_type_id' => $gt->id,
            'purpose' => 'Evaluation Meeting',
            'arrival_date' => '2026-11-01',
            'departure_date' => '2026-11-04',
            'rooms_requested' => 2,
            'total_guests' => 2,
        ]);

        $charges = $this->billingService->computeBookingCharges($booking);

        $this->assertEquals(3, $charges['nights']);
        $this->assertEquals(800.00, $charges['rate_per_night']);
        // 3 nights * 2 rooms * ₹800 = ₹4800
        $this->assertEquals(4800.00, $charges['room_charges']);
    }

    public function test_tax_calculation_with_decimal_safety(): void
    {
        $gh = GuestHouse::create(['code' => 'GH-TAX', 'name' => 'Tax GH', 'status' => 'published', 'is_active' => true]);
        $rt = RoomType::create(['code' => 'SUITE', 'name' => 'Suite', 'default_tariff' => 2000.00, 'standard_capacity' => 2]);
        $gt = GuestType::create(['code' => 'CAT-D', 'name' => 'Private Guest', 'priority_order' => 4]);

        Tax::create(['name' => 'CGST', 'code' => 'CGST-6', 'calculation_method' => 'percentage', 'rate' => 6.00, 'is_active' => true]);
        Tax::create(['name' => 'SGST', 'code' => 'SGST-6', 'calculation_method' => 'percentage', 'rate' => 6.00, 'is_active' => true]);

        $user = User::create(['name' => 'Applicant', 'email' => 'app2@test.com', 'password' => 'secret', 'primary_role' => 'employee']);

        // 1 night, 1 room @ ₹2000. 12% total tax = ₹240
        $booking = Booking::create([
            'booking_reference' => 'UGH-BILL-02',
            'applicant_id' => $user->id,
            'submitted_by_id' => $user->id,
            'guest_house_id' => $gh->id,
            'room_type_id' => $rt->id,
            'guest_type_id' => $gt->id,
            'purpose' => 'Personal',
            'arrival_date' => '2026-11-01',
            'departure_date' => '2026-11-02',
            'rooms_requested' => 1,
            'total_guests' => 1,
        ]);

        $charges = $this->billingService->computeBookingCharges($booking);

        $this->assertEquals(2000.00, $charges['room_charges']);
        $this->assertEquals(240.00, $charges['tax_amount']);
        $this->assertEquals(2240.00, $charges['grand_total']);
    }

    public function test_payment_stage_enforcement(): void
    {
        SystemSetting::set('payment_stage', 'approval');

        $user = User::create(['name' => 'User', 'email' => 'u@test.com', 'password' => 'secret', 'primary_role' => 'employee']);
        $booking = new Booking(['payment_status' => 'unpaid']);

        // When payment stage is 'approval', allocating a room should fail if payment_status is not paid
        $valid = $this->billingService->validatePaymentStage($booking, 'allocate_room');
        $this->assertFalse($valid);

        $booking->payment_status = 'paid';
        $validPaid = $this->billingService->validatePaymentStage($booking, 'allocate_room');
        $this->assertTrue($validPaid);
    }
}

