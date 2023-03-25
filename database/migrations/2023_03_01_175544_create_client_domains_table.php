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
        Schema::create('client_domains', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client');
            $table->foreign('client')->references('id')->on('clients');
            $table->string('client_name')->nullable();
            $table->string('domain');
            $table->date('registered_dt');
            $table->date('expiry_dt');
            $table->integer('created_by');
            $table->boolean('notified')->default(false);
            $table->boolean('renewed')->default(false);
            $table->date('renewd_dt')->nullable();
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
        Schema::dropIfExists('client_domains');
    }
};
