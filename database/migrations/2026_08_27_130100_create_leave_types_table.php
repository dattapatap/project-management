<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('leave_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. Casual Leave, Sick Leave, Paid Leave
            $table->string('code')->unique(); // e.g. CL, SL, PL, LWP
            $table->unsignedInteger('days_allowed_per_year')->default(12);
            $table->boolean('is_paid')->default(true);
            $table->boolean('status')->default(true); // Active / Inactive
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_types');
    }
};
