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
        Schema::table('global_attendance_logs', function (Blueprint $table) {
            $table->string('work_location', 50)->nullable()->after('status');
            $table->text('work_location_notes')->nullable()->after('work_location');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('global_attendance_logs', function (Blueprint $table) {
            $table->dropColumn(['work_location', 'work_location_notes']);
        });
    }
};
