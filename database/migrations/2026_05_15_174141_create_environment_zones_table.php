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
        Schema::create('environment_zones', function (Blueprint $table) {
            $table->id();

            $table->foreignId('environment_id')
                ->constrained('environments')
                ->cascadeOnDelete();

            $table->string('name');
            $table->string('slug');

            /**
             * Ejemplos:
             * floor, wall, countertop, backsplash, island, facade
             */
            $table->string('zone_type')->nullable();

            /**
             * Máscara PNG de la zona.
             * Ejemplo:
             * environments/kitchen-01/masks/floor.png
             */
            $table->string('mask_image')->nullable();

            /**
             * Para una fase posterior donde el admin dibuje zonas.
             */
            $table->json('polygon_points')->nullable();

            /**
             * Valores default para textura por zona.
             */
            $table->decimal('default_texture_scale', 8, 2)->default(1);
            $table->decimal('default_texture_rotation', 8, 2)->default(0);
            $table->decimal('default_opacity', 5, 2)->default(1);

            /**
             * Para una fase posterior con perspectiva avanzada.
             */
            $table->boolean('supports_perspective')->default(false);
            $table->json('perspective_points')->nullable();

            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->unique(['environment_id', 'slug']);

            $table->index('environment_id');
            $table->index(['zone_type', 'is_active']);
            $table->index(['is_active', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('environment_zones');
    }
};
