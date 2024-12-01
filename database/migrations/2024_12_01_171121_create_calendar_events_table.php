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
        Schema::create('calendar_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('event_type');
            $table->datetime('start_date');
            $table->datetime('end_date')->nullable();
            $table->text('suggestion')->nullable();
            $table->string('status')->default('active'); // Para controlar o estado das sugestões
            $table->string('color')->nullable(); // Para armazenar a cor do evento
            $table->text('description')->nullable(); // Para armazenar a descrição do evento
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calendar_events');
    }
};