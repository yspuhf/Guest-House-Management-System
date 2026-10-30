<?php

namespace App\Http\Controllers;

use App\Models\BookingDocument;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function download(BookingDocument $document)
    {
        $user = Auth::user();
        $booking = $document->booking;

        // Authorize download: Must be applicant/submitter or have verification/admin privilege
        $authorized = $user->hasRole(['system_admin', 'guest_house_admin', 'guest_house_incharge', 'verifier', 'approver', 'accounts_finance', 'report_auditor'])
            || $booking->applicant_id === $user->id
            || $booking->submitted_by_id === $user->id;

        if (!$authorized) {
            abort(403, 'You are not authorized to access this private document.');
        }

        if (!Storage::exists($document->file_path)) {
            abort(404, 'The requested document file could not be found in private storage.');
        }

        return Storage::download($document->file_path, $document->original_filename);
    }
}

