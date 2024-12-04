<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ai_feedbacks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('suggestion_id');
            $table->string('suggestion_type');
            $table->enum('feedback_type', ['helpful', 'not_helpful']);
            $table->text('comments')->nullable();
            $table->boolean('applied')->default(false);
            $table->integer('effectiveness_score')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('ai_feedbacks');
    }
};