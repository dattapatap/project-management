<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::table('leave_types', function (Blueprint $table) {
            $table->decimal('monthly_limit', 4, 1)->nullable()->default(2.0)->after('days_allowed_per_year');
            $table->boolean('is_monthly_accrual')->default(true)->after('monthly_limit');
        });
    }


    public function down(): void
    {
        Schema::table('leave_types', function (Blueprint $table) {
            $table->dropColumn(['monthly_limit', 'is_monthly_accrual']);
        });
    }
};
