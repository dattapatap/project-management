<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('csd_amc_contracts', function (Blueprint $table) {
            $table->string('billing_cycle')->default('yearly')->after('contract_type'); // monthly, yearly
            $table->string('document_path')->nullable()->after('amount');
            $table->string('document_name')->nullable()->after('document_path');
        });
    }

    public function down(): void
    {
        Schema::table('csd_amc_contracts', function (Blueprint $table) {
            $table->dropColumn(['billing_cycle', 'document_path', 'document_name']);
        });
    }
};
