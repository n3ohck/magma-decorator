<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('environment_zone_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('environment_id')->constrained('environments')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('color', 20)->default('#CC1A1A');
            $table->string('icon')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['environment_id', 'slug'], 'ezg_env_slug_unique');
            $table->index(['environment_id', 'is_active', 'sort_order'], 'ezg_env_active_sort_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('environment_zone_groups');
    }
};
