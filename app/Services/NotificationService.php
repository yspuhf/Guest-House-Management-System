<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\EmailTemplate;
use App\Models\NotificationLog;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    /**
     * Send email notification based on configured template and log attempt.
     * Email failure never rolls back the parent transaction.
     */
    public function notify(Booking $booking, string $eventKey, array $extraReplacements = []): ?NotificationLog
    {
        try {
            $template = EmailTemplate::where('event_key', $eventKey)->where('is_active', true)->first();
            if (!$template) {
                return null;
            }

            $applicant = $booking->applicant;
            $primaryGuest = $booking->primaryGuest;
            $instName = SystemSetting::get('institution_name', 'Dr. Y.S. Parmar University of Horticulture & Forestry, Nauni');
            $currency = SystemSetting::get('currency_symbol', '₹');
            $rooms = $booking->bookingRooms->map(fn($br) => $br->room->room_number)->join(', ');

            $replacements = array_merge([
                '{APPLICANT_NAME}' => $applicant?->name ?? 'Applicant',
                '{GUEST_NAME}' => $primaryGuest?->name ?? 'Guest',
                '{BOOKING_REF}' => $booking->booking_reference,
                '{GUEST_HOUSE}' => $booking->guestHouse?->name ?? 'University Guest House',
                '{ROOM_TYPE}' => $booking->roomType?->name ?? 'Standard Room',
                '{ROOM_NUMBERS}' => $rooms ?: 'Pending Allocation',
                '{ARRIVAL_DATE}' => $booking->arrival_date ? $booking->arrival_date->format('d M Y') : '',
                '{DEPARTURE_DATE}' => $booking->departure_date ? $booking->departure_date->format('d M Y') : '',
                '{CHECKIN_TIME}' => $booking->arrival_time ?? '12:00',
                '{CHECKOUT_TIME}' => $booking->departure_time ?? '11:00',
                '{CHECKIN_TIMESTAMP}' => now()->format('d M Y H:i:s'),
                '{INSTITUTION_NAME}' => $instName,
                '{CURRENCY}' => $currency,
                '{REMARKS}' => 'None',
            ], $extraReplacements);

            $subject = str_replace(array_keys($replacements), array_values($replacements), $template->subject);
            $body = str_replace(array_keys($replacements), array_values($replacements), $template->body_template);

            $recipientEmail = $applicant?->email ?? 'guest@yspuniversity.ac.in';
            $recipientName = $applicant?->name ?? 'Applicant';

            // Attempt email dispatch (safe fail)
            $deliveryStatus = 'Sent';
            $errorMessage = null;

            try {
                // If mail driver is configured
                if (config('mail.default')) {
                    Mail::raw($body, function ($message) use ($recipientEmail, $recipientName, $subject) {
                        $message->to($recipientEmail, $recipientName)
                                ->subject($subject);
                    });
                }
            } catch (\Throwable $mailEx) {
                $deliveryStatus = 'Failed';
                $errorMessage = $mailEx->getMessage();
            }

            return NotificationLog::create([
                'booking_id' => $booking->id,
                'recipient_email' => $recipientEmail,
                'recipient_name' => $recipientName,
                'event' => $eventKey,
                'subject' => $subject,
                'message_body' => $body,
                'delivery_status' => $deliveryStatus,
                'error_message' => $errorMessage,
                'sent_at' => now(),
            ]);
        } catch (\Throwable $e) {
            logger()->error("Notification error for event {$eventKey}: " . $e->getMessage());
            return null;
        }
    }
}

