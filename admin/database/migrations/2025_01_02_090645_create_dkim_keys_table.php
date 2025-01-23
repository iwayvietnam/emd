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
        Schema::create('dkim_keys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('domain_id')->index();
            $table->string('domain');
            $table->string('selector');
            $table->mediumInteger('key_bits');
            $table->text('private_key')->nullable();
            $table->text('dns_record')->nullable();
            $table->timestamps();
            $table->unique(['domain_id', 'selector']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dkim_keys');
    }
};
