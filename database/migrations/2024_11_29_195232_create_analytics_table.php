<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    if (!Schema::hasTable('business_analytics')) {
        Schema::create('business_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->integer('views')->default(0);
            $table->integer('clicks')->default(0);
            $table->integer('calls')->default(0);
            $table->json('devices')->nullable();
            $table->json('user_locations')->nullable();
            $table->json('search_keywords')->nullable();
            $table->timestamps();
        });
    }
}

    public function down()
    {
        Schema::dropIfExists('business_analytics');
    }
};