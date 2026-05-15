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
        Schema::create('design_sessions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('environment_id')
                ->nullable()
                ->constrained('environments')
                ->nullOnDelete();

            $table->uuid('public_uuid')->unique();

            $table->string('visitor_name')->nullable();
            $table->string('visitor_email')->nullable();
            $table->string('visitor_phone')->nullable();

            /**
             * draft, lead_created, quoted, closed
             */
            $table->string('status')->default('draft');

            /**
             * Imagen final exportada.
             * Puede ser una ruta en storage o base64 temporal si después lo procesamos.
             */
            $table->string('final_image')->nullable();

            /**
             * Estado completo del configurador.
             * Ejemplo:
             * zonas seleccionadas, materiales, escala, rotación, opacidad.
             */
            $table->json('snapshot')->nullable();

            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamps();

            $table->index('environment_id');
            $table->index('status');
            $table->index('visitor_email');
            $table->index('visitor_phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('design_sessions');
    }
};
