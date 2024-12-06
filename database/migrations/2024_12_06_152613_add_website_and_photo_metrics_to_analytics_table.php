<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('business_analytics', function (Blueprint $table) {
            $table->integer('website_visits')->default(0)->after('calls');
            $table->integer('photo_views')->default(0)->after('website_visits');
        });
    }

    public function down()
    {
        Schema::table('business_analytics', function (Blueprint $table) {
            $table->dropColumn(['website_visits', 'photo_views']);
        });
    }
};