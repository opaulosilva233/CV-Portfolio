<?php

namespace App\Services;

use App\Models\PageView;
use App\Models\SectionEngagement;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AnalyticsService
{
    /**
     * Get comprehensive analytics data for the specified period.
     */
    public function getAnalyticsData(int $days = 30): array
    {
        $startDate = Carbon::now()->subDays($days);

        return [
            'overview' => $this->getOverviewStats($startDate),
            'traffic_timeline' => $this->getTrafficTimeline($startDate, $days),
            'geographic_data' => $this->getGeographicData($startDate),
            'visitor_types' => $this->getVisitorTypes($startDate),
            'company_data' => $this->getTopCompanies($startDate),
            'section_engagement' => $this->getSectionEngagement($startDate),
            'referrer_data' => $this->getReferrerData($startDate),
        ];
    }

    /**
     * Get overview statistics.
     */
    private function getOverviewStats(Carbon $startDate): array
    {
        $query = PageView::where('created_at', '>=', $startDate);

        $totalViews = $query->count();
        $uniqueVisitors = (clone $query)->distinct('session_id')->count('session_id');
        
        $recruiterVisits = (clone $query)->where('is_recruiter', true)->count();
        $recruiterPercentage = $uniqueVisitors > 0 
            ? round(($recruiterVisits / $uniqueVisitors) * 100, 1) 
            : 0;

        $countriesCount = (clone $query)->distinct('country')->count('country');
        $companiesCount = (clone $query)->whereNotNull('company')->distinct('company')->count('company');

        // Average session duration from section engagements
        $avgSessionDuration = SectionEngagement::where('created_at', '>=', $startDate)
            ->select('session_id', DB::raw('SUM(duration_seconds) as session_duration'))
            ->groupBy('session_id')
            ->get()
            ->avg('session_duration') ?? 0;

        return [
            'total_views' => $totalViews,
            'unique_visitors' => $uniqueVisitors,
            'recruiter_visits' => $recruiterVisits,
            'recruiter_percentage' => $recruiterPercentage,
            'countries_count' => $countriesCount,
            'companies_count' => $companiesCount,
            'avg_session_duration_seconds' => round($avgSessionDuration, 0),
        ];
    }

    /**
     * Get traffic data over time for charts.
     */
    private function getTrafficTimeline(Carbon $startDate, int $days): array
    {
        $viewsPerDay = PageView::where('created_at', '>=', $startDate)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date')
            ->toArray();

        $uniquePerDay = PageView::where('created_at', '>=', $startDate)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(DISTINCT session_id) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date')
            ->toArray();

        $timeline = [];
        for ($i = $days; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $displayDate = Carbon::now()->subDays($i)->format('d/m');

            $timeline[] = [
                'date' => $displayDate,
                'views' => $viewsPerDay[$date] ?? 0,
                'unique_visitors' => $uniquePerDay[$date] ?? 0,
            ];
        }

        return $timeline;
    }

    /**
     * Get geographic distribution of visitors.
     */
    private function getGeographicData(Carbon $startDate): array
    {
        $countryData = PageView::where('created_at', '>=', $startDate)
            ->select(
                'country',
                DB::raw('COUNT(*) as visits'),
                DB::raw('COUNT(DISTINCT session_id) as unique_visitors')
            )
            ->whereNotNull('country')
            ->groupBy('country')
            ->orderByDesc('visits')
            ->limit(15)
            ->get()
            ->map(function ($item) {
                return [
                    'country' => $item->country,
                    'flag' => $this->getCountryFlag($item->country),
                    'visits' => (int) $item->visits,
                    'unique_visitors' => (int) $item->unique_visitors,
                ];
            })
            ->values();

        $cityData = PageView::where('created_at', '>=', $startDate)
            ->select(
                'country',
                'city',
                DB::raw('COUNT(*) as visits')
            )
            ->whereNotNull('city')
            ->groupBy('country', 'city')
            ->orderByDesc('visits')
            ->limit(20)
            ->get()
            ->map(function ($item) {
                return [
                    'country' => $item->country,
                    'city' => $item->city,
                    'visits' => (int) $item->visits,
                ];
            })
            ->values();

        return [
            'by_country' => $countryData,
            'by_city' => $cityData,
        ];
    }

    /**
     * Get visitor type breakdown.
     */
    private function getVisitorTypes(Carbon $startDate): array
    {
        $visitorTypes = PageView::where('created_at', '>=', $startDate)
            ->select(
                'visitor_type',
                DB::raw('COUNT(*) as count'),
                DB::raw('COUNT(DISTINCT session_id) as unique_visitors')
            )
            ->groupBy('visitor_type')
            ->orderByDesc('count')
            ->get()
            ->map(function ($item) {
                return [
                    'type' => $item->visitor_type ?? 'unknown',
                    'label' => $this->formatVisitorTypeLabel($item->visitor_type ?? 'unknown'),
                    'count' => (int) $item->count,
                    'unique_visitors' => (int) $item->unique_visitors,
                ];
            })
            ->values();

        $recruiterBreakdown = PageView::where('created_at', '>=', $startDate)
            ->select(
                'is_recruiter',
                DB::raw('COUNT(*) as count'),
                DB::raw('COUNT(DISTINCT session_id) as unique_visitors')
            )
            ->groupBy('is_recruiter')
            ->get()
            ->map(function ($item) {
                return [
                    'is_recruiter' => (bool) $item->is_recruiter,
                    'label' => $item->is_recruiter ? 'Recruiter' : 'Other',
                    'count' => (int) $item->count,
                    'unique_visitors' => (int) $item->unique_visitors,
                ];
            })
            ->values();

        return [
            'by_type' => $visitorTypes,
            'recruiter_breakdown' => $recruiterBreakdown,
        ];
    }

    /**
     * Get top companies visiting the site.
     */
    private function getTopCompanies(Carbon $startDate): array
    {
        $companies = PageView::where('created_at', '>=', $startDate)
            ->whereNotNull('company')
            ->where('company', '!=', '')
            ->select(
                'company',
                'country',
                DB::raw('COUNT(*) as visits'),
                DB::raw('COUNT(DISTINCT session_id) as unique_visitors'),
                DB::raw('MAX(is_recruiter) as is_recruiter')
            )
            ->groupBy('company', 'country')
            ->orderByDesc('visits')
            ->limit(20)
            ->get()
            ->map(function ($item) {
                return [
                    'company' => $item->company,
                    'country' => $item->country,
                    'visits' => (int) $item->visits,
                    'unique_visitors' => (int) $item->unique_visitors,
                    'is_recruiter' => (bool) $item->is_recruiter,
                ];
            })
            ->values();

        return $companies->toArray();
    }

    /**
     * Get section engagement data.
     */
    private function getSectionEngagement(Carbon $startDate): array
    {
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

        return $sectionStats;
    }

    /**
     * Get referrer data.
     */
    private function getReferrerData(Carbon $startDate): array
    {
        $referrers = PageView::where('created_at', '>=', $startDate)
            ->whereNotNull('referrer')
            ->where('referrer', '!=', '')
            ->select(
                'referrer',
                DB::raw('COUNT(*) as visits'),
                DB::raw('COUNT(DISTINCT session_id) as unique_visitors')
            )
            ->groupBy('referrer')
            ->orderByDesc('visits')
            ->limit(15)
            ->get()
            ->map(function ($item) {
                return [
                    'referrer' => $item->referrer,
                    'domain' => parse_url($item->referrer, PHP_URL_HOST) ?? $item->referrer,
                    'visits' => (int) $item->visits,
                    'unique_visitors' => (int) $item->unique_visitors,
                ];
            })
            ->values();

        return $referrers;
    }

    /**
     * Process and enrich a page view with geolocation and company data.
     */
    public function enrichPageView(array $data): array
    {
        $ipAddress = $data['ip_address'] ?? null;
        $userAgent = $data['user_agent'] ?? '';
        $referrer = $data['referrer'] ?? null;

        $geoData = $this->getGeoLocation($ipAddress);
        $companyData = $this->getCompanyFromIP($ipAddress);

        $company = $companyData['company'] ?? null;
        $isRecruiter = PageView::detectRecruiterSignals($company ?? '', $userAgent);
        $visitorType = PageView::classifyVisitor($company, $referrer, $userAgent);

        return array_merge($data, [
            'country' => $geoData['country'] ?? null,
            'city' => $geoData['city'] ?? null,
            'region' => $geoData['region'] ?? null,
            'company' => $company,
            'is_recruiter' => $isRecruiter,
            'visitor_type' => $visitorType,
        ]);
    }

    /**
     * Get geolocation data from IP address using free APIs.
     */
    private function getGeoLocation(?string $ipAddress): array
    {
        if (!$ipAddress || in_array($ipAddress, ['127.0.0.1', '::1', 'localhost'])) {
            return [];
        }

        try {
            // Using ip-api.com (free, no API key required for non-commercial use)
            $response = Http::timeout(2)->get("http://ip-api.com/json/{$ipAddress}");
            
            if ($response->successful()) {
                $data = $response->json();
                return [
                    'country' => $data['country'] ?? null,
                    'countryCode' => $data['countryCode'] ?? null,
                    'region' => $data['regionName'] ?? null,
                    'city' => $data['city'] ?? null,
                    'lat' => $data['lat'] ?? null,
                    'lon' => $data['lon'] ?? null,
                    'isp' => $data['isp'] ?? null,
                ];
            }
        } catch (\Exception $e) {
            Log::warning('Failed to get geo location: ' . $e->getMessage());
        }

        return [];
    }

    /**
     * Get company information from IP address.
     */
    private function getCompanyFromIP(?string $ipAddress): array
    {
        if (!$ipAddress || in_array($ipAddress, ['127.0.0.1', '::1', 'localhost'])) {
            return [];
        }

        try {
            // Using ip-api.com for company info
            $response = Http::timeout(2)->get("http://ip-api.com/json/{$ipAddress}?fields=status,message,country,city,org,isp,as");
            
            if ($response->successful()) {
                $data = $response->json();
                $org = $data['org'] ?? $data['isp'] ?? null;
                
                // Clean up company name
                $company = null;
                if ($org) {
                    // Remove AS number if present
                    $company = preg_replace('/^AS\d+\s*/', '', $org);
                    // Common ISP suffixes to remove for better company identification
                    $company = preg_replace('/\s*Inc\.?$/i', '', $company);
                    $company = preg_replace('/\s*LLC$/i', '', $company);
                    $company = preg_replace('/\s*Ltd\.?$/i', '', $company);
                }

                return [
                    'company' => $company,
                    'isp' => $data['isp'] ?? null,
                    'as' => $data['as'] ?? null,
                ];
            }
        } catch (\Exception $e) {
            Log::warning('Failed to get company info: ' . $e->getMessage());
        }

        return [];
    }

    /**
     * Format section label for display.
     */
    private function formatSectionLabel(string $section): string
    {
        return ucwords(str_replace('-', ' ', $section));
    }

    /**
     * Format visitor type label for display.
     */
    private function formatVisitorTypeLabel(string $type): string
    {
        $labels = [
            'recruiter' => 'Recruiter',
            'developer' => 'Developer',
            'organic_search' => 'Organic Search',
            'general' => 'General Visitor',
            'unknown' => 'Unknown',
        ];

        return $labels[$type] ?? ucfirst($type);
    }

    /**
     * Get country flag emoji.
     */
    private function getCountryFlag(string $countryCode): string
    {
        $flagCodes = [
            'United States' => '🇺🇸',
            'United Kingdom' => '🇬🇧',
            'Germany' => '🇩🇪',
            'France' => '🇫🇷',
            'Spain' => '🇪🇸',
            'Portugal' => '🇵🇹',
            'Netherlands' => '🇳🇱',
            'Brazil' => '🇧🇷',
            'India' => '🇮🇳',
            'China' => '🇨🇳',
            'Japan' => '🇯🇵',
            'Canada' => '🇨🇦',
            'Australia' => '🇦🇺',
            'Italy' => '🇮🇹',
            'Poland' => '🇵🇱',
            'Ukraine' => '🇺🇦',
            'Romania' => '🇷🇴',
        ];

        return $flagCodes[$countryCode] ?? '🌍';
    }
}
