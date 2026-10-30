<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number', 50)->unique(); // e.g., INV-2026-0001
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->date('invoice_date');
            $table->decimal('subtotal', 10, 2)->default(0.00); // Room charges
            $table->decimal('tax_total', 10, 2)->default(0.00);
            $table->decimal('additional_charges', 10, 2)->default(0.00); // Meals, amenities, damage
            $table->decimal('discount_or_waiver', 10, 2)->default(0.00);
            $table->decimal('grand_total', 10, 2)->default(0.00);
            $table->decimal('paid_amount', 10, 2)->default(0.00);
            $table->decimal('balance_due', 10, 2)->default(0.00);
            $table->enum('status', ['Unpaid', 'Partially Paid', 'Paid', 'Cancelled', 'Refunded'])->default('Unpaid');
            $table->boolean('is_finalized')->default(false);
            $table->foreignId('generated_by_id')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index('invoice_number');
            $table->index('booking_id');
            $table->index('status');
        });

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->string('item_description', 255);
            $table->enum('item_type', ['room_charge', 'tax', 'meal', 'damage', 'extension', 'adjustment'])->default('room_charge');
            $table->decimal('unit_rate', 10, 2)->default(0.00);
            $table->integer('quantity')->default(1);
            $table->decimal('line_total', 10, 2)->default(0.00);
            $table->foreignId('tax_id')->nullable()->constrained('taxes')->nullOnDelete();
            $table->decimal('tax_rate_applied', 8, 2)->default(0.00);
            $table->timestamps();

            $table->index('invoice_id');
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_number', 50)->unique(); // e.g. REC-2026-0001
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->enum('payment_method', [
                'Cash',
                'Online (Payment Gateway)',
                'POS / Card',
                'UPI / QR',
                'Demand Draft / Cheque',
                'Book Transfer / University Account'
            ])->default('Cash');
            $table->string('transaction_reference', 100)->nullable();
            $table->date('payment_date');
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['Successful', 'Pending Gateway', 'Failed', 'Refunded'])->default('Successful');
            $table->text('notes')->nullable();
            $table->foreignId('collected_by_id')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index('receipt_number');
            $table->index('invoice_id');
            $table->index('booking_id');
            $table->index('status');
        });

        Schema::create('refunds_or_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $table->enum('type', ['Refund', 'Adjustment', 'Waiver'])->default('Refund');
            $table->decimal('amount', 10, 2);
            $table->text('reason');
            $table->foreignId('authorized_by_id')->constrained('users')->restrictOnDelete();
            $table->string('reference_number', 100)->nullable();
            $table->timestamps();

            $table->index('invoice_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refunds_or_adjustments');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
    }
};

