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
        Schema::table('domains', function (Blueprint $table) {
            $table->unsignedBigInteger('quota_limit');
            $table->unsignedInteger('quota_period');
            $table->unsignedInteger('rate_limit');
            $table->unsignedInteger('rate_period');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('domains', function (Blueprint $table) {
            $table->dropColumn('quota_limit');
            $table->dropColumn('quota_period');
            $table->dropColumn('rate_limit');
            $table->dropColumn('rate_period');
        });
    }
};
