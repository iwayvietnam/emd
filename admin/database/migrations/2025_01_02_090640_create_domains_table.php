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
        Schema::create('domains', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('email')->unique();
            $table->text('description')->nullable();
            $table->text('dns_mx_record')->nullable();
            $table->text('dns_dmarc_record')->nullable();
            $table->text('dns_ptr_record')->nullable();
            $table->text('dns_spf_record')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('domains');
    }
};
