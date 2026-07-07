<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('day_closings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->date('closing_date');
            $table->string('department'); // NSD, CSD, OD
            $table->json('achieved_metrics'); // e.g. {"sts": 46, "dsr": 1}
            $table->string('target_status'); // Met, Not Met
            $table->text('executive_remarks')->nullable();
            $table->string('status')->default('Pending'); // Pending, Approved, Rejected
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->text('tl_remarks')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'closing_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('day_closings');
    }
};
