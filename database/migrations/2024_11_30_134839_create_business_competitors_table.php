<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('business_competitors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->foreignId('competitor_id')->constrained('businesses')->onDelete('cascade');
            $table->timestamps();
            
            // Evitar duplicatas
            $table->unique(['business_id', 'competitor_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('business_competitors');
    }
};