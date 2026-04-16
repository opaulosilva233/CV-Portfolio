<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [\App\Http\Controllers\PortfolioController::class, 'index'])->name('home');
Route::get('/sitemap.xml', [\App\Http\Controllers\SitemapController::class, 'index'])->name('sitemap');

Route::post('/language', [\App\Http\Controllers\LanguageController::class, 'update'])->name('language.switch');
Route::post('/contact', [\App\Http\Controllers\PortfolioController::class, 'storeMessage'])->name('contact.store');
Route::post('/analytics/section-engagement', [\App\Http\Controllers\Admin\AnalyticsController::class, 'trackSectionEngagement'])
    ->middleware('throttle:120,1')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])
    ->name('analytics.section-engagement.track');

Route::get('/experiences/{experience}/image', [\App\Http\Controllers\Admin\ExperienceController::class, 'serveImage'])->name('experiences.image');
Route::get('/projects/{project}/image/{filename?}', [\App\Http\Controllers\Admin\ProjectController::class, 'serveImage'])->name('projects.image');
Route::get('/educations/{education}/image', [\App\Http\Controllers\Admin\EducationController::class, 'serveImage'])->name('educations.image');
Route::get('/settings/hero-image', [\App\Http\Controllers\Admin\SettingController::class, 'serveHeroImage'])->name('settings.hero-image');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

    Route::post('admin/experiences/bulk-delete', [\App\Http\Controllers\Admin\ExperienceController::class, 'bulkDelete'])->name('admin.experiences.bulk-delete');
    Route::resource('admin/experiences', \App\Http\Controllers\Admin\ExperienceController::class)->names('admin.experiences');
    
    Route::post('admin/education/bulk-delete', [\App\Http\Controllers\Admin\EducationController::class, 'bulkDelete'])->name('admin.education.bulk-delete');
    Route::resource('admin/education', \App\Http\Controllers\Admin\EducationController::class)->names('admin.education');
    
    Route::post('admin/skills/bulk-delete', [\App\Http\Controllers\Admin\SkillController::class, 'bulkDelete'])->name('admin.skills.bulk-delete');
    Route::post('admin/skills/reorder', [\App\Http\Controllers\Admin\SkillController::class, 'reorder'])->name('admin.skills.reorder');
    Route::resource('admin/skills', \App\Http\Controllers\Admin\SkillController::class)->names('admin.skills');
    
    Route::post('admin/projects/bulk-delete', [\App\Http\Controllers\Admin\ProjectController::class, 'bulkDelete'])->name('admin.projects.bulk-delete');
    Route::post('admin/projects/reorder', [\App\Http\Controllers\Admin\ProjectController::class, 'reorder'])->name('admin.projects.reorder');
    Route::resource('admin/projects', \App\Http\Controllers\Admin\ProjectController::class)->names('admin.projects');
    Route::resource('admin/sections', \App\Http\Controllers\Admin\PageSectionController::class)->names('admin.sections');
    Route::post('admin/messages/bulk-delete', [\App\Http\Controllers\Admin\MessageController::class, 'bulkDelete'])->name('admin.messages.bulk-delete');
    Route::resource('admin/messages', \App\Http\Controllers\Admin\MessageController::class)->only(['index', 'show', 'update', 'destroy'])->names('admin.messages');
    Route::resource('admin/interests', \App\Http\Controllers\Admin\InterestController::class)->names('admin.interests');
    Route::post('admin/interests/reorder', [\App\Http\Controllers\Admin\InterestController::class, 'updateOrder'])->name('admin.interests.reorder');

    Route::get('admin/settings', [\App\Http\Controllers\Admin\SettingController::class, 'edit'])->name('admin.settings.edit');
    Route::post('admin/settings', [\App\Http\Controllers\Admin\SettingController::class, 'update'])->name('admin.settings.update');

    Route::get('admin/analytics/stats', [\App\Http\Controllers\Admin\AnalyticsController::class, 'getStats'])->name('admin.analytics.stats');
});

require __DIR__ . '/auth.php';
