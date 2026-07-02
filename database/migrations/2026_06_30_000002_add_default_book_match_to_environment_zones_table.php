<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('environment_zones', function (Blueprint $table) {
            $table->boolean('default_book_match')->default(false)->after('supports_perspective');
        });
    }

    public function down(): void
    {
        Schema::table('environment_zones', function (Blueprint $table) {
            $table->dropColumn('default_book_match');
        });
    }
};
