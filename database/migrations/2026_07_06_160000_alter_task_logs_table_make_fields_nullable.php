<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('task_logs', function (Blueprint $table) {
            $table->time('endtime')->nullable()->change();
            $table->double('time_spend', 6, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('task_logs', function (Blueprint $table) {
            $table->time('endtime')->nullable(false)->change();
            $table->double('time_spend', 6, 2)->nullable(false)->change();
        });
    }
};
