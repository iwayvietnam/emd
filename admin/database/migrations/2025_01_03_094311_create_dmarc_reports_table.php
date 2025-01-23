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
        Schema::create('dmarc_reports', function (Blueprint $table) {
            $table->id();
            $table->string('report_id');
            $table->string('org_name')->index();
            $table->string('org_email')->nullable();
            $table->string('extra_contact')->nullable();
            $table->timestamp('date_begin')->nullable()->index();
            $table->timestamp('date_end')->nullable()->index();
            $table->string('domain')->index();
            $table->string('adkim', 32);
            $table->string('aspf', 32);
            $table->string('policy', 32);
            $table->string('subdomain_policy', 32);
            $table->unsignedTinyInteger('percentage')->default(0);
            $table->boolean('is_forensic')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dmarc_reports');
    }
};
