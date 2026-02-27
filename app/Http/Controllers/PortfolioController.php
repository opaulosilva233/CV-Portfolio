<?php

namespace App\Http\Controllers;

use App\Models\Experience;
use App\Models\Project;
use App\Models\SiteSetting;
use App\Models\Skill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class PortfolioController extends Controller
{
    public function index()
    {
        // Cache portfolio data for better performance
        $projects = Cache::remember('portfolio_projects', 3600, function () {
            return Project::where('is_featured', true)->orderBy('sort_order')->get();
        });

        $skills = Cache::remember('portfolio_skills', 3600, function () {
            return Skill::orderBy('sort_order')->get()->groupBy('category');
        });

        $experiences = Cache::remember('portfolio_experiences', 3600, function () {
            return Experience::with(['roles' => function ($q) {
                $q->orderBy('start_date', 'desc');
            }])->orderBy('sort_order')->get()->sortByDesc(function ($exp) {
                return $exp->roles->max('start_date');
            })->values();
        });

        $educations = Cache::remember('portfolio_educations', 3600, function () {
            return \App\Models\Education::orderBy('start_date', 'desc')->get();
        });

        return Inertia::render('Welcome', [
            'hero' => [
                'name' => SiteSetting::getValue('name', 'My Name'),
                'title' => SiteSetting::getValue('job_title', 'Full Stack Developer'),
                'bio' => SiteSetting::getValue('bio', 'Welcome to my portfolio.'),
                'image' => SiteSetting::getValue('hero_image'),
            ],
            'projects' => $projects,
            'skills' => $skills,
            'experiences' => $experiences,
            'educations' => $educations,
            'socials' => SiteSetting::getValue('social_links', []),
        ]);
    }
}
