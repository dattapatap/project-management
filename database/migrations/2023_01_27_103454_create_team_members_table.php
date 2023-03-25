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
        Schema::create('team_members', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team');
            $table->foreign('team')->references('id')->on('teams');

            $table->unsignedBigInteger('user');
            $table->foreign('user')->references('id')->on('users');

            $table->unsignedBigInteger('department');

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
        Schema::dropIfExists('team_members');
    }
};
