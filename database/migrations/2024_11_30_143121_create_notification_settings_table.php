<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('notification_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->onDelete('cascade');
            $table->string('metric_type'); // 'views', 'clicks', 'conversion'
            $table->string('condition'); // 'above', 'below'
            $table->decimal('threshold', 10, 2);
            $table->boolean('email_enabled')->default(true);
            $table->boolean('app_enabled')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('notification_settings');
    }
};