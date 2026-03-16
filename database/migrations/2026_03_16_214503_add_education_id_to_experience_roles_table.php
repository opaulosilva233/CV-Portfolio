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
        Schema::table('experience_roles', function (Blueprint $table) {
            $table->foreignId('education_id')->nullable()->constrained('education')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('experience_roles', function (Blueprint $table) {
            $table->dropForeign(['education_id']);
            $table->dropColumn('education_id');
        });
    }
};
