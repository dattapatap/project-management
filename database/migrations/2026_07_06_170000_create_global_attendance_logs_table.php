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
        Schema::create('global_attendance_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('userid');
            $table->date('log_date');
            $table->time('starttime');
            $table->time('endtime')->nullable();
            $table->double('time_spend')->nullable();
            $table->string('status')->default('active'); // active, paused, completed
            $table->timestamps();

            $table->foreign('userid')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('global_attendance_logs');
    }
};
