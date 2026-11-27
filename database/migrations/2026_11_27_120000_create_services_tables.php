<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 2 services (plan 2.2): help requests, volunteers and blood donors.
 * Phone numbers are personal data: only the Services role sees them.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('help_requests', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->string('type')->index();
            $table->string('name');
            $table->string('phone', 32);
            $table->string('district')->nullable()->index();
            $table->string('village')->nullable();
            $table->string('person_in_need')->nullable();
            $table->text('details');
            $table->string('status')->default('new')->index();
            $table->timestamp('consent_at');
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('staff_notes')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });

        Schema::create('volunteers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone', 32)->unique();
            $table->string('area')->nullable();
            $table->json('skills')->nullable();
            $table->string('availability')->nullable();
            $table->timestamp('contacted_at')->nullable()->index();
            $table->text('staff_notes')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });

        Schema::create('blood_donors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('blood_group', 3)->index();
            $table->string('district')->index();
            $table->string('phone', 32)->unique();
            $table->date('last_donated_on')->nullable();
            $table->boolean('is_available')->default(true)->index();
            $table->timestamp('consent_at');
            $table->text('staff_notes')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blood_donors');
        Schema::dropIfExists('volunteers');
        Schema::dropIfExists('help_requests');
    }
};
