<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Por defecto true: el muro/grupo arranca con book match activo (comportamiento
        // actual). El admin puede desactivarlo por grupo.
        Schema::table('environment_zone_groups', function (Blueprint $table) {
            $table->boolean('default_book_match')->default(true)->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('environment_zone_groups', function (Blueprint $table) {
            $table->dropColumn('default_book_match');
        });
    }
};
