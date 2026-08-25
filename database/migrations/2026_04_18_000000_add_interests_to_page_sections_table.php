<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\PageSection;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!PageSection::where('name', 'interests')->exists()) {
            $maxSortOrder = PageSection::max('sort_order') ?? 0;
            PageSection::create([
                'name' => 'interests',
                'title' => 'Interests',
                'is_visible' => true,
                'sort_order' => $maxSortOrder + 1,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        PageSection::where('name', 'interests')->delete();
    }
};
