<?php

namespace App\Providers;

use App\Models\Booking;
use App\Models\GuestHouse;
use App\Models\Invoice;
use App\Policies\BookingPolicy;
use App\Policies\GuestHousePolicy;
use App\Policies\InvoicePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Booking::class => BookingPolicy::class,
        GuestHouse::class => GuestHousePolicy::class,
        Invoice::class => InvoicePolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        // Super Admin Gate
        Gate::before(function ($user, $ability) {
            if ($user->primary_role === 'system_admin') {
                return true;
            }
        });
    }
}

