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
        Schema::create('message_failures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->index();
            $table->string('severity')->nullable()->default(null);
            $table->mediumText('description')->nullable()->default(null);
            $table->timestamp('failed_at')->nullable()->default(null);
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
        Schema::dropIfExists('message_failures');
    }
};
