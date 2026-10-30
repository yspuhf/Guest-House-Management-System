<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    public function view(User $user, Invoice $invoice): bool
    {
        if ($user->hasRole(['system_admin', 'guest_house_admin', 'guest_house_incharge', 'accounts_finance', 'report_auditor'])) {
            return true;
        }

        return $invoice->booking->applicant_id === $user->id || $invoice->booking->submitted_by_id === $user->id;
    }

    public function manage(User $user): bool
    {
        return $user->hasRole(['accounts_finance', 'guest_house_incharge', 'system_admin']);
    }
}

