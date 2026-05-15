<?php

use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\LegalController;
use App\Livewire\Portal\CreateTicket;
use App\Livewire\Portal\Onboarding;
use App\Livewire\Portal\TicketDetail;
use App\Livewire\Portal\TicketList;
use App\Livewire\Public\OfficeDetail;
use App\Livewire\Public\OfficeList;
use App\Livewire\Public\TicketTracker;
use App\Models\Office;
use Illuminate\Support\Facades\Route;

Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');

Route::prefix('legal')->name('legal.')->group(function (): void {
    Route::get('/terms', [LegalController::class, 'terms'])->name('terms');
    Route::get('/privacy', [LegalController::class, 'privacy'])->name('privacy');
    Route::get('/cookies', [LegalController::class, 'cookies'])->name('cookies');
    Route::get('/data-protection', [LegalController::class, 'dataProtection'])->name('data-protection');
    Route::get('/transparency', [LegalController::class, 'transparency'])->name('transparency');
});

Route::get('/', function () {
    $offices = Office::active()
        ->orderBy('sort_order')
        ->orderBy('name')
        ->take(5)
        ->get();

    return view('welcome', compact('offices'));
})->name('home');

Route::get('/offices', OfficeList::class)->name('offices.index');
Route::get('/offices/{slug}', OfficeDetail::class)->name('offices.show');
Route::get('/track', TicketTracker::class)->name('track.ticket');
Route::view('/project-overview', 'pages.project-overview')->name('project.overview');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return to_route('portal.tickets.index');
    })->name('dashboard');
});

require __DIR__.'/settings.php';

// 'verified' is consistent with the existing dashboard route group.
// Google OAuth guarantees email validity; MustVerifyEmail is intentionally not implemented.
Route::middleware(['auth', 'verified', 'role:student'])
    ->prefix('portal')
    ->name('portal.')
    ->group(function (): void {
        Route::get('/onboarding', Onboarding::class)->name('onboarding');

        Route::middleware('onboarding')->group(function (): void {
            Route::get('/tickets', TicketList::class)->name('tickets.index');
            Route::get('/tickets/create', CreateTicket::class)->name('tickets.create');
            Route::get('/tickets/{ulid}', TicketDetail::class)->name('tickets.show');
        });
    });
