<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guest_houses', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->text('address')->nullable();
            $table->text('eligibility_rules')->nullable();
            $table->foreignId('assigned_incharge_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('contact_person', 100)->nullable();
            $table->string('contact_phone', 50)->nullable();
            $table->string('contact_email', 150)->nullable();
            $table->enum('status', ['draft', 'published', 'deactivated'])->default('draft');
            $table->boolean('is_active')->default(true);
            $table->string('supporting_document_path', 255)->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('is_active');
            $table->index('assigned_incharge_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guest_houses');
    }
};

