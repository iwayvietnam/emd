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
        Schema::create('restricted_recipients', function (Blueprint $table) {
            $table->id();
            $table->string('recipient')->unique();
            $table->enum('verdict', [
                'REJECT',
                'DEFER',
                'OK',
            ]);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restricted_recipients');
    }
};
