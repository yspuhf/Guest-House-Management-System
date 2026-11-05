<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Implements:
     * 1. Invoice Categorisation (Stay Charges vs Dining Charges vs Consolidated, strictly Non-GST).
     * 2. Room Allotment During Booking Approval with audit logging and dates tracking.
     */
    public function up(): void
    {
        // Add Categorisation and Non-GST columns to Invoices
        Schema::table('invoices', function (Blueprint $table) {
            $table->enum('invoice_type', ['stay', 'dining', 'consolidated'])->default('consolidated')->after('booking_id');
            $table->foreignId('billed_to_guest_id')->nullable()->after('invoice_type')->constrained('booking_guests')->nullOnDelete();
            $table->string('billed_guest_name', 150)->nullable()->after('billed_to_guest_id');
            $table->boolean('is_non_gst')->default(true)->after('grand_total');
            $table->string('tax_exemption_clause', 255)->default('Notification No. UHF/EO-55/2025/- 1615-40 (Public Educational Facility Exemption)')->after('is_non_gst');
            $table->decimal('stay_charges_subtotal', 10, 2)->default(0.00)->after('tax_exemption_clause');
            $table->decimal('dining_charges_subtotal', 10, 2)->default(0.00)->after('stay_charges_subtotal');
        });

        // Add Room Allotment Approval tracking & Official Paper Requisition Fields to Bookings
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('allotted_room_id')->nullable()->after('room_type_id')->constrained('rooms')->nullOnDelete();
            $table->timestamp('room_allotted_at')->nullable()->after('allotted_room_id');
            $table->foreignId('allotted_by_id')->nullable()->after('room_allotted_at')->constrained('users')->nullOnDelete();

            // Official Form Fields (Notification & Estate Application)
            $table->enum('visit_type', ['Official', 'Private'])->default('Official')->after('purpose');
            $table->string('institution_address', 500)->nullable()->after('visit_type');
            $table->string('home_address', 500)->nullable()->after('institution_address');
            $table->string('aadhar_number', 20)->nullable()->after('home_address');
            $table->enum('sharing_preference', ['Independent Suite', 'Can Be Shared'])->default('Independent Suite')->after('rooms_requested');
        });

        Schema::table('booking_rooms', function (Blueprint $table) {
            $table->timestamp('allotted_at')->nullable()->after('status');
            $table->foreignId('allotted_by_id')->nullable()->after('allotted_at')->constrained('users')->nullOnDelete();
            $table->string('allotment_remarks', 255)->nullable()->after('allotted_by_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking_rooms', function (Blueprint $table) {
            $table->dropForeign(['allotted_by_id']);
            $table->dropColumn(['allotted_at', 'allotted_by_id', 'allotment_remarks']);
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['allotted_room_id']);
            $table->dropForeign(['allotted_by_id']);
            $table->dropColumn(['allotted_room_id', 'room_allotted_at', 'allotted_by_id']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['billed_to_guest_id']);
            $table->dropColumn([
                'invoice_type',
                'billed_to_guest_id',
                'billed_guest_name',
                'is_non_gst',
                'tax_exemption_clause',
                'stay_charges_subtotal',
                'dining_charges_subtotal'
            ]);
        });
    }
};

