<?php

namespace App\Http\Middleware;

use App\Models\PageView;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class TrackPageView
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // We only track successful GET requests to the frontend
        if ($response->getStatusCode() === 200 && $request->isMethod('GET') && !$request->isXmlHttpRequest()) {
            $path = $request->path();
            
            // Exclude admin, system and auth paths
            $excludedPaths = ['admin', 'login', 'register', 'dashboard', 'user', 'up', '_', 'api'];
            
            if (!Str::startsWith($path, $excludedPaths)) {
                try {
                    PageView::create([
                        'path' => $path === '/' ? 'home' : $path,
                        'session_id' => $request->session()->getId(),
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                    ]);
                } catch (\Exception $e) {
                    // Silently fail to not break the user experience
                    logger()->error('Failed to log page view: ' . $e->getMessage());
                }
            }
        }

        return $response;
    }
}
