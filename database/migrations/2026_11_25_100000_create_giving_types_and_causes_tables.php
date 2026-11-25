<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Each giving type is its own fund (Zakat, Khums, Sadaqah, ...), plan 1.2.
        Schema::create('giving_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            // Restricted funds (Zakat, Khums, Fitrana) may only go to causes marked eligible for them.
            $table->boolean('is_restricted')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('causes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            // Appeals appear on the home page under "Where your help is needed now".
            $table->boolean('is_appeal')->default(false);
            $table->string('appeal_title')->nullable();
            $table->unsignedBigInteger('goal_amount')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Which restricted funds a cause may receive. Set by Finance, never assumed.
        Schema::create('cause_giving_type', function (Blueprint $table) {
            $table->foreignId('cause_id')->constrained()->cascadeOnDelete();
            $table->foreignId('giving_type_id')->constrained()->cascadeOnDelete();
            $table->primary(['cause_id', 'giving_type_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cause_giving_type');
        Schema::dropIfExists('causes');
        Schema::dropIfExists('giving_types');
    }
};
