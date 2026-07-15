<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('lead_source')->nullable()->default('Sales');
        });

        // 1. Set all existing clients to default 'Sales'
        DB::table('clients')->update(['lead_source' => 'Sales']);

        // 2. Identify 'Manual' clients using history touchpoints
        $manualClientIds = DB::table('client_histories')
            ->where('remarks', 'like', '%Project Management section%')
            ->pluck('client')
            ->toArray();
        if (!empty($manualClientIds)) {
            DB::table('clients')->whereIn('id', $manualClientIds)->update(['lead_source' => 'Manual']);
        }

        // 3. Identify 'Bulk' clients using history touchpoints
        $bulkClientIds = DB::table('client_histories')
            ->where('remarks', 'like', '%bulk CSV%')
            ->pluck('client')
            ->toArray();
        if (!empty($bulkClientIds)) {
            DB::table('clients')->whereIn('id', $bulkClientIds)->update(['lead_source' => 'Bulk']);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('lead_source');
        });
    }
};
