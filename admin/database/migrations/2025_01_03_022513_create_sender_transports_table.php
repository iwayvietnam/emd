<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sender_transports', static function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->index();
            $table->foreignId('transport_id')->index();
            $table->string('sender')->unique();
            $table->string('transport');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sender_transports');
    }
};
