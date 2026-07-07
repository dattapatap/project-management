<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('target_type'); // sts_updates, dsr_updates, communications, hours_logged, tasks_completed
            $table->integer('target_value');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['user_id', 'target_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_targets');
    }
};
