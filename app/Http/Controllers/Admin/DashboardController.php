<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Education;
use App\Models\Experience;
use App\Models\Project;
use App\Models\Skill;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'projects' => Project::count(),
            'skills' => Skill::count(),
            'experiences' => Experience::count(),
            'education' => Education::count(),
        ];

        // Fetch recent activity (latest updated records)
        $recentActivity = collect()
            ->concat($this->getRecent(Project::class, 'Project'))
            ->concat($this->getRecent(Experience::class, 'Experience'))
            ->concat($this->getRecent(Skill::class, 'Skill'))
            ->concat($this->getRecent(Education::class, 'Education'))
            ->sortByDesc('updated_at')
            ->take(5)
            ->values();

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'recentActivity' => $recentActivity,
        ]);
    }

    private function getRecent($model, $type)
    {
        return $model::latest('updated_at')
            ->take(5)
            ->get()
            ->map(function ($item) use ($type) {
                return [
                    'id' => $item->id,
                    'type' => $type,
                    'title' => $item->title ?? $item->name ?? $item->company ?? $item->institution ?? 'N/A',
                    'updated_at' => $item->updated_at,
                ];
            });
    }
}
