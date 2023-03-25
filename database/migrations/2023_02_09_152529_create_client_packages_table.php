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
        Schema::create('client_packages', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('client');
            $table->foreign('client')->references('id')->on('clients');

            $table->unsignedBigInteger('project_id');
            $table->foreign('project_id')->references('id')->on('department_projects');

            $table->double('package', 15,2);
            $table->double('balance', 15,2)->nullable();
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
        Schema::dropIfExists('client_packages');
    }
};
