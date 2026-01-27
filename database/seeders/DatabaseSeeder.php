<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin User
        User::factory()->create([
            'name' => 'Paulo Silva', // Personalized for the user
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        // Page Sections
        $sections = ['Hero', 'About', 'Experience', 'Skills', 'Projects', 'Contact'];
        foreach ($sections as $index => $section) {
            \App\Models\PageSection::create([
                'name' => strtolower($section),
                'title' => $section,
                'is_visible' => true,
                'sort_order' => $index,
            ]);
        }
        
        // Sample Skills
        \App\Models\Skill::create(['name' => 'Laravel', 'category' => 'technical', 'proficiency' => 90]);
        \App\Models\Skill::create(['name' => 'Vue.js', 'category' => 'technical', 'proficiency' => 85]);
        \App\Models\Skill::create(['name' => 'Docker', 'category' => 'technical', 'proficiency' => 80]);

        // Sample Experience
        \App\Models\Experience::create([
            'company' => 'Tech Corp', 
            'role' => 'Senior Developer', 
            'start_date' => now()->subYears(2),
            'is_current' => true,
            'description' => 'Leading backend development using Laravel.'
        ]);
    }
}
