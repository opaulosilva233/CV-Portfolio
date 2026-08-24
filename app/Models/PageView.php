<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PageView extends Model
{
    use HasFactory;

    protected $fillable = [
        'path',
        'session_id',
        'ip_address',
        'user_agent',
        'country',
        'city',
        'region',
        'company',
        'is_recruiter',
        'visitor_type',
        'referrer',
    ];

    protected $casts = [
        'is_recruiter' => 'boolean',
    ];

    /**
     * Determine if the visitor is likely a recruiter based on company name or other signals.
     */
    public static function detectRecruiterSignals(string $company, string $userAgent): bool
    {
        $recruiterKeywords = [
            'recruit', 'talent', 'hr ', 'human resources', 'linkedin', 
            'indeed', 'glassdoor', 'hiring', 'staffing', 'headhunter',
            'career', 'job', 'employment', 'workday', 'greenhouse',
            'lever', 'ashby', 'bamboohr'
        ];

        $companyLower = strtolower($company);
        $uaLower = strtolower($userAgent);

        foreach ($recruiterKeywords as $keyword) {
            if (str_contains($companyLower, $keyword) || str_contains($uaLower, $keyword)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Classify visitor type based on available data.
     */
    public static function classifyVisitor(?string $company, ?string $referrer, string $userAgent): string
    {
        if (self::detectRecruiterSignals($company ?? '', $userAgent)) {
            return 'recruiter';
        }

        $techReferrers = ['github.com', 'stackoverflow.com', 'dev.to', 'medium.com', 'twitter.com', 'x.com'];
        foreach ($techReferrers as $ref) {
            if ($referrer && str_contains(strtolower($referrer), $ref)) {
                return 'developer';
            }
        }

        $searchEngines = ['google', 'bing', 'duckduckgo', 'yahoo'];
        foreach ($searchEngines as $engine) {
            if ($referrer && str_contains(strtolower($referrer), $engine)) {
                return 'organic_search';
            }
        }

        return 'general';
    }
}
