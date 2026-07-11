<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Cuando es true, el ambiente ofrece TODOS los materiales activos (comportamiento
        // por defecto, retrocompatible). Cuando es false, sólo los de la tabla pivote.
        Schema::table('environments', function (Blueprint $table) {
            $table->boolean('all_materials')->default(true)->after('is_active');
        });

        Schema::create('environment_material', function (Blueprint $table) {
            $table->id();
            $table->foreignId('environment_id')->constrained('environments')->cascadeOnDelete();
            $table->foreignId('material_id')->constrained('materials')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['environment_id', 'material_id'], 'env_mat_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('environment_material');

        Schema::table('environments', function (Blueprint $table) {
            $table->dropColumn('all_materials');
        });
    }
};
