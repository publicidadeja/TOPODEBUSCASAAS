<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('business_analytics', function (Blueprint $table) {
            $table->decimal('rating', 3, 1)->nullable()->after('calls');
        });
    }

    public function down()
    {
        Schema::table('business_analytics', function (Blueprint $table) {
            $table->dropColumn('rating');
        });
    }
};