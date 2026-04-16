<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PageView;
use App\Models\SectionEngagement;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class AnalyticsController extends Controller
{
    public function getStats()
    {
        $days = 30;
        $startDate = Carbon::now()->subDays($days);
        $chartData = $this->buildEmptyChartData($days);

        $totalViews = 0;
        $uniqueVisitors = 0;
        $totalEngagementSeconds = 0;
        $averageEngagementPerVisitor = 0;
        $averageSessionDuration = 0;
        $sectionStats = collect();

        try {
            if (Schema::hasTable('page_views')) {
                $totalViews = PageView::where('created_at', '>=', $startDate)->count();

                $uniqueVisitors = PageView::where('created_at', '>=', $startDate)
                    ->distinct('session_id')
                    ->count('session_id');

                $viewsPerDay = PageView::where('created_at', '>=', $startDate)
                    ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get()
                    ->pluck('count', 'date')
                    ->toArray();

                $chartData = $this->buildChartDataFromViews($days, $viewsPerDay);
            }

            if (Schema::hasTable('section_engagements')) {
                $engagementQuery = SectionEngagement::where('created_at', '>=', $startDate);

                $totalEngagementSeconds = (int) $engagementQuery->sum('duration_seconds');

                $averageEngagementPerVisitor = $uniqueVisitors > 0
                    ? round($totalEngagementSeconds / $uniqueVisitors)
                    : 0;

                $averageSessionDuration = (int) SectionEngagement::where('created_at', '>=', $startDate)
                    ->select('session_id', DB::raw('SUM(duration_seconds) as session_duration'))
                    ->groupBy('session_id')
                    ->get()
                    ->avg('session_duration');

                $sectionStats = SectionEngagement::where('created_at', '>=', $startDate)
                    ->select(
                        'section',
                        DB::raw('SUM(duration_seconds) as total_seconds'),
                        DB::raw('AVG(duration_seconds) as average_seconds'),
                        DB::raw('COUNT(*) as interactions'),
                        DB::raw('COUNT(DISTINCT session_id) as unique_visitors')
                    )
                    ->groupBy('section')
                    ->orderByDesc('total_seconds')
                    ->get()
                    ->map(function ($item) {
                        return [
                            'section' => $item->section,
                            'label' => $this->formatSectionLabel($item->section),
                            'total_seconds' => (int) $item->total_seconds,
                            'average_seconds' => (int) round($item->average_seconds),
                            'interactions' => (int) $item->interactions,
                            'unique_visitors' => (int) $item->unique_visitors,
                        ];
                    })
                    ->values();
            }
        } catch (\Throwable $e) {
            Log::warning('Analytics stats fallback used: ' . $e->getMessage());
        }

        return response()->json([
            'total_views' => $totalViews,
            'unique_visitors' => $uniqueVisitors,
            'chart_data' => $chartData,
            'total_engagement_seconds' => $totalEngagementSeconds,
            'avg_engagement_per_visitor_seconds' => $averageEngagementPerVisitor,
            'avg_session_duration_seconds' => $averageSessionDuration,
            'most_engaged_section' => $sectionStats->first(),
            'section_stats' => $sectionStats,
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
