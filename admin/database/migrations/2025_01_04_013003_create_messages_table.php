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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->index();
            $table->uuid('hash')->unique();
            $table->string('from_name');
            $table->string('from_email')->index();
            $table->string('reply_to');
            $table->string('recipient');
            $table->text('headers')->nullable()->default(null);
            $table->string('message_id');
            $table->text('subject');
            $table->longText('content')->nullable()->default(null);
            $table->ipAddress('ip_address')->index();
            $table->unsignedInteger('open_count')->default(0);
            $table->unsignedInteger('click_count')->default(0);
            $table->timestamp('sent_at')->index()->nullable()->default(null);
            $table->timestamp('last_opened')->index()->nullable()->default(null);
            $table->timestamp('last_clicked')->index()->nullable()->default(null);
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
        Schema::dropIfExists('messages');
    }
};
