<?php

use App\Http\Controllers\Auth\GoogleAuthController;
use App\Livewire\Portal\CreateTicket;
use App\Livewire\Portal\Onboarding;
use App\Livewire\Portal\TicketDetail;
use App\Livewire\Portal\TicketList;
use App\Models\Office;
use Illuminate\Support\Facades\Route;

Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');

Route::get('/', function () {
    $offices = Office::active()
        ->orderBy('sort_order')
        ->orderBy('name')
        ->take(5)
        ->get();

    return view('welcome', compact('offices'));
})->name('home');

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
