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
        Schema::create('client_docs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client');
            $table->foreign('client')->references('id')->on('clients');

            $table->integer('history')->nullable();

            $table->string('category');
            $table->string('doc_type');

            $table->text('files');
            $table->string('description')->nullable();

            $table->dateTime('uploaded')->nullable();
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
        Schema::dropIfExists('client_docs');
    }
};
