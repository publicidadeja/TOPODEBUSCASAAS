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
    Schema::create('smart_calendars', function (Blueprint $table) {
        $table->id();
        $table->foreignId('business_id')->constrained()->onDelete('cascade');
        $table->string('event_type');
        $table->string('title');
        $table->text('suggestion');
        $table->timestamp('start_date')->nullable();  // Adicionado nullable()
        $table->timestamp('end_date')->nullable();    // Adicionado nullable()
        $table->string('status')->default('pending');
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
