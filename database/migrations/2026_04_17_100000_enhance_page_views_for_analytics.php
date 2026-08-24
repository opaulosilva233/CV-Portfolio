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
        if (!Schema::hasTable('page_views')) {
            Schema::create('page_views', function (Blueprint $table) {
                $table->id();
                $table->string('path')->index();
                $table->string('session_id')->nullable()->index();
                $table->string('ip_address')->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamps();
            });
        }

        Schema::table('page_views', function (Blueprint $table) {
            if (!Schema::hasColumn('page_views', 'country')) {
                $table->string('country')->nullable()->after('ip_address');
            }
            if (!Schema::hasColumn('page_views', 'city')) {
                $table->string('city')->nullable()->after('country');
            }
            if (!Schema::hasColumn('page_views', 'region')) {
                $table->string('region')->nullable()->after('city');
            }
            if (!Schema::hasColumn('page_views', 'company')) {
                $table->string('company')->nullable()->after('region');
            }
            if (!Schema::hasColumn('page_views', 'is_recruiter')) {
                $table->boolean('is_recruiter')->default(false)->after('company');
            }
            if (!Schema::hasColumn('page_views', 'visitor_type')) {
                $table->string('visitor_type')->nullable()->after('is_recruiter');
            }
            if (!Schema::hasColumn('page_views', 'referrer')) {
                $table->string('referrer')->nullable()->after('visitor_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('page_views', function (Blueprint $table) {
            $table->dropColumn([
                'country',
                'city',
                'region',
                'company',
                'is_recruiter',
                'visitor_type',
                'referrer',
            ]);
        });
    }
};
