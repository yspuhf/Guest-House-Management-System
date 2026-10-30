<?php

namespace Tests\Unit;

use App\Models\ApprovalStep;
use App\Models\ApprovalWorkflow;
use App\Models\Booking;
use App\Models\GuestHouse;
use App\Models\GuestType;
use App\Models\RoomType;
use App\Models\User;
use App\Services\AuditService;
use App\Services\BookingWorkflowService;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected BookingWorkflowService $workflowService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->workflowService = new BookingWorkflowService(new NotificationService(), new AuditService());
    }

    public function test_submission_advances_draft_to_verification(): void
    {
        $user = User::create(['name' => 'Faculty Applicant', 'email' => 'fac@test.com', 'password' => 'secret', 'primary_role' => 'employee']);
        $gh = GuestHouse::create(['code' => 'GH-W1', 'name' => 'GH W1', 'status' => 'published', 'is_active' => true]);
        $rt = RoomType::create(['code' => 'RT-W1', 'name' => 'RT W1', 'default_tariff' => 1000.00, 'standard_capacity' => 2]);
        $gt = GuestType::create(['code' => 'GT-W1', 'name' => 'Official', 'priority_order' => 1]);

        $booking = Booking::create([
            'booking_reference' => 'UGH-WF-01',
            'applicant_id' => $user->id,
            'submitted_by_id' => $user->id,
            'guest_house_id' => $gh->id,
            'room_type_id' => $rt->id,
            'guest_type_id' => $gt->id,
            'purpose' => 'Viva Examiner',
            'arrival_date' => '2026-11-10',
            'departure_date' => '2026-11-12',
            'status' => 'Draft',
        ]);

        $updated = $this->workflowService->submitBooking($booking, $user, 'Initial submission');

        $this->assertEquals('Verification Pending', $updated->status);
        $this->assertDatabaseHas('booking_status_history', [
            'booking_id' => $booking->id,
            'from_status' => 'Draft',
            'to_status' => 'Verification Pending',
            'action' => 'Submitted',
        ]);
    }

    public function test_rejection_requires_mandatory_remarks(): void
    {
        $user = User::create(['name' => 'Dean Approver', 'email' => 'dean@test.com', 'password' => 'secret', 'primary_role' => 'approver']);
        $gh = GuestHouse::create(['code' => 'GH-W2', 'name' => 'GH W2', 'status' => 'published', 'is_active' => true]);
        $rt = RoomType::create(['code' => 'RT-W2', 'name' => 'RT W2', 'default_tariff' => 1000.00, 'standard_capacity' => 2]);
        $gt = GuestType::create(['code' => 'GT-W2', 'name' => 'Official', 'priority_order' => 1]);

        $booking = Booking::create([
            'booking_reference' => 'UGH-WF-02',
            'applicant_id' => $user->id,
            'submitted_by_id' => $user->id,
            'guest_house_id' => $gh->id,
            'room_type_id' => $rt->id,
            'guest_type_id' => $gt->id,
            'purpose' => 'Test',
            'arrival_date' => '2026-11-10',
            'departure_date' => '2026-11-12',
            'status' => 'Approval Pending',
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->workflowService->rejectBooking($booking, $user, '   '); // Empty remarks must throw exception
    }

    public function test_return_for_correction_allows_resubmission(): void
    {
        $verifier = User::create(['name' => 'Verifier', 'email' => 'v@test.com', 'password' => 'secret', 'primary_role' => 'verifier']);
        $applicant = User::create(['name' => 'Applicant', 'email' => 'app@test.com', 'password' => 'secret', 'primary_role' => 'employee']);
        $gh = GuestHouse::create(['code' => 'GH-W3', 'name' => 'GH W3', 'status' => 'published', 'is_active' => true]);
        $rt = RoomType::create(['code' => 'RT-W3', 'name' => 'RT W3', 'default_tariff' => 1000.00, 'standard_capacity' => 2]);
        $gt = GuestType::create(['code' => 'GT-W3', 'name' => 'Official', 'priority_order' => 1]);

        $booking = Booking::create([
            'booking_reference' => 'UGH-WF-03',
            'applicant_id' => $applicant->id,
            'submitted_by_id' => $applicant->id,
            'guest_house_id' => $gh->id,
            'room_type_id' => $rt->id,
            'guest_type_id' => $gt->id,
            'purpose' => 'Test',
            'arrival_date' => '2026-11-10',
            'departure_date' => '2026-11-12',
            'status' => 'Verification Pending',
        ]);

        $this->workflowService->returnForCorrection($booking, $verifier, 'Please attach signed invitation letter.');

        $this->assertEquals('Returned for Correction', $booking->fresh()->status);
        $this->assertTrue($booking->fresh()->isReturned());
    }
}

