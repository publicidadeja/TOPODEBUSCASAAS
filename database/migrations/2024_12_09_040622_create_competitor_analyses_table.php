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
    Schema::create('competitor_analyses', function (Blueprint $table) {
        $table->id();
        $table->foreignId('business_id')->constrained();
        $table->json('data');
        $table->timestamp('analyzed_at');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('competitor_analyses');
    }
};
