<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('csd_renewals', function (Blueprint $table) {
            $table->timestamp('renewed_at')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('csd_renewals', function (Blueprint $table) {
            $table->dropColumn('renewed_at');
        });
    }
};
