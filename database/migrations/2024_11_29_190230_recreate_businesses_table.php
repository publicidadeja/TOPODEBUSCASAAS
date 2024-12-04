<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Primeiro, dropa a tabela se ela existir
        Schema::dropIfExists('business_hours');
        Schema::dropIfExists('automated_posts');
        Schema::dropIfExists('business_analytics');
        Schema::dropIfExists('businesses');

        // Recria a tabela com a estrutura correta
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('segment');
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('google_business_id')->nullable();
            $table->text('description')->nullable();
            $table->string('website')->nullable();
            $table->timestamps();
        });

        // Recria as tabelas dependentes
        Schema::create('business_hours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->string('day_of_week');
            $table->time('opening_time');
            $table->time('closing_time');
            $table->boolean('is_closed')->default(false);
            $table->boolean('is_holiday')->default(false);
            $table->timestamps();
        });

        Schema::create('automated_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('content');
            $table->string('type');
            $table->datetime('scheduled_for');
            $table->boolean('is_posted')->default(false);
            $table->timestamps();
        });

        Schema::create('business_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->integer('views')->default(0);
            $table->integer('clicks')->default(0);
            $table->integer('calls')->default(0);
            $table->json('search_keywords')->nullable();
            $table->date('date');
            $table->timestamps();
        });
    }

    public function down()
{
    Schema::dropIfExists('holiday_hours');
    Schema::dropIfExists('calendar_events');
    Schema::dropIfExists('business_analytics');
    Schema::dropIfExists('business_hours');
    Schema::dropIfExists('automated_posts');
    Schema::dropIfExists('business_competitors');
    Schema::dropIfExists('business_goals');
    Schema::dropIfExists('notification_settings');
    Schema::dropIfExists('notifications');
    Schema::dropIfExists('businesses');
}
};