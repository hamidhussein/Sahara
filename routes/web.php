<?php

use App\Backups\BackupManager;
use App\Enums\Role;
use App\Http\Controllers\AboutController;
use App\Http\Controllers\DonationController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SeoController;
use App\Models\HelpRequest;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

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

// Services (plan 2.1): help requests, volunteers and blood donors.
Route::view('/get-help', 'pages.get-help')->name('get-help');
Route::view('/get-involved', 'pages.get-involved')->name('get-involved');
Route::view('/partner-with-us', 'pages.partner')->name('partner');

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
// Backup downloads from Admin → Settings → Backups (Super Admin, signed link).
Route::get('/finance/backups/download', function (Request $request, BackupManager $backups) {
    abort_unless($request->user()?->hasRole(Role::SuperAdmin->value), 403);
    $path = (string) $request->query('path');
    abort_unless($backups->isBackupFile($path), 404);

    // A backup holds every donor's details, so each download is recorded.
    activity('backups')->event('download')->withProperties(['file' => basename($path), 'ip' => $request->ip()])->log('Downloaded a backup');

    return Storage::disk($backups->disk())->download($path);
})->middleware(['auth', 'signed'])->name('backups.download');

Route::get('/receipts/{donation:reference}', [DonationController::class, 'receipt'])
    ->middleware(['signed', 'throttle:30,1'])
    ->name('donations.receipt');
Route::get('/payments/fake/{donation:reference}', [DonationController::class, 'fakeCheckout'])->name('payments.fake.checkout');
Route::get('/finance/transfer-receipts/{donation}', [DonationController::class, 'transferReceipt'])
    ->middleware('auth')
    ->name('donations.transfer-receipt');

// Documents attached to help requests (prescriptions, CNIC copies): Services team only.
Route::get('/services/help-requests/{helpRequest}/document', function (HelpRequest $helpRequest) {
    Gate::authorize('view', $helpRequest);
    $media = $helpRequest->getFirstMedia('document') ?? abort(404);

    return response()->streamDownload(fn () => fpassthru($media->stream()), $media->file_name, ['Content-Type' => $media->mime_type], 'inline');
})->middleware('auth')->name('services.help-document');

// Legal (footer only). Text is edited in Admin → Content → Pages.
Route::get('/privacy', PageController::class)->defaults('slug', 'privacy')->name('privacy');
Route::get('/refund-policy', PageController::class)->defaults('slug', 'refund-policy')->name('refund-policy');
Route::get('/terms', PageController::class)->defaults('slug', 'terms')->name('terms');

// Search engines (plan 1.4)
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');

// The pre-launch coming-soon page, kept until the new site goes live.
Route::get('/coming-soon', fn () => response()->file(public_path('coming-soon/index.html')));
