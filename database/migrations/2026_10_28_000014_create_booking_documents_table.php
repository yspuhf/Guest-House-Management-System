<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->string('document_title', 150);
            $table->enum('document_type', ['approval_letter', 'id_proof', 'duty_slip', 'payment_receipt', 'other'])->default('approval_letter');
            $table->string('file_path', 255);
            $table->string('original_filename', 255);
            $table->string('mime_type', 100)->nullable();
            $table->bigInteger('file_size_bytes')->default(0);
            $table->foreignId('uploaded_by_id')->constrained('users')->cascadeOnDelete();
            $table->boolean('is_confidential')->default(false); // identity proofs are flagged confidential
            $table->timestamps();

            $table->index(['booking_id', 'document_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_documents');
    }
};

