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
        // 1. Move existing data to experience_roles
        $experiences = \Illuminate\Support\Facades\DB::table('experiences')->get();

        foreach ($experiences as $experience) {
            \Illuminate\Support\Facades\DB::table('experience_roles')->insert([
                'experience_id' => $experience->id,
                'role' => $experience->role,
                'start_date' => $experience->start_date,
                'end_date' => $experience->end_date,
                'is_current' => $experience->is_current,
                'description' => $experience->description,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 2. Drop old columns from experiences table
        Schema::table('experiences', function (Blueprint $table) {
            $table->dropColumn(['role', 'start_date', 'end_date', 'is_current', 'description']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('experiences', function (Blueprint $table) {
            $table->string('role')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_current')->default(false);
            $table->text('description')->nullable();
        });

        // 3. Move data back
        $roles = \Illuminate\Support\Facades\DB::table('experience_roles')->get();
        // Since one experience could now have multiple roles, the down migration is lossy.
        // We will just pick the latest role to restore if there are multiple.
        foreach ($roles as $role) {
            \Illuminate\Support\Facades\DB::table('experiences')
                ->where('id', $role->experience_id)
                ->update([
                    'role' => $role->role,
                    'start_date' => $role->start_date,
                    'end_date' => $role->end_date,
                    'is_current' => $role->is_current,
                    'description' => $role->description,
                ]);
        }
    }
};
