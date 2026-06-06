<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('environment_zones', function (Blueprint $table) {
            $table->foreignId('zone_group_id')
                ->nullable()
                ->after('environment_id')
                ->constrained('environment_zone_groups')
                ->nullOnDelete();

            $table->index('zone_group_id');
        });
    }

    public function down(): void
    {
        Schema::table('environment_zones', function (Blueprint $table) {
            $table->dropForeign(['zone_group_id']);
            $table->dropIndex(['zone_group_id']);
            $table->dropColumn('zone_group_id');
        });
    }
};
