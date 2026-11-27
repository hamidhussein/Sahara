<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Every attempt is recorded, paid or not (plan 1.2, Donation module step 3).
        Schema::create('donations', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 32)->unique();
            $table->foreignId('giving_type_id')->constrained()->restrictOnDelete();
            $table->foreignId('cause_id')->nullable()->constrained()->restrictOnDelete();
            $table->unsignedBigInteger('amount'); // whole rupees
            $table->char('currency', 3)->default('PKR');

            $table->string('donor_name');
            $table->string('donor_phone', 32);
            $table->string('donor_email')->nullable();
            $table->boolean('is_anonymous')->default(false);

            $table->string('payment_method', 32)->index();
            $table->string('status', 32)->default('pending')->index();
            $table->string('gateway_transaction_id')->nullable()->index();
            $table->json('gateway_payload')->nullable();

            // A restricted fund (Zakat/Khums) chosen for a cause not marked eligible.
            $table->boolean('needs_eligibility_review')->default(false)->index();

            $table->timestamp('paid_at')->nullable()->index();
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('receipt_sent_at')->nullable();
            $table->text('finance_notes')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donations');
    }
};
