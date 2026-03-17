<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PageView;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function getStats()
    {
        $days = 30;
        $startDate = Carbon::now()->subDays($days);

        // Total views last 30 days
        $totalViews = PageView::where('created_at', '>=', $startDate)->count();

        // Unique visitors last 30 days
        $uniqueVisitors = PageView::where('created_at', '>=', $startDate)
            ->distinct('session_id')
            ->count('session_id');

        // Views per day for chart
        $viewsPerDay = PageView::where('created_at', '>=', $startDate)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date')
            ->toArray();

        // Fill missing days with 0
        $chartData = [];
        for ($i = $days; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $chartData[] = [
                'date' => Carbon::now()->subDays($i)->format('d/m'),
                'views' => $viewsPerDay[$date] ?? 0
            ];
        }

        // Top pages
        $topPages = PageView::select('path', DB::raw('count(*) as count'))
            ->where('created_at', '>=', $startDate)
            ->groupBy('path')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'total_views' => $totalViews,
            'unique_visitors' => $uniqueVisitors,
            'chart_data' => $chartData,
            'top_pages' => $topPages,
        ]);
    }
}
