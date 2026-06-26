<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('client_engagements', 'csd_team_leader_id')) {
            Schema::table('client_engagements', function (Blueprint $table) {
                $table->unsignedBigInteger('csd_team_leader_id')->nullable()->after('csd_owner_id');
                $table->foreign('csd_team_leader_id')->references('id')->on('users');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('client_engagements', 'csd_team_leader_id')) {
            Schema::table('client_engagements', function (Blueprint $table) {
                $table->dropForeign(['csd_team_leader_id']);
                $table->dropColumn('csd_team_leader_id');
            });
        }
    }
};
