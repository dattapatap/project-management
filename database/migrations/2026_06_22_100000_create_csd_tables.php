<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('csd_client_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client');
            $table->foreign('client')->references('id')->on('clients');
            $table->unsignedBigInteger('project_id')->nullable();
            $table->foreign('project_id')->references('id')->on('department_projects');
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->foreign('assigned_to')->references('id')->on('users');
            $table->date('handoff_date')->nullable();
            $table->string('health_status')->default('healthy'); // healthy, at_risk, churning
            $table->unsignedTinyInteger('satisfaction_score')->nullable();
            $table->string('status')->default('active'); // active, inactive
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->foreign('created_by')->references('id')->on('users');
            $table->timestamps();
        });

        Schema::create('csd_contact_persons', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client');
            $table->foreign('client')->references('id')->on('clients');
            $table->string('name');
            $table->string('designation')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('whatsapp')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->unsignedBigInteger('created_by');
            $table->foreign('created_by')->references('id')->on('users');
            $table->timestamps();
        });

        Schema::create('csd_communications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client');
            $table->foreign('client')->references('id')->on('clients');
            $table->unsignedBigInteger('assignment_id')->nullable();
            $table->foreign('assignment_id')->references('id')->on('csd_client_assignments');
            $table->string('type'); // call, meeting, email, whatsapp, note
            $table->string('subject')->nullable();
            $table->text('remarks');
            $table->dateTime('communication_date');
            $table->date('next_followup')->nullable();
            $table->text('mom')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->foreign('created_by')->references('id')->on('users');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('csd_collection_followups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client');
            $table->foreign('client')->references('id')->on('clients');
            $table->unsignedBigInteger('package_id')->nullable();
            $table->foreign('package_id')->references('id')->on('client_packages');
            $table->string('invoice_ref')->nullable();
            $table->decimal('amount_due', 12, 2)->default(0);
            $table->date('due_date')->nullable();
            $table->date('followup_date')->nullable();
            $table->string('status')->default('pending'); // pending, partial, paid, overdue
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->foreign('assigned_to')->references('id')->on('users');
            $table->unsignedBigInteger('created_by');
            $table->foreign('created_by')->references('id')->on('users');
            $table->timestamps();
        });

        Schema::create('csd_change_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client');
            $table->foreign('client')->references('id')->on('clients');
            $table->unsignedBigInteger('project_id')->nullable();
            $table->foreign('project_id')->references('id')->on('department_projects');
            $table->string('title');
            $table->text('description');
            $table->string('requirement_doc')->nullable();
            $table->decimal('estimated_cost', 12, 2)->nullable();
            $table->string('status')->default('submitted');
            // submitted, estimating, approved, rejected, transferred_to_od, completed
            $table->unsignedBigInteger('od_project_id')->nullable();
            $table->foreign('od_project_id')->references('id')->on('department_projects');
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->foreign('assigned_to')->references('id')->on('users');
            $table->unsignedBigInteger('created_by');
            $table->foreign('created_by')->references('id')->on('users');
            $table->timestamps();
        });

        Schema::create('csd_support_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_no')->unique();
            $table->unsignedBigInteger('client');
            $table->foreign('client')->references('id')->on('clients');
            $table->string('subject');
            $table->text('description');
            $table->string('type')->default('ticket'); // ticket, complaint, escalation
            $table->string('priority')->default('medium'); // low, medium, high, critical
            $table->string('status')->default('open'); // open, in_progress, resolved, closed
            $table->dateTime('sla_due_at')->nullable();
            $table->dateTime('resolved_at')->nullable();
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->foreign('assigned_to')->references('id')->on('users');
            $table->unsignedBigInteger('created_by');
            $table->foreign('created_by')->references('id')->on('users');
            $table->timestamps();
        });

        Schema::create('csd_amc_contracts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client');
            $table->foreign('client')->references('id')->on('clients');
            $table->unsignedBigInteger('project_id')->nullable();
            $table->foreign('project_id')->references('id')->on('department_projects');
            $table->string('contract_type')->default('amc'); // amc, support
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('status')->default('active'); // active, expired, renewed, cancelled
            $table->unsignedBigInteger('created_by');
            $table->foreign('created_by')->references('id')->on('users');
            $table->timestamps();
        });

        Schema::create('csd_renewals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client');
            $table->foreign('client')->references('id')->on('clients');
            $table->string('renewal_type'); // amc, domain, hosting, subscription
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('title');
            $table->date('due_date');
            $table->decimal('amount', 12, 2)->nullable();
            $table->string('status')->default('upcoming'); // upcoming, due, renewed, lapsed
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->foreign('created_by')->references('id')->on('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('csd_renewals');
        Schema::dropIfExists('csd_amc_contracts');
        Schema::dropIfExists('csd_support_tickets');
        Schema::dropIfExists('csd_change_requests');
        Schema::dropIfExists('csd_collection_followups');
        Schema::dropIfExists('csd_communications');
        Schema::dropIfExists('csd_contact_persons');
        Schema::dropIfExists('csd_client_assignments');
    }
};
