<?php

namespace App\Http\Controllers;

use App\Models\Experience;
use App\Models\Project;
use App\Models\SiteSetting;
use App\Models\Skill;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class PortfolioController extends Controller
{
    public function index()
    {
        // Cache portfolio data for better performance
        $projects = Cache::remember('portfolio_projects', 3600, function () {
            return Project::with(['translations', 'skills'])->where('is_featured', true)->orderBy('completed_at', 'desc')->get();
        });

        $skills = Cache::remember('portfolio_skills', 3600, function () {
            return Skill::with('translations')->orderBy('sort_order')->get()->groupBy('category');
        });

        $experiences = Cache::remember('portfolio_experiences', 3600, function () {
            return Experience::with(['translations', 'roles' => function ($q) {
                $q->with(['translations', 'education.translations'])->orderBy('start_date', 'desc');
            }])->orderBy('sort_order')->get()->sortByDesc(function ($exp) {
                return $exp->roles->max('start_date');
            })->values();
        });

        $educations = Cache::remember('portfolio_educations', 3600, function () {
            return \App\Models\Education::with('translations')->orderBy('start_date', 'desc')->get();
        });

        $interests = Cache::remember('portfolio_interests', 3600, function () {
            return \App\Models\Interest::where('is_active', true)->orderBy('order')->get()->groupBy('category');
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
            'interests' => $interests,
            'experiences' => $experiences,
            'educations' => $educations,
            'socials' => array_filter([
                'GitHub' => SiteSetting::getValue('social_github'),
                'LinkedIn' => SiteSetting::getValue('social_linkedin'),
                'Twitter' => SiteSetting::getValue('social_twitter'),
                'Instagram' => SiteSetting::getValue('social_instagram'),
            ]),
            'footer_text' => SiteSetting::getValue('footer_text', 'System Online.'),
            'contact_email' => SiteSetting::getValue('contact_email'),
            'contact_phone' => SiteSetting::getValue('contact_phone'),
            'contact_address' => SiteSetting::getValue('contact_address'),
            'resume_url' => SiteSetting::getValue('resume_url'),
            'seo' => [
                'title' => SiteSetting::getValue('seo_title', 'Portfolio'),
                'description' => SiteSetting::getValue('seo_description', ''),
                'keywords' => SiteSetting::getValue('seo_keywords', ''),
            ]
        ]);
    }

    public function storeMessage(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string|min:10',
        ]);

        Message::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'subject' => $validated['subject'] ?? 'Website Contact',
            'message' => $validated['message'],
            'status' => 'unread',
        ]);

        return redirect()->back()->with('success', 'Message sent successfully!');
    }
}
