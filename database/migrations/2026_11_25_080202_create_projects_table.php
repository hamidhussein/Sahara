<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('village')->nullable();
            $table->string('district')->nullable()->index();
            $table->string('status')->default('planned')->index();
            $table->string('donor')->nullable();
            $table->unsignedInteger('people_served')->nullable();
            $table->string('people_served_label')->nullable();
            $table->unsignedBigInteger('cost')->nullable();
            $table->date('started_on')->nullable();
            $table->date('completed_on')->nullable();
            $table->text('summary');
            $table->longText('story')->nullable();
            $table->string('language', 2)->default('en');
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_published')->default(true);
            $table->string('seo_title')->nullable();
            $table->string('seo_description', 320)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
