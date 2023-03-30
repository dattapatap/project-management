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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('projectid');
            $table->foreign('projectid')->references('id')->on('department_projects')->onDelete('cascade');

            $table->bigInteger('created_by');
            $table->bigInteger('assigned_to')->nullable();

            $table->string('title');
            $table->longText('description')->nullable();

            $table->string('status')->default('Todo'); //['Todo', 'Inprogress', 'Completed']
            $table->string('priority')->default('Medium'); //[Low, Medium, High]

            $table->dateTime('startdate');
            $table->dateTime('enddate');
            $table->dateTime('act_startdate')->nullable();
            $table->dateTime('act_enddate')->nullable();

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
        Schema::dropIfExists('tasks');
    }
};
