<?php

namespace App\Policies;

use App\Models\GuestHouse;
use App\Models\User;

class GuestHousePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function manage(User $user): bool
    {
        return $user->hasRole(['system_admin', 'guest_house_admin']);
    }

    public function publish(User $user, GuestHouse $guestHouse): bool
    {
        return $user->hasRole(['system_admin', 'guest_house_admin']);
    }
}

