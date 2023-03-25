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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category');

            $table->text('website_link')->nullable();

            $table->string('cont_person')->nullable();
            $table->string('designation')->nullable();

            $table->string('email')->nullable();
            $table->string('alt_email')->nullable();
            $table->string('mobile');
            $table->string('alt_mobile')->nullable();
            $table->string('telephone')->nullable();
            $table->string('alt_telephone')->nullable();

            $table->text('address');
            $table->string('city');

            $table->text('alt_address')->nullable();

            $table->unsignedBigInteger('ref_user');
            $table->foreign('ref_user')->references('id')->on('users');

            $table->unsignedBigInteger('tele_ref_user');
            $table->foreign('tele_ref_user')->references('id')->on('users');


            $table->string('description')->nullable();
            $table->string('status');

            $table->boolean('is_active')->default(false);
            $table->dateTime('active_from')->nullable();


            $table->integer('created_by');
            $table->integer('updated_by');

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
        Schema::dropIfExists('clients');
    }
};
