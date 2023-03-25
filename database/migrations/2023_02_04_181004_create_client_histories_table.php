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
        Schema::create('client_histories', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('client');
            $table->foreign('client')->references('id')->on('clients');

            $table->string('category');
            $table->text('remarks');

            $table->string('status')->nullable();
            $table->string('tbro_type')->nullable();
            $table->time('time')->nullable();
            $table->date('tbro')->nullable();
            $table->string('demo_link')->nullable();

            $table->integer('created');

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
        Schema::dropIfExists('client_histories');
    }
};
