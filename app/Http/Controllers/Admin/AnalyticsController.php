<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PageView;
use App\Models\SectionEngagement;
use App\Services\AnalyticsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;

class AnalyticsController extends Controller
{
    public function __construct(
        private AnalyticsService $analyticsService
    ) {}

    /**
     * Display the analytics dashboard page.
     */
    public function index(Request $request)
    {
        $days = $request->input('days', 30);
        $analyticsData = $this->analyticsService->getAnalyticsData((int) $days);

        return Inertia::render('Admin/Analytics/Index', [
            'analytics' => $analyticsData,
            'period' => [
                'days' => (int) $days,
                'available_periods' => [7, 14, 30, 60, 90],
            ],
        ]);
    }

    /**
     * Get analytics stats as JSON (for backward compatibility and AJAX calls).
     */
    public function getStats()
    {
        $days = 30;
        $analyticsData = $this->analyticsService->getAnalyticsData($days);

        return response()->json([
            'overview' => $analyticsData['overview'],
            'traffic_timeline' => $analyticsData['traffic_timeline'],
            'section_stats' => $analyticsData['section_engagement'],
            // Backward compatibility fields
            'total_views' => $analyticsData['overview']['total_views'],
            'unique_visitors' => $analyticsData['overview']['unique_visitors'],
            'chart_data' => $analyticsData['traffic_timeline'],
            'total_engagement_seconds' => collect($analyticsData['section_engagement'])->sum('total_seconds'),
            'avg_engagement_per_visitor_seconds' => $analyticsData['overview']['avg_session_duration_seconds'],
            'avg_session_duration_seconds' => $analyticsData['overview']['avg_session_duration_seconds'],
            'most_engaged_section' => $analyticsData['section_engagement']->first(),
        ]);
    }

    public function trackSectionEngagement(Request $request)
    {
        if (!Schema::hasTable('section_engagements')) {
            return response()->noContent();
        }

        $validated = $request->validate([
            'entries' => ['required', 'array', 'min:1', 'max:30'],
            'entries.*.section' => ['required', 'string', 'max:100'],
            'entries.*.duration_seconds' => ['required', 'integer', 'min:1', 'max:3600'],
            'entries.*.path' => ['nullable', 'string', 'max:255'],
        ]);

        $allowedSections = ['about', 'interests', 'skills', 'timeline', 'experience', 'education', 'projects', 'terminal', 'contact'];
        $sessionId = $request->session()->getId();
        $now = now();

        $rows = collect($validated['entries'])
            ->map(function (array $entry) use ($allowedSections, $sessionId, $now) {
                $section = $entry['section'] === 'timeline-mobile' ? 'timeline' : $entry['section'];

                if (!in_array($section, $allowedSections, true)) {
                    return null;
                }

                return [
                    'section' => $section,
                    'path' => $entry['path'] ?? 'home',
                    'session_id' => $sessionId,
                    'duration_seconds' => (int) $entry['duration_seconds'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            })
            ->filter()
            ->values()
            ->all();

        if (!empty($rows)) {
            SectionEngagement::insert($rows);
        }

        return response()->noContent();
    }

    private function formatSectionLabel(string $section): string
    {
        return ucwords(str_replace('-', ' ', $section));
    }

    private function buildEmptyChartData(int $days): array
    {
        $chartData = [];

        for ($i = $days; $i >= 0; $i--) {
            $chartData[] = [
                'date' => Carbon::now()->subDays($i)->format('d/m'),
                'views' => 0,
            ];
        }

        return $chartData;
    }

    private function buildChartDataFromViews(int $days, array $viewsPerDay): array
    {
        $chartData = [];

        for ($i = $days; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');

            $chartData[] = [
                'date' => Carbon::now()->subDays($i)->format('d/m'),
                'views' => $viewsPerDay[$date] ?? 0,
            ];
        }

        return $chartData;
    }
}
