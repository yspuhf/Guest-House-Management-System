<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_reference', 50)->unique(); // e.g., UGH-2026-0001
            $table->foreignId('applicant_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('submitted_by_id')->constrained('users')->cascadeOnDelete(); // Actor submitting
            $table->foreignId('guest_house_id')->constrained('guest_houses')->restrictOnDelete();
            $table->foreignId('room_type_id')->constrained('room_types')->restrictOnDelete();
            $table->foreignId('guest_type_id')->constrained('guest_types')->restrictOnDelete();
            $table->foreignId('expenditure_head_id')->nullable()->constrained('expenditure_heads')->nullOnDelete();
            $table->foreignId('approval_workflow_id')->nullable()->constrained('approval_workflows')->nullOnDelete();

            $table->text('purpose');
            $table->date('arrival_date');
            $table->time('arrival_time')->default('12:00:00');
            $table->date('departure_date');
            $table->time('departure_time')->default('11:00:00');
            $table->integer('billable_nights')->default(1);
            $table->integer('rooms_requested')->default(1);
            $table->integer('total_guests')->default(1);

            $table->enum('meal_preference', ['None', 'Breakfast', 'Breakfast & Dinner', 'All Meals (Breakfast, Lunch, Dinner)'])->default('None');
            $table->text('special_requests')->nullable();
            $table->boolean('accepted_terms')->default(true);

            // Lifecycle Status Flow
            $table->enum('status', [
                'Draft',
                'Submitted',
                'Verification Pending',
                'Approval Pending',
                'Approved',
                'Room Allocated',
                'Checked In',
                'Checked Out',
                'Closed',
                'Rejected',
                'Returned for Correction',
                'Cancelled',
                'Extension Pending'
            ])->default('Draft');

            $table->integer('current_step_order')->default(0);

            // Tariff & Billing Aggregates (Decimals)
            $table->decimal('applied_tariff_per_night', 10, 2)->default(0.00);
            $table->decimal('total_room_charges', 10, 2)->default(0.00);
            $table->decimal('total_tax_amount', 10, 2)->default(0.00);
            $table->decimal('total_other_charges', 10, 2)->default(0.00);
            $table->decimal('grand_total', 10, 2)->default(0.00);
            $table->decimal('paid_amount', 10, 2)->default(0.00);
            $table->decimal('balance_amount', 10, 2)->default(0.00);

            $table->enum('payment_stage_applied', ['approval', 'check_in', 'check_out'])->default('check_out');
            $table->enum('payment_status', ['unpaid', 'partially_paid', 'paid', 'exempted', 'billed_to_department'])->default('unpaid');

            // Cancellation fields
            $table->text('cancellation_reason')->nullable();
            $table->foreignId('cancelled_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('cancelled_at')->nullable();

            $table->timestamps();

            // Compound and single indexes for high performance queries
            $table->index('booking_reference');
            $table->index('applicant_id');
            $table->index('guest_house_id');
            $table->index('room_type_id');
            $table->index('status');
            $table->index(['arrival_date', 'departure_date']);
            $table->index('payment_status');
        });

        Schema::create('booking_guests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('designation', 100)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('id_document_type', 50)->nullable(); // Aadhaar, Passport, University ID, Govt ID
            $table->string('id_document_number', 100)->nullable();
            $table->boolean('is_primary')->default(false);
            $table->string('relationship', 50)->nullable(); // Self, Spouse, Colleague, Delegate
            $table->timestamps();

            $table->index('booking_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_guests');
        Schema::dropIfExists('bookings');
    }
};

