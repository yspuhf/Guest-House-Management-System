<?php

namespace App\Policies;

use App\Models\User;

class ReportPolicy
{
    public function view(User $user): bool
    {
        return $user->hasRole(['system_admin', 'guest_house_admin', 'guest_house_incharge', 'accounts_finance', 'report_auditor']);
    }
}

