<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [\App\Http\Controllers\PortfolioController::class, 'index'])->name('home');

Route::post('/language', [\App\Http\Controllers\LanguageController::class, 'update'])->name('language.switch');

Route::get('/experiences/{experience}/image', [\App\Http\Controllers\Admin\ExperienceController::class, 'serveImage'])->name('experiences.image');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    // Admin Routes
    Route::resource('admin/experiences', \App\Http\Controllers\Admin\ExperienceController::class)->names('admin.experiences');
    Route::resource('admin/education', \App\Http\Controllers\Admin\EducationController::class)->names('admin.education');
    Route::resource('admin/skills', \App\Http\Controllers\Admin\SkillController::class)->names('admin.skills');
    Route::resource('admin/projects', \App\Http\Controllers\Admin\ProjectController::class)->names('admin.projects');
    Route::resource('admin/sections', \App\Http\Controllers\Admin\PageSectionController::class)->names('admin.sections');

    Route::get('admin/settings', [\App\Http\Controllers\Admin\SettingController::class, 'edit'])->name('admin.settings.edit');
    Route::post('admin/settings', [\App\Http\Controllers\Admin\SettingController::class, 'update'])->name('admin.settings.update');
});

require __DIR__ . '/auth.php';
