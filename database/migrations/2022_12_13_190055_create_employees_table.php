<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user');
            $table->foreign('user')->references('id')->on('users');

            $table->string('name');
            $table->string('alt_email')->nullable();
            $table->string('alt_number')->nullable();
            $table->string('gender');
            $table->date('dob');
            $table->date('joining_dt');
            $table->date('end_dt')->nullable();
            $table->string('mem_code');
            $table->string('designation');
            $table->string('status');

            $table->string('fb')->nullable();
            $table->string('insta')->nullable();
            $table->string('twitter')->nullable();
            $table->string('youtube')->nullable();
            $table->string('github')->nullable();


            $table->integer('created_by');
            $table->integer('updated_by')->nullable();
            $table->dateTime('last_login')->nullable();

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
        Schema::dropIfExists('employees');
    }
};
