<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->foreignId('room_id')->constrained('rooms')->restrictOnDelete();
            $table->foreignId('allocated_by_id')->constrained('users')->restrictOnDelete();
            $table->timestamp('allocated_at')->useCurrent();
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamp('checked_out_at')->nullable();
            $table->enum('status', ['Allocated', 'Occupied', 'Vacated', 'Released'])->default('Allocated');
            $table->text('room_condition_on_checkin')->nullable();
            $table->text('room_condition_on_checkout')->nullable();
            $table->timestamps();

            $table->index('booking_id');
            $table->index('room_id');
            $table->index('status');
        });

        Schema::create('room_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('rooms')->cascadeOnDelete();
            $table->foreignId('guest_house_id')->constrained('guest_houses')->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date'); // [start_date, end_date)
            $table->enum('block_type', ['maintenance', 'deep_cleaning', 'vip_hold', 'administrative_block'])->default('maintenance');
            $table->text('reason');
            $table->foreignId('blocked_by_id')->constrained('users')->restrictOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['room_id', 'is_active']);
            $table->index(['start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_blocks');
        Schema::dropIfExists('booking_rooms');
    }
};

