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
        Schema::create('dmarc_report_records', function (Blueprint $table) {
            $table->id();
            $table->string('report_id')->index();
            $table->string('source_ip', 64);
            $table->unsignedSmallInteger('count');
            $table->string('header_from');
            $table->string('envelope_from')->nullable();
            $table->string('envelope_to')->nullable();
            $table->string('disposition');
            $table->string('dkim', 32);
            $table->string('spf', 32);
            $table->text('reason')->nullable();
            $table->string('dkim_domain')->nullable();
            $table->string('dkim_selector')->nullable();
            $table->string('dkim_result', 32)->nullable();
            $table->string('spf_domain')->nullable();
            $table->string('spf_result', 32)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dmarc_report_records');
    }
};
