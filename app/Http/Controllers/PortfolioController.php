<?php

namespace App\Http\Controllers;

use App\Models\Experience;
use App\Models\Project;
use App\Models\SiteSetting;
use App\Models\Skill;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PortfolioController extends Controller
{
    public function index()
    {
        return Inertia::render('Welcome', [
            'hero' => [
                'name' => SiteSetting::getValue('name', 'My Name'),
                'title' => SiteSetting::getValue('job_title', 'Full Stack Developer'),
                'bio' => SiteSetting::getValue('bio', 'Welcome to my portfolio.'),
                'image' => SiteSetting::getValue('hero_image'),
            ],
            'projects' => Project::where('is_featured', true)->orderBy('sort_order')->get(),
            'skills' => Skill::orderBy('sort_order')->get()->groupBy('category'),
            'experiences' => Experience::orderBy('start_date', 'desc')->get(),
            'socials' => SiteSetting::getValue('social_links', []),
        ]);
    }
}
