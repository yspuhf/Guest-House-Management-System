<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->string('from_status', 50)->nullable();
            $table->string('to_status', 50);
            $table->string('action', 100); // e.g. Submitted, Verified, Approved, Rejected, Returned, Room Allocated, Checked In, Checked Out, Cancelled, Extended
            $table->foreignId('actor_id')->constrained('users')->cascadeOnDelete();
            $table->text('remarks')->nullable();
            $table->string('workflow_stage', 100)->nullable();
            $table->timestamps();

            $table->index(['booking_id', 'created_at']);
        });

        Schema::create('booking_extensions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->date('original_departure_date');
            $table->date('requested_departure_date');
            $table->integer('additional_nights');
            $table->decimal('additional_charges', 10, 2)->default(0.00);
            $table->text('reason');
            $table->enum('status', ['Pending', 'Approved', 'Rejected'])->default('Pending');
            $table->foreignId('requested_by_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('decided_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('decision_remarks')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();

            $table->index(['booking_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_extensions');
        Schema::dropIfExists('booking_status_history');
    }
};

