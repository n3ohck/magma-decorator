<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('environment_zone_groups', function (Blueprint $table) {
            $table->float('label_x')->nullable()->after('icon');
            $table->float('label_y')->nullable()->after('label_x');
        });
    }

    public function down(): void
    {
        Schema::table('environment_zone_groups', function (Blueprint $table) {
            $table->dropColumn(['label_x', 'label_y']);
        });
    }
};
