<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guest_house_id')->constrained('guest_houses')->cascadeOnDelete();
            $table->foreignId('room_type_id')->constrained('room_types')->restrictOnDelete();
            $table->string('room_number', 50);
            $table->string('floor', 50)->nullable();
            $table->decimal('tariff', 10, 2)->nullable(); // null means use room_type or guest_type tariff
            $table->integer('max_occupancy')->default(2);
            $table->json('facilities')->nullable(); // e.g. ["AC", "Geyser", "Wi-Fi", "TV", "Study Desk"]
            $table->boolean('is_active')->default(true);
            $table->enum('publication_status', ['draft', 'published'])->default('published');
            $table->enum('maintenance_status', ['operational', 'under_maintenance', 'cleaning_in_progress'])->default('operational');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['guest_house_id', 'room_number']);
            $table->index('guest_house_id');
            $table->index('room_type_id');
            $table->index('is_active');
            $table->index('publication_status');
            $table->index('maintenance_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};

