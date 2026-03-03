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
        // Work Experience
        Schema::create('experiences', function (Blueprint $table) {
            $table->id();
            $table->string('company');
            $table->string('role');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_current')->default(false);
            $table->text('description')->nullable();
            $table->string('location')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Skills
        Schema::create('skills', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category')->default('technical'); // technical, soft, language
            $table->integer('proficiency')->default(0); // 0-100 or 1-5 level
            $table->string('icon')->nullable(); // For fontawesome or svg path
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Projects (Portfolio)
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->string('image_url')->nullable(); // Main cover image
            $table->string('project_url')->nullable();
            $table->string('github_url')->nullable();
            $table->json('tech_stack')->nullable(); // Store tags like ["Laravel", "Vue"]
            $table->boolean('is_featured')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
        
        // Page Sections (For Dynamic Order/Toggling)
        Schema::create('page_sections', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., 'hero', 'about', 'experience', 'projects'
            $table->string('title')->nullable(); // Overridable title
            $table->boolean('is_visible')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_sections');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('skills');
        Schema::dropIfExists('experiences');
    }
};
