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
        Schema::create('x509_certificates', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('domain_id')->index();
            $table->unsignedInteger('private_key_id')->index();
            $table->unsignedInteger('signing_request_id')->index();
            $table->string('serial_number', 64)->unique();
            $table->string('subject_dn', 256);
            $table->string('issuer_dn', 256);
            $table->timestamp('not_before')->nullable();
            $table->timestamp('not_after')->nullable();
            $table->text('certificate_data')->nullable();
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
        Schema::dropIfExists('x509_certificates');
    }
};
