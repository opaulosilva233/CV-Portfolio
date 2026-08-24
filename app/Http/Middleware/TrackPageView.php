<?php

namespace App\Http\Middleware;

use App\Models\PageView;
use App\Services\AnalyticsService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class TrackPageView
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ?AnalyticsService $analyticsService = null): Response
    {
        $response = $next($request);

        // We only track successful GET requests to the frontend
        if ($response->getStatusCode() === 200 && $request->isMethod('GET') && !$request->isXmlHttpRequest()) {
            $path = $request->path();
            
            // Exclude admin, system and auth paths
            $excludedPaths = ['admin', 'login', 'register', 'dashboard', 'user', 'up', '_', 'api'];
            
            if (!Str::startsWith($path, $excludedPaths)) {
                try {
                    $data = [
                        'path' => $path === '/' ? 'home' : $path,
                        'session_id' => $request->session()->getId(),
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'referrer' => $request->headers->get('referer'),
                    ];

                    // Enrich with geolocation and company data if AnalyticsService is available
                    if ($analyticsService) {
                        $enrichedData = $analyticsService->enrichPageView($data);
                        PageView::create($enrichedData);
                    } else {
                        // Fallback: create basic record without enrichment
                        PageView::create($data);
                    }
                } catch (\Exception $e) {
                    // Silently fail to not break the user experience
                    Log::warning('Failed to log page view: ' . $e->getMessage());
                }
            }
        }

        return $response;
    }
}
