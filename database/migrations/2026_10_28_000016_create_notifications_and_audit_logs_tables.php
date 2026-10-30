<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('event_key', 100)->unique(); // e.g. booking_submitted, booking_verified, booking_approved, booking_rejected, room_allocated, checked_in, checked_out, payment_received
            $table->string('title', 150);
            $table->string('subject', 255);
            $table->text('body_template');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('event_key');
        });

        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->nullable()->constrained('bookings')->nullOnDelete();
            $table->string('recipient_email', 150);
            $table->string('recipient_name', 150)->nullable();
            $table->string('event', 100);
            $table->string('subject', 255);
            $table->text('message_body')->nullable();
            $table->enum('delivery_status', ['Sent', 'Queued', 'Failed'])->default('Sent');
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->useCurrent();
            $table->timestamps();

            $table->index('booking_id');
            $table->index('delivery_status');
            $table->index('sent_at');
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('actor_name', 150)->nullable();
            $table->string('actor_role', 50)->nullable();
            $table->string('action', 100); // Created, Updated, Deleted, Published, Allocated, Approved, Rejected, Returned, Cancelled, CheckedIn, CheckedOut, PaymentRecorded
            $table->string('module', 100); // Guest House, Room, Tariff, Booking, Workflow, Finance, RBAC, System Settings
            $table->string('auditable_type', 150)->nullable(); // Eloquent Model Class
            $table->unsignedBigInteger('auditable_id')->nullable();
            $table->json('before_values')->nullable();
            $table->json('after_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('logged_at')->useCurrent();

            $table->index(['auditable_type', 'auditable_id']);
            $table->index('actor_id');
            $table->index('module');
            $table->index('action');
            $table->index('logged_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('notification_logs');
        Schema::dropIfExists('email_templates');
    }
};

