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
        Schema::create('task_logs', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('taskid');
            $table->foreign('taskid')->references('id')->on('tasks')->onDelete('cascade');

            $table->bigInteger('userid');

            $table->dateTime('start_date');
            $table->dateTime('end_date')->nullable();

            $table->double('time_spend', 6,2);

            $table->date('log_date');

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
        Schema::dropIfExists('task_logs');
    }
};
