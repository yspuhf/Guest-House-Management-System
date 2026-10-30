<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('taxes', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('code', 50)->unique();
            $table->enum('calculation_method', ['percentage', 'fixed'])->default('percentage');
            $table->decimal('rate', 8, 2)->default(0.00); // e.g., 6.00, 12.00, 18.00
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('is_active');
        });

        Schema::create('expenditure_heads', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 150);
            $table->string('department', 100)->nullable();
            $table->text('description')->nullable();
            $table->boolean('requires_approval_document')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenditure_heads');
        Schema::dropIfExists('taxes');
    }
};

