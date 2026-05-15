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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();

            $table->foreignId('design_session_id')
                ->nullable()
                ->constrained('design_sessions')
                ->nullOnDelete();

            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();

            $table->string('city')->nullable();

            /**
             * Ejemplos:
             * residential, commercial, kitchen, bathroom, exterior
             */
            $table->string('project_type')->nullable();

            /**
             * whatsapp, phone, email
             */
            $table->string('preferred_contact_method')->nullable();

            $table->text('message')->nullable();

            /**
             * new, contacted, quoted, won, lost
             */
            $table->string('status')->default('new');

            $table->timestamps();

            $table->index('design_session_id');
            $table->index('status');
            $table->index('email');
            $table->index('phone');
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
