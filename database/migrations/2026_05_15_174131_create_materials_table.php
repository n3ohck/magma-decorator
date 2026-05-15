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
        Schema::create('materials', function (Blueprint $table) {
            $table->id();

            $table->foreignId('material_category_id')
                ->constrained('material_categories')
                ->cascadeOnDelete();

            $table->string('name');
            $table->string('slug')->unique();

            $table->string('sku')->nullable();
            $table->string('origin_country')->nullable();

            $table->string('finish')->nullable();
            $table->string('base_color')->nullable();

            $table->text('short_description')->nullable();
            $table->longText('description')->nullable();

            /**
             * Ruta relativa dentro de storage/app/public.
             * Ejemplo:
             * materials/textures/calacatta-gold.jpg
             */
            $table->string('texture_image');
            $table->string('thumbnail_image')->nullable();

            /**
             * Valores default para render visual.
             */
            $table->decimal('default_scale', 8, 2)->default(1);
            $table->decimal('default_rotation', 8, 2)->default(0);
            $table->decimal('default_opacity', 5, 2)->default(1);

            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);

            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index('material_category_id');
            $table->index(['is_active', 'sort_order']);
            $table->index(['is_featured', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materials');
    }
};
