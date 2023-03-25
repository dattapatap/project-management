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
        Schema::create('department_members', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('department');
            $table->foreign('department')->references('id')->on('departments');

            $table->unsignedBigInteger('user');
            $table->foreign('user')->references('id')->on('users');

            $table->dateTime('from_date');
            $table->dateTime('to_date')->nullable();

            $table->boolean('status')->default(true); // Active/Inactive

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
        Schema::dropIfExists('department_members');
    }
};
