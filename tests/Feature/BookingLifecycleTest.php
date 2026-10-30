<?php

namespace Tests\Feature;

use App\Models\GuestHouse;
use App\Models\GuestType;
use App\Models\RoomType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_unauthenticated(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_public_tracker_is_accessible_without_authentication(): void
    {
        $response = $this->get('/track');
        $response->assertStatus(200);
        $response->assertSee('Track Guest House Reservation');
    }

    public function test_authenticated_user_can_view_booking_form(): void
    {
        $user = User::create([
            'name' => 'Faculty Member',
            'email' => 'faculty@yspuniversity.ac.in',
            'password' => bcrypt('password123'),
            'primary_role' => 'employee',
            'is_active' => true,
        ]);

        $gh = GuestHouse::create([
            'code' => 'VIP-GH',
            'name' => 'VIP Guest House',
            'status' => 'published',
            'is_active' => true,
        ]);

        $rt = RoomType::create([
            'code' => 'VIP',
            'name' => 'VIP Suite',
            'default_tariff' => 2000.00,
            'standard_capacity' => 2,
        ]);

        $gt = GuestType::create([
            'code' => 'CAT-A',
            'name' => 'Category A: Official Guest',
            'priority_order' => 1,
        ]);

        $response = $this->actingAs($user)->get('/bookings/create');
        $response->assertStatus(200);
        $response->assertSee('Guest House Reservation Requisition');
    }
}

