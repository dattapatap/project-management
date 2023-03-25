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
        Schema::create('client_payments', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('client');
            $table->foreign('client')->references('id')->on('clients');

            $table->unsignedBigInteger('package_id');
            $table->foreign('package_id')->references('id')->on('client_packages');

            $table->date('paid_date');

            $table->double('amount', 15,2);
            $table->double('remains', 15,2);

            $table->integer('created_by');
            $table->string('payment_type');

            $table->string('file')->nullable();
            $table->string('transactioinid')->nullable();

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
        Schema::dropIfExists('client_payments');
    }
};
