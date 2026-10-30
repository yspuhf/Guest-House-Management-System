<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['id' => 1, 'name' => 'system_admin', 'display_name' => 'System Administrator', 'description' => 'Full control over system accounts, security, settings and backups'],
            ['id' => 2, 'name' => 'guest_house_admin', 'display_name' => 'Guest House Administrator', 'description' => 'Configures properties, rooms, tariffs, guest categories and policies'],
            ['id' => 3, 'name' => 'guest_house_incharge', 'display_name' => 'Guest House Incharge', 'description' => 'Manages room allocation, check-in, check-out, room condition and daily operations'],
            ['id' => 4, 'name' => 'verifier', 'display_name' => 'Booking Verifier', 'description' => 'First-level scrutinizer for booking requests and uploaded approval letters'],
            ['id' => 5, 'name' => 'approver', 'display_name' => 'Approving Authority', 'description' => 'Delegated authority (Dean/Director/Registrar) to approve or reject requests'],
            ['id' => 6, 'name' => 'employee', 'display_name' => 'Employee / Applicant', 'description' => 'University faculty, staff or scholar submitting and tracking guest bookings'],
            ['id' => 7, 'name' => 'accounts_finance', 'display_name' => 'Accounts / Finance Officer', 'description' => 'Processes payments, issues official receipts, manages refunds and billing registers'],
            ['id' => 8, 'name' => 'report_auditor', 'display_name' => 'Report Viewer / Auditor', 'description' => 'View-only access to operational, financial and system audit registers'],
        ];

        foreach ($roles as $r) {
            Role::updateOrCreate(['name' => $r['name']], $r);
        }

        $permissions = [
            ['name' => 'users.manage', 'module' => 'RBAC', 'display_name' => 'Manage Users & Roles'],
            ['name' => 'masters.manage', 'module' => 'Configuration', 'display_name' => 'Manage Master Data'],
            ['name' => 'bookings.create', 'module' => 'Bookings', 'display_name' => 'Create Bookings'],
            ['name' => 'bookings.view_own', 'module' => 'Bookings', 'display_name' => 'View Own Bookings'],
            ['name' => 'bookings.verify', 'module' => 'Workflow', 'display_name' => 'Verify Bookings'],
            ['name' => 'bookings.approve', 'module' => 'Workflow', 'display_name' => 'Approve/Reject Bookings'],
            ['name' => 'rooms.allocate', 'module' => 'Operations', 'display_name' => 'Allocate Rooms'],
            ['name' => 'operations.checkin_checkout', 'module' => 'Operations', 'display_name' => 'Check-in and Check-out'],
            ['name' => 'billing.manage', 'module' => 'Finance', 'display_name' => 'Billing & Cashiering'],
            ['name' => 'reports.view', 'module' => 'Reporting', 'display_name' => 'View Reports'],
            ['name' => 'audit.view', 'module' => 'Audit', 'display_name' => 'View Audit Trail'],
        ];

        foreach ($permissions as $p) {
            Permission::updateOrCreate(['name' => $p['name']], $p);
        }

        // Attach permissions to roles
        $admin = Role::where('name', 'system_admin')->first();
        if ($admin) {
            $admin->permissions()->sync(Permission::all());
        }
    }
}

