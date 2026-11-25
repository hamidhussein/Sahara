<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\ProjectController;
use Illuminate\Support\Facades\Route;

/*
 * Public site, following the sitemap in the SSWF Website Master Plan (0.2).
 * Pages still showing 'pages.placeholder' are built later in Phase 1, or in
 * Phase 2 for Get Help and Get Involved.
 */

Route::get('/', HomeController::class)->name('home');

$placeholder = fn (string $title, string $lede, array $crumbs = []) => [
    'title' => $title,
    'lede' => $lede,
    'breadcrumbs' => $crumbs ?: [$title => null],
];

// About
Route::view('/about', 'pages.placeholder', $placeholder('About Us', 'Twenty years of standing with Gilgit-Baltistan.'))->name('about');
Route::view('/about/leadership', 'pages.placeholder', $placeholder('Our Leadership', 'The people who carry the responsibility.', ['About' => '/about', 'Leadership' => null]))->name('leadership');
Route::view('/about/transparency', 'pages.placeholder', $placeholder('Transparency', 'Your trust is our most important asset.', ['About' => '/about', 'Transparency' => null]))->name('transparency');

// Programs and projects
Route::get('/programs', [ProgramController::class, 'index'])->name('programs.index');
Route::get('/programs/{program}', [ProgramController::class, 'show'])->name('programs.show');
Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');

// Services (Phase 2)
Route::view('/get-help', 'pages.placeholder', $placeholder('Get Help', 'You are not alone. Tell us what you need.'))->name('get-help');
Route::view('/get-involved', 'pages.placeholder', $placeholder('Get Involved', 'Be someone’s Sahara.'))->name('get-involved');

// Media
Route::get('/news', [PostController::class, 'index'])->name('news.index');
Route::get('/news/{post}', [PostController::class, 'show'])->name('news.show');
Route::view('/gallery', 'pages.placeholder', $placeholder('Gallery', 'Moments of service.'))->name('gallery');

// Contact and donate
Route::view('/contact', 'pages.placeholder', $placeholder('Contact Us', 'We would love to hear from you.'))->name('contact');
Route::view('/donate', 'pages.placeholder', $placeholder('Donate', 'Your gift becomes water, medicine and food for someone today.'))->name('donate');

// Legal (footer only)
Route::view('/privacy', 'pages.placeholder', $placeholder('Privacy Policy', 'How we collect, use and protect your details.'))->name('privacy');
Route::view('/refund-policy', 'pages.placeholder', $placeholder('Refund Policy', 'What to do if you donated by mistake.'))->name('refund-policy');
Route::view('/terms', 'pages.placeholder', $placeholder('Terms of Use', 'The terms for using this website.'))->name('terms');

// The pre-launch coming-soon page, kept until the new site goes live.
Route::get('/coming-soon', fn () => response()->file(public_path('coming-soon/index.html')));
