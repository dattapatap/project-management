<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('department_projects', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('client');
            $table->foreign('client')->references('id')->on('clients');

            $table->unsignedBigInteger('department');
            $table->foreign('department')->references('id')->on('departments');

            $table->integer('category');
            $table->integer('sub_category');

            $table->unsignedBigInteger('assigned_by');
            $table->foreign('assigned_by')->references('id')->on('users');

            $table->dateTime('created_date');

            $table->string('project_name');

            $table->dateTime('start_date');
            $table->dateTime('end_date')->nullable();

            $table->dateTime('act_start_date')->nullable();
            $table->dateTime('act_end_date')->nullable();

            $table->string('status')->default('Assigned');
            $table->string('project_link')->nullable();

            $table->string('referel_link')->nullable();
            $table->string('demo_link')->nullable();
            $table->longText('description')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('department_projects');
    }
};
