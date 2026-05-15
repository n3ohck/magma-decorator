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
        Schema::create('environments', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('slug')->unique();

            /**
             * Ejemplos:
             * kitchen, bathroom, living_room, commercial, exterior
             */
            $table->string('type')->nullable();

            $table->text('description')->nullable();

            /**
             * Imagen base del ambiente.
             * Ejemplo:
             * environments/kitchen-01/base.jpg
             */
            $table->string('base_image');

            /**
             * Imagen de preview para cards/listado.
             */
            $table->string('preview_image')->nullable();

            /**
             * Overlays opcionales para realismo visual.
             */
            $table->string('shadow_overlay_image')->nullable();
            $table->string('light_overlay_image')->nullable();

            /**
             * Tamaño base del canvas.
             * Todas las máscaras deben tener este mismo tamaño.
             */
            $table->unsignedInteger('canvas_width')->default(1600);
            $table->unsignedInteger('canvas_height')->default(1000);

            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);

            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index(['type', 'is_active']);
            $table->index(['is_active', 'sort_order']);
            $table->index(['is_featured', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('environments');
    }
};
