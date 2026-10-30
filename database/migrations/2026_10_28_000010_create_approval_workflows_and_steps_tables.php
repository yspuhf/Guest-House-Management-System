<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_workflows', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('service_type', 100)->default('Guest House Booking');
            $table->foreignId('organization_unit_id')->nullable()->constrained('organization_units')->nullOnDelete();
            $table->boolean('allow_return_for_correction')->default(true);
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index('service_type');
            $table->index('is_active');
            $table->index('organization_unit_id');
        });

        Schema::create('approval_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approval_workflow_id')->constrained('approval_workflows')->cascadeOnDelete();
            $table->integer('step_order')->default(1);
            $table->enum('stage_type', ['verifier', 'approver', 'incharge', 'admin'])->default('verifier');
            $table->string('role_name', 50)->nullable(); // e.g., 'verifier', 'approver'
            $table->foreignId('specific_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('step_title', 100);
            $table->boolean('can_edit')->default(false);
            $table->boolean('can_reject')->default(true);
            $table->boolean('can_return')->default(true);
            $table->timestamps();

            $table->index(['approval_workflow_id', 'step_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_steps');
        Schema::dropIfExists('approval_workflows');
    }
};

