<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\DonationController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SeoController;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
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
Route::get('/about', [AboutController::class, 'about'])->name('about');
Route::get('/about/leadership', [AboutController::class, 'leadership'])->name('leadership');
Route::get('/about/transparency', [AboutController::class, 'transparency'])->name('transparency');

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
Route::get('/gallery', [GalleryController::class, 'index'])->name('gallery');
Route::get('/gallery/{album}', [GalleryController::class, 'show'])->name('gallery.show');

// Contact
Route::view('/contact', 'contact')->name('contact');

// Donations (plan 1.1 Donate, 1.2 Donation module)
Route::get('/donate', [DonationController::class, 'create'])->name('donate');
Route::get('/donate/zakat-calculator', fn () => view('donate.zakat-calculator'))->name('donate.zakat-calculator');
Route::get('/donate/thank-you/{donation:reference}', [DonationController::class, 'thankYou'])->middleware('signed')->name('donate.thank-you');
Route::get('/donate/payment-failed/{donation:reference}', [DonationController::class, 'failed'])->middleware('signed')->name('donate.failed');
Route::match(['get', 'post'], '/payments/{method}/callback', [DonationController::class, 'callback'])
    ->middleware('throttle:30,1')
    ->withoutMiddleware(PreventRequestForgery::class)
    ->name('payments.callback');
Route::get('/receipts/{donation:reference}', [DonationController::class, 'receipt'])
    ->middleware(['signed', 'throttle:30,1'])
    ->name('donations.receipt');
Route::get('/payments/fake/{donation:reference}', [DonationController::class, 'fakeCheckout'])->name('payments.fake.checkout');
Route::get('/finance/transfer-receipts/{donation}', [DonationController::class, 'transferReceipt'])
    ->middleware('auth')
    ->name('donations.transfer-receipt');

// Legal (footer only). Text is edited in Admin → Content → Pages.
Route::get('/privacy', PageController::class)->defaults('slug', 'privacy')->name('privacy');
Route::get('/refund-policy', PageController::class)->defaults('slug', 'refund-policy')->name('refund-policy');
Route::get('/terms', PageController::class)->defaults('slug', 'terms')->name('terms');

// Search engines (plan 1.4)
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');

// The pre-launch coming-soon page, kept until the new site goes live.
Route::get('/coming-soon', fn () => response()->file(public_path('coming-soon/index.html')));
