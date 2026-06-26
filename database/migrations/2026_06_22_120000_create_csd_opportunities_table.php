<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('csd_opportunities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client');
            $table->foreign('client')->references('id')->on('clients');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('type')->default('upsell'); // upsell, cross_sell
            $table->decimal('estimated_value', 12, 2)->nullable();
            $table->string('status')->default('identified'); // identified, proposed, won, lost
            $table->date('followup_date')->nullable();
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->foreign('assigned_to')->references('id')->on('users');
            $table->unsignedBigInteger('created_by');
            $table->foreign('created_by')->references('id')->on('users');
            $table->timestamps();
        });

        Schema::table('csd_collection_followups', function (Blueprint $table) {
            $table->date('commitment_date')->nullable()->after('followup_date');
            $table->decimal('commitment_amount', 12, 2)->nullable()->after('commitment_date');
        });
    }

    public function down(): void
    {
        Schema::table('csd_collection_followups', function (Blueprint $table) {
            $table->dropColumn(['commitment_date', 'commitment_amount']);
        });
        Schema::dropIfExists('csd_opportunities');
    }
};
