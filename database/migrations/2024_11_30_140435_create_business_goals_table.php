<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('business_goals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->integer('year');
            $table->integer('month');
            $table->integer('monthly_views_goal');
            $table->integer('monthly_clicks_goal');
            $table->decimal('conversion_rate_goal', 5, 2);
            $table->timestamps();

            $table->foreign('business_id')
                  ->references('id')
                  ->on('businesses')
                  ->onDelete('cascade');

            $table->unique(['business_id', 'year', 'month']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('business_goals');
    }
};