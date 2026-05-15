<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('design_session_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('design_session_id')
                ->constrained('design_sessions')
                ->cascadeOnDelete();

            $table->foreignId('environment_zone_id')
                ->constrained('environment_zones')
                ->cascadeOnDelete();

            $table->foreignId('material_id')
                ->nullable()
                ->constrained('materials')
                ->nullOnDelete();

            $table->decimal('texture_scale', 8, 2)->default(1);
            $table->decimal('texture_rotation', 8, 2)->default(0);
            $table->decimal('opacity', 5, 2)->default(1);

            /**
             * Config extra por zona:
             * brillo, contraste, posición X/Y, modo de mezcla, etc.
             */
            $table->json('settings')->nullable();

            $table->timestamps();

            $table->unique(
                ['design_session_id', 'environment_zone_id'],
                'dsi_session_zone_unique'
            );

            $table->index('design_session_id', 'dsi_session_idx');
            $table->index('environment_zone_id', 'dsi_zone_idx');
            $table->index('material_id', 'dsi_material_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('design_session_items');
    }
};
