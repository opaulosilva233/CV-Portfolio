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
        Schema::create('section_engagements', function (Blueprint $table) {
            $table->id();
            $table->string('section')->index();
            $table->string('path')->nullable()->index();
            $table->string('session_id')->nullable()->index();
            $table->unsignedInteger('duration_seconds');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('section_engagements');
    }
};
