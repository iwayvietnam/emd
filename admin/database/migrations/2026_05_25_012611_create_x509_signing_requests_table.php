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
        Schema::create('x509_signing_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('domain_id')->index();
            $table->unsignedInteger('private_key_id')->index();
            $table->string('cn')->index();
            $table->string('country')->nullable();
            $table->string('state')->nullable();
            $table->string('locality')->nullable();
            $table->string('organization')->nullable();
            $table->string('organization_unit')->nullable();
            $table->text('csr_data')->nullable();
            $table->unsignedInteger('created_by')->default(0)->index();
            $table->unsignedInteger('updated_by')->default(0)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('x509_signing_requests');
    }
};
