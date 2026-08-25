<?php

namespace Tests\Feature;

use App\Models\PageSection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageSectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_interests_section_exists_and_can_be_reordered(): void
    {
        $user = User::factory()->create();

        PageSection::create(['name' => 'hero', 'title' => 'Hero', 'is_visible' => true, 'sort_order' => 0]);
        PageSection::create(['name' => 'about', 'title' => 'About', 'is_visible' => true, 'sort_order' => 1]);
        $interests = PageSection::create(['name' => 'interests', 'title' => 'Interests', 'is_visible' => true, 'sort_order' => 2]);
        $skills = PageSection::create(['name' => 'skills', 'title' => 'Skills', 'is_visible' => true, 'sort_order' => 3]);

        $response = $this->actingAs($user)->get('/admin/sections');
        $response->assertStatus(200);

        $reorderResponse = $this->actingAs($user)->post('/admin/sections/reorder', [
            'ids' => [$skills->id, $interests->id, 1, 2]
        ]);
        $reorderResponse->assertStatus(200);

        $this->assertEquals(0, $skills->fresh()->sort_order);
        $this->assertEquals(1, $interests->fresh()->sort_order);
    }

    public function test_homepage_loads_sections_successfully(): void
    {
        PageSection::create(['name' => 'hero', 'title' => 'Hero', 'is_visible' => true, 'sort_order' => 0]);
        PageSection::create(['name' => 'interests', 'title' => 'Interests', 'is_visible' => true, 'sort_order' => 1]);

        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function test_homepage_sections_passed_in_reordered_sort_order(): void
    {
        $user = User::factory()->create();

        PageSection::query()->delete();

        $sec1 = PageSection::create(['name' => 'hero', 'title' => 'Hero', 'is_visible' => true, 'sort_order' => 0]);
        $sec2 = PageSection::create(['name' => 'projects', 'title' => 'Projects', 'is_visible' => true, 'sort_order' => 1]);
        $sec3 = PageSection::create(['name' => 'skills', 'title' => 'Skills', 'is_visible' => true, 'sort_order' => 2]);

        $reorderResponse = $this->actingAs($user)->post('/admin/sections/reorder', [
            'ids' => [$sec3->id, $sec2->id, $sec1->id]
        ]);
        $reorderResponse->assertStatus(200);

        $response = $this->get('/');
        $response->assertStatus(200);

        $sections = $response->viewData('page')['props']['sections'];
        $sectionNames = array_column($sections, 'name');

        $this::assertEquals(['skills', 'projects', 'hero'], $sectionNames);
    }
}
