<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_engagements', function (Blueprint $table) {
            $table->id();
            $table->string('engagement_no', 32)->unique();
            $table->unsignedBigInteger('client_id');
            $table->foreign('client_id')->references('id')->on('clients');
            $table->unsignedBigInteger('parent_engagement_id')->nullable();
            $table->foreign('parent_engagement_id')->references('id')->on('client_engagements');
            $table->unsignedBigInteger('root_engagement_id')->nullable();
            $table->foreign('root_engagement_id')->references('id')->on('client_engagements');

            $table->string('source_type')->nullable(); // nsd_maturity, csd_opportunity, manual, change_request
            $table->unsignedBigInteger('source_id')->nullable();
            $table->unsignedBigInteger('opportunity_id')->nullable();

            $table->string('engagement_type'); // initial, upsell, cross_sell, amendment
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status')->default('identified');
            $table->decimal('estimated_value', 12, 2)->nullable();
            $table->decimal('closed_value', 12, 2)->nullable();

            $table->unsignedBigInteger('project_id')->nullable();
            $table->foreign('project_id')->references('id')->on('department_projects');
            $table->unsignedBigInteger('package_id')->nullable();
            $table->foreign('package_id')->references('id')->on('client_packages');

            $table->unsignedBigInteger('sales_owner_id')->nullable();
            $table->foreign('sales_owner_id')->references('id')->on('users');
            $table->unsignedBigInteger('csd_owner_id')->nullable();
            $table->foreign('csd_owner_id')->references('id')->on('users');
            $table->unsignedBigInteger('csd_team_leader_id')->nullable();
            $table->foreign('csd_team_leader_id')->references('id')->on('users');

            $table->timestamp('won_at')->nullable();
            $table->timestamp('commercial_closed_at')->nullable();
            $table->timestamp('delivery_started_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->unsignedBigInteger('created_by');
            $table->foreign('created_by')->references('id')->on('users');
            $table->timestamps();

            $table->index(['client_id', 'status']);
            $table->index(['parent_engagement_id']);
            $table->index(['source_type', 'source_id']);
        });

        Schema::create('client_engagement_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('engagement_id');
            $table->foreign('engagement_id')->references('id')->on('client_engagements')->cascadeOnDelete();
            $table->string('event_type');
            $table->string('from_status')->nullable();
            $table->string('to_status')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users');
            $table->timestamps();
        });

        Schema::table('csd_opportunities', function (Blueprint $table) {
            $table->unsignedBigInteger('engagement_id')->nullable()->after('client');
            $table->foreign('engagement_id')->references('id')->on('client_engagements');
        });

        if (!Schema::hasColumn('department_projects', 'engagement_id')) {
            Schema::table('department_projects', function (Blueprint $table) {
                $table->unsignedBigInteger('engagement_id')->nullable()->after('client');
                $table->foreign('engagement_id')->references('id')->on('client_engagements');
            });
        }

        if (!Schema::hasColumn('client_packages', 'engagement_id')) {
            Schema::table('client_packages', function (Blueprint $table) {
                $table->unsignedBigInteger('engagement_id')->nullable()->after('client');
                $table->foreign('engagement_id')->references('id')->on('client_engagements');
            });
        }
    }

    public function down(): void
    {
        Schema::table('client_packages', function (Blueprint $table) {
            if (Schema::hasColumn('client_packages', 'engagement_id')) {
                $table->dropForeign(['engagement_id']);
                $table->dropColumn('engagement_id');
            }
        });

        Schema::table('department_projects', function (Blueprint $table) {
            if (Schema::hasColumn('department_projects', 'engagement_id')) {
                $table->dropForeign(['engagement_id']);
                $table->dropColumn('engagement_id');
            }
        });

        Schema::table('csd_opportunities', function (Blueprint $table) {
            $table->dropForeign(['engagement_id']);
            $table->dropColumn('engagement_id');
        });

        Schema::dropIfExists('client_engagement_events');
        Schema::dropIfExists('client_engagements');
    }
};
