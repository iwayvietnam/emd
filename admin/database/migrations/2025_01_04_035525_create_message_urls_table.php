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
        Schema::create('message_urls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->index();
            $table->uuid('hash')->unique();
            $table->string('url', 2048);
            $table->unsignedInteger('click_count')->default(0);
            $table->timestamp('last_clicked')->nullable()->default(null);
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
        Schema::dropIfExists('message_urls');
    }
};
