<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('message_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->index();
            $table->string('user_agent', 2048);
            $table->ipAddress('ip_address');
            $table->timestamp('opened_at')->nullable()->default(null);
            $table->timestamp('clicked_at')->nullable()->default(null);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('message_devices');
    }
};
