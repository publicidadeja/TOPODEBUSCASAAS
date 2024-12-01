<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('calendar_events', function (Blueprint $table) {
        $table->id();
        $table->foreignId('business_id')->constrained()->onDelete('cascade');
        $table->string('title');
        $table->string('event_type');
        $table->datetime('start_date');
        $table->datetime('end_date')->nullable();
        $table->text('suggestion')->nullable();
        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('smart_calendars');
    }
};
