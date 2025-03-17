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
        Schema::create('mail_servers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->ipAddress('ip_address');
            $table->string('ssh_user');
            $table->unsignedSmallInteger('ssh_port');
            $table->text('ssh_private_key')->nullable();
            $table->text('ssh_public_key')->nullable();
            $table->string('sudo_password', 1024)->nullable();
            $table->timestamps();
            $table->unique(['ip_address', 'ssh_user']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mail_servers');
    }
};
