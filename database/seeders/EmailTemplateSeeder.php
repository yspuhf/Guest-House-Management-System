<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'event_key' => 'booking_submitted',
                'title' => 'Booking Request Submitted',
                'subject' => 'Booking Request Submitted - Reference: {BOOKING_REF}',
                'body_template' => "Dear {APPLICANT_NAME},\n\nYour guest house reservation request for {GUEST_NAME} has been successfully submitted.\nReference Number: {BOOKING_REF}\nGuest House: {GUEST_HOUSE}\nStay Period: {ARRIVAL_DATE} to {DEPARTURE_DATE}\n\nYour request has been routed to the departmental verifier.\n\nRegards,\n{INSTITUTION_NAME}",
            ],
            [
                'event_key' => 'booking_verified',
                'title' => 'Booking Request Verified',
                'subject' => 'Booking Verified - Reference: {BOOKING_REF}',
                'body_template' => "Dear {APPLICANT_NAME},\n\nYour booking request {BOOKING_REF} has been verified and forwarded to the approving authority.\n\nVerifier Remarks: {REMARKS}\n\nRegards,\n{INSTITUTION_NAME}",
            ],
            [
                'event_key' => 'booking_approved',
                'title' => 'Booking Request Approved',
                'subject' => 'Booking Approved - Reference: {BOOKING_REF}',
                'body_template' => "Dear {APPLICANT_NAME},\n\nCongratulations! Your reservation request {BOOKING_REF} has been approved.\nThe Guest House Incharge will now allocate available rooms.\n\nAuthority Remarks: {REMARKS}\n\nRegards,\n{INSTITUTION_NAME}",
            ],
            [
                'event_key' => 'booking_rejected',
                'title' => 'Booking Request Rejected',
                'subject' => 'Booking Request Rejected - Reference: {BOOKING_REF}',
                'body_template' => "Dear {APPLICANT_NAME},\n\nYour guest house booking request {BOOKING_REF} could not be approved.\n\nReason / Mandatory Remarks:\n{REMARKS}\n\nRegards,\n{INSTITUTION_NAME}",
            ],
            [
                'event_key' => 'booking_returned',
                'title' => 'Booking Request Returned for Correction',
                'subject' => 'Booking Returned for Correction - Reference: {BOOKING_REF}',
                'body_template' => "Dear {APPLICANT_NAME},\n\nYour booking request {BOOKING_REF} requires corrections.\n\nRemarks:\n{REMARKS}\n\nPlease log in to the portal and re-submit.\n\nRegards,\n{INSTITUTION_NAME}",
            ],
            [
                'event_key' => 'room_allocated',
                'title' => 'Rooms Allocated to Booking',
                'subject' => 'Room Allocation Confirmed - Reference: {BOOKING_REF}',
                'body_template' => "Dear {APPLICANT_NAME},\n\nRooms have been officially assigned for your guest {GUEST_NAME}.\nReference: {BOOKING_REF}\nGuest House: {GUEST_HOUSE}\nAllocated Room(s): {ROOM_NUMBERS}\nDates: {ARRIVAL_DATE} to {DEPARTURE_DATE}\n\nRegards,\n{INSTITUTION_NAME}",
            ],
            [
                'event_key' => 'guest_checked_in',
                'title' => 'Guest Checked In',
                'subject' => 'Guest Arrival Recorded - Reference: {BOOKING_REF}',
                'body_template' => "Dear {APPLICANT_NAME},\n\nGuest {GUEST_NAME} has checked in to {GUEST_HOUSE}, Room(s): {ROOM_NUMBERS}.\n\nRegards,\n{INSTITUTION_NAME}",
            ],
            [
                'event_key' => 'invoice_settled',
                'title' => 'Invoice Settled & Receipt Issued',
                'subject' => 'Guest House Bill Settled - Receipt: {RECEIPT_NUM}',
                'body_template' => "Dear {APPLICANT_NAME} / {GUEST_NAME},\n\nYour bill for reservation {BOOKING_REF} has been fully settled.\nReceipt Number: {RECEIPT_NUM}\nAmount Paid: {CURRENCY} {AMOUNT_PAID}\nPayment Method: {PAYMENT_METHOD}\n\nRegards,\n{INSTITUTION_NAME}",
            ],
        ];

        foreach ($templates as $t) {
            EmailTemplate::updateOrCreate(['event_key' => $t['event_key']], $t);
        }
    }
}

