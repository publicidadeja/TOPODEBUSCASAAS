<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('segment');
            $table->string('address');
            $table->string('phone');
            $table->string('website')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->string('status')->default('active');
            $table->decimal('rating', 3, 1)->nullable();
            $table->integer('review_count')->default(0);
            $table->json('settings')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('businesses');
    }
};