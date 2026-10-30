<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guest_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->integer('priority_order')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('is_active');
        });

        Schema::create('guest_type_tariffs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guest_type_id')->constrained('guest_types')->cascadeOnDelete();
            $table->foreignId('room_type_id')->constrained('room_types')->cascadeOnDelete();
            $table->decimal('rate_per_night', 10, 2);
            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['guest_type_id', 'room_type_id'], 'guest_room_tariff_unique');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guest_type_tariffs');
        Schema::dropIfExists('guest_types');
    }
};

