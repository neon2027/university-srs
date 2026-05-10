# Employee Onboarding & Verification Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** After first Google login, show a one-time role-selection screen; employees pick an office and wait for admin approval via Filament while retaining student-level portal access.

**Architecture:** Three new columns on `users` (`onboarding_status`, `pending_office_id`, `onboarding_completed_at`) drive state. A middleware redirects users who haven't completed onboarding to `/portal/onboarding`. A Livewire banner in the portal layout shows pending/rejected status. A Filament resource lets office admins approve or reject requests.

**Tech Stack:** Laravel 13, Livewire 4, Filament v5, Spatie Laravel Permission, Laravel Notifications (mail channel), Pest v4.

---

## File Map

| Action | Path |
|---|---|
| Create | `app/Enums/OnboardingStatus.php` |
| Create | `database/migrations/2026_05_11_000001_add_onboarding_columns_to_users_table.php` |
| Modify | `app/Models/User.php` |
| Modify | `database/factories/UserFactory.php` |
| Create | `app/Http/Middleware/EnsureOnboardingComplete.php` |
| Modify | `bootstrap/app.php` |
| Modify | `routes/web.php` |
| Modify | `app/Http/Controllers/Auth/GoogleAuthController.php` |
| Create | `app/Livewire/Portal/Onboarding.php` |
| Create | `resources/views/livewire/portal/onboarding.blade.php` |
| Create | `app/Livewire/Portal/OnboardingNotice.php` |
| Create | `resources/views/livewire/portal/onboarding-notice.blade.php` |
| Modify | `resources/views/components/layouts/portal.blade.php` |
| Create | `app/Notifications/EmployeeVerificationRequestedNotification.php` |
| Create | `app/Notifications/EmployeeVerificationResultNotification.php` |
| Create | `app/Filament/Actions/ApproveEmployeeAction.php` |
| Create | `app/Filament/Actions/RejectEmployeeAction.php` |
| Create | `app/Filament/Resources/EmployeeRequestResource.php` |
| Create | `app/Filament/Resources/EmployeeRequestResource/Pages/ListEmployeeRequests.php` |
| Create | `tests/Feature/EmployeeOnboardingTest.php` |

---

### Task 1: OnboardingStatus Enum, Migration & User Model

**Files:**
- Create: `app/Enums/OnboardingStatus.php`
- Create: `database/migrations/2026_05_11_000001_add_onboarding_columns_to_users_table.php`
- Modify: `app/Models/User.php`
- Modify: `database/factories/UserFactory.php`

- [ ] **Step 1: Create the OnboardingStatus enum**

```php
<?php
// app/Enums/OnboardingStatus.php
namespace App\Enums;

enum OnboardingStatus: string
{
    case PendingEmployee = 'pending_employee';
    case Rejected = 'rejected';
}
```

- [ ] **Step 2: Generate and fill the migration**

Run: `php artisan make:migration add_onboarding_columns_to_users_table --no-interaction`

Replace the generated file content with:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('onboarding_status')->nullable()->after('avatar');
            $table->foreignId('pending_office_id')->nullable()->constrained('offices')->nullOnDelete()->after('onboarding_status');
            $table->timestamp('onboarding_completed_at')->nullable()->after('pending_office_id');
        });

        // Backfill so existing users skip the onboarding screen.
        DB::table('users')->update(['onboarding_completed_at' => DB::raw('created_at')]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('pending_office_id');
            $table->dropColumn(['onboarding_status', 'onboarding_completed_at']);
        });
    }
};
```

- [ ] **Step 3: Run the migration**

Run: `php artisan migrate --no-interaction`

Expected: no errors, `users` table gains three columns.

- [ ] **Step 4: Update User model — Fillable, casts, pendingOffice relation**

Replace the `#[Fillable]` attribute and `casts()` method, and add the `pendingOffice` relation. The full updated file:

```php
<?php

namespace App\Models;

use App\Enums\OnboardingStatus;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'google_id', 'avatar', 'onboarding_status', 'pending_office_id', 'onboarding_completed_at'])]
#[Hidden(['remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() !== 'admin') {
            return false;
        }

        return $this->hasAnyRole(['super_admin', 'office_admin', 'staff']);
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'onboarding_completed_at' => 'datetime',
            'onboarding_status' => OnboardingStatus::class,
        ];
    }

    public function offices(): BelongsToMany
    {
        return $this->belongsToMany(Office::class)
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    public function pendingOffice(): BelongsTo
    {
        return $this->belongsTo(Office::class, 'pending_office_id');
    }

    public function primaryOffice(): ?Office
    {
        return $this->offices()->wherePivot('is_primary', true)->first();
    }

    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }
}
```

- [ ] **Step 5: Update UserFactory — set onboarding_completed_at by default and add pendingEmployee state**

```php
<?php

namespace Database\Factories;

use App\Enums\OnboardingStatus;
use App\Models\Office;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'google_id' => null,
            'avatar' => null,
            'onboarding_completed_at' => now(),
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function withGoogle(): static
    {
        return $this->state(fn (array $attributes) => [
            'google_id' => fake()->uuid(),
            'avatar' => fake()->imageUrl(),
        ]);
    }

    public function pendingEmployee(Office $office): static
    {
        return $this->state(fn (array $attributes) => [
            'onboarding_status' => OnboardingStatus::PendingEmployee,
            'pending_office_id' => $office->id,
            'onboarding_completed_at' => now(),
        ]);
    }

    public function rejectedEmployee(): static
    {
        return $this->state(fn (array $attributes) => [
            'onboarding_status' => OnboardingStatus::Rejected,
            'pending_office_id' => null,
            'onboarding_completed_at' => now(),
        ]);
    }

    public function needsOnboarding(): static
    {
        return $this->state(fn (array $attributes) => [
            'onboarding_completed_at' => null,
        ]);
    }
}
```

- [ ] **Step 6: Run pint**

Run: `vendor/bin/pint --dirty --format agent`

- [ ] **Step 7: Commit**

```bash
git add app/Enums/OnboardingStatus.php \
        database/migrations/ \
        app/Models/User.php \
        database/factories/UserFactory.php
git commit -m "feat: add onboarding columns to users, OnboardingStatus enum, factory states"
```

---

### Task 2: Middleware, Routes & Google Callback

**Files:**
- Create: `app/Http/Middleware/EnsureOnboardingComplete.php`
- Modify: `bootstrap/app.php`
- Modify: `routes/web.php`
- Modify: `app/Http/Controllers/Auth/GoogleAuthController.php`

- [ ] **Step 1: Create the middleware**

Run: `mkdir -p app/Http/Middleware`

Create `app/Http/Middleware/EnsureOnboardingComplete.php`:

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOnboardingComplete
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->onboarding_completed_at === null) {
            return redirect()->route('portal.onboarding');
        }

        return $next($request);
    }
}
```

- [ ] **Step 2: Register the middleware alias in bootstrap/app.php**

```php
<?php

use App\Http\Middleware\EnsureOnboardingComplete;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
            'onboarding' => EnsureOnboardingComplete::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
```

- [ ] **Step 3: Restructure portal routes in routes/web.php**

Replace the existing portal route group with:

```php
Route::middleware(['auth', 'verified', 'role:student'])
    ->prefix('portal')
    ->name('portal.')
    ->group(function (): void {
        Route::get('/onboarding', \App\Livewire\Portal\Onboarding::class)->name('onboarding');

        Route::middleware('onboarding')->group(function (): void {
            Route::get('/tickets', TicketList::class)->name('tickets.index');
            Route::get('/tickets/create', CreateTicket::class)->name('tickets.create');
            Route::get('/tickets/{ulid}', TicketDetail::class)->name('tickets.show');
        });
    });
```

- [ ] **Step 4: Update GoogleAuthController — redirect new users to onboarding, not portal**

In the `callback()` method, change the final redirect for non-admin users from `/portal/tickets` to `/portal/onboarding` for brand-new users (those without `onboarding_completed_at`):

```php
public function callback()
{
    $googleUser = Socialite::driver('google')->user();

    $user = User::where('google_id', $googleUser->id)
        ->orWhere('email', $googleUser->email)
        ->first();

    if ($user) {
        $user->update([
            'google_id' => $googleUser->id,
            'avatar' => $googleUser->avatar,
        ]);
    } else {
        $user = User::create([
            'name' => $googleUser->name,
            'email' => $googleUser->email,
            'google_id' => $googleUser->id,
            'avatar' => $googleUser->avatar,
        ]);

        $user->forceFill(['email_verified_at' => now()])->save();
        $user->assignRole('student');
    }

    Auth::login($user, remember: true);

    if ($user->hasAnyRole(['super_admin', 'office_admin', 'staff'])) {
        return redirect('/admin');
    }

    // New users (onboarding_completed_at is null) go to onboarding.
    // Returning users go straight to the portal.
    if ($user->onboarding_completed_at === null) {
        return redirect()->route('portal.onboarding');
    }

    return redirect()->route('portal.tickets.index');
}
```

- [ ] **Step 5: Run pint**

Run: `vendor/bin/pint --dirty --format agent`

- [ ] **Step 6: Commit**

```bash
git add app/Http/Middleware/EnsureOnboardingComplete.php \
        bootstrap/app.php \
        routes/web.php \
        app/Http/Controllers/Auth/GoogleAuthController.php
git commit -m "feat: add EnsureOnboardingComplete middleware and restructure portal routes"
```

---

### Task 3: Onboarding Livewire Component

**Files:**
- Create: `app/Livewire/Portal/Onboarding.php`
- Create: `resources/views/livewire/portal/onboarding.blade.php`

- [ ] **Step 1: Create the Livewire component**

Run: `php artisan make:livewire --no-interaction Portal/Onboarding`

Replace `app/Livewire/Portal/Onboarding.php` with:

```php
<?php

namespace App\Livewire\Portal;

use App\Enums\OnboardingStatus;
use App\Models\Office;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.portal')]
class Onboarding extends Component
{
    public int $step = 1;

    public ?int $selectedOfficeId = null;

    public function mount(): void
    {
        if (auth()->user()->onboarding_completed_at !== null) {
            $this->redirect(route('portal.tickets.index'), navigate: true);
        }
    }

    #[Computed]
    public function offices()
    {
        return Office::active()->orderBy('name')->get();
    }

    public function chooseStudent(): void
    {
        auth()->user()->update(['onboarding_completed_at' => now()]);
        $this->redirect(route('portal.tickets.index'), navigate: true);
    }

    public function showEmployeePicker(): void
    {
        $this->step = 2;
    }

    public function submitEmployeeRequest(): void
    {
        $this->validate(['selectedOfficeId' => ['required', 'integer', 'exists:offices,id']]);

        $office = Office::findOrFail($this->selectedOfficeId);

        auth()->user()->update([
            'onboarding_status' => OnboardingStatus::PendingEmployee,
            'pending_office_id' => $office->id,
            'onboarding_completed_at' => now(),
        ]);

        $admins = $office->users()->role('office_admin')->get();
        \Illuminate\Support\Facades\Notification::send(
            $admins,
            new \App\Notifications\EmployeeVerificationRequestedNotification(auth()->user(), $office)
        );

        $this->redirect(route('portal.tickets.index'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.portal.onboarding');
    }
}
```

- [ ] **Step 2: Create the blade view**

Replace `resources/views/livewire/portal/onboarding.blade.php` with:

```blade
<div class="flex min-h-[70vh] items-center justify-center">
    <div class="w-full max-w-md">
        @if ($step === 1)
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold text-white">Welcome to BUSRS</h1>
                <p class="mt-2 text-sm text-zinc-400">How are you affiliated with Bicol University?</p>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <button wire:click="chooseStudent"
                    class="flex flex-col items-center gap-3 rounded-xl border border-zinc-700 bg-zinc-800 p-8 text-center hover:border-zinc-500 hover:bg-zinc-700 transition-colors">
                    <span class="text-4xl">🎓</span>
                    <div>
                        <div class="font-semibold text-white">Student</div>
                        <div class="text-xs text-zinc-400 mt-1">Enrolled at BU</div>
                    </div>
                </button>

                <button wire:click="showEmployeePicker"
                    class="flex flex-col items-center gap-3 rounded-xl border border-zinc-700 bg-zinc-800 p-8 text-center hover:border-zinc-500 hover:bg-zinc-700 transition-colors">
                    <span class="text-4xl">🏢</span>
                    <div>
                        <div class="font-semibold text-white">Employee</div>
                        <div class="text-xs text-zinc-400 mt-1">Faculty or Staff</div>
                    </div>
                </button>
            </div>
        @else
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold text-white">Select Your Office</h1>
                <p class="mt-2 text-sm text-zinc-400">Your office admin will verify your affiliation before granting staff access.</p>
            </div>

            <div class="rounded-xl border border-zinc-700 bg-zinc-800 p-6">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-zinc-300 mb-2">Office</label>
                    <select wire:model="selectedOfficeId"
                        class="w-full rounded-lg border border-zinc-600 bg-zinc-700 px-3 py-2 text-white focus:border-[#0089CB] focus:outline-none">
                        <option value="">Select an office…</option>
                        @foreach ($this->offices as $office)
                            <option value="{{ $office->id }}">{{ $office->name }}</option>
                        @endforeach
                    </select>
                    @error('selectedOfficeId') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>

                <button wire:click="submitEmployeeRequest"
                    class="w-full rounded-lg bg-[#0089CB] px-4 py-2.5 text-sm font-semibold text-white hover:bg-[#0077b3] transition-colors">
                    Submit Verification Request
                </button>

                <button wire:click="$set('step', 1)"
                    class="mt-3 w-full text-center text-sm text-zinc-400 hover:text-zinc-200 transition-colors">
                    ← Back
                </button>
            </div>
        @endif
    </div>
</div>
```

- [ ] **Step 3: Run pint**

Run: `vendor/bin/pint --dirty --format agent`

- [ ] **Step 4: Commit**

```bash
git add app/Livewire/Portal/Onboarding.php \
        resources/views/livewire/portal/onboarding.blade.php
git commit -m "feat: add Onboarding Livewire component with student/employee role selector"
```

---

### Task 4: OnboardingNotice Banner + Portal Layout

**Files:**
- Create: `app/Livewire/Portal/OnboardingNotice.php`
- Create: `resources/views/livewire/portal/onboarding-notice.blade.php`
- Modify: `resources/views/components/layouts/portal.blade.php`

- [ ] **Step 1: Create the Livewire component**

Run: `php artisan make:livewire --no-interaction Portal/OnboardingNotice`

Replace `app/Livewire/Portal/OnboardingNotice.php` with:

```php
<?php

namespace App\Livewire\Portal;

use App\Enums\OnboardingStatus;
use App\Models\Office;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class OnboardingNotice extends Component
{
    public bool $showOfficeSelector = false;

    public ?int $selectedOfficeId = null;

    #[Computed]
    public function offices()
    {
        return Office::active()->orderBy('name')->get();
    }

    public function continueAsStudent(): void
    {
        auth()->user()->update([
            'onboarding_status' => null,
            'pending_office_id' => null,
        ]);
    }

    public function showReapplyForm(): void
    {
        $this->showOfficeSelector = true;
        $this->selectedOfficeId = null;
    }

    public function reapply(): void
    {
        $this->validate(['selectedOfficeId' => ['required', 'integer', 'exists:offices,id']]);

        $office = Office::findOrFail($this->selectedOfficeId);
        $user = auth()->user();

        $user->update([
            'onboarding_status' => OnboardingStatus::PendingEmployee,
            'pending_office_id' => $office->id,
        ]);

        $admins = $office->users()->role('office_admin')->get();
        \Illuminate\Support\Facades\Notification::send(
            $admins,
            new \App\Notifications\EmployeeVerificationRequestedNotification($user, $office)
        );

        $this->showOfficeSelector = false;
    }

    public function render(): View
    {
        return view('livewire.portal.onboarding-notice');
    }
}
```

- [ ] **Step 2: Create the blade view**

Create `resources/views/livewire/portal/onboarding-notice.blade.php`:

```blade
@php $status = auth()->user()?->onboarding_status; @endphp

@if ($status === \App\Enums\OnboardingStatus::PendingEmployee)
    <div class="border-b border-yellow-500/30 bg-yellow-500/10 px-4 py-3 text-sm text-yellow-300">
        <div class="mx-auto flex max-w-4xl items-center gap-2">
            <span>⏳</span>
            <span>
                Your request to join <strong>{{ auth()->user()->pendingOffice?->name }}</strong>
                is pending verification. You can still submit requests while you wait.
            </span>
        </div>
    </div>
@elseif ($status === \App\Enums\OnboardingStatus::Rejected)
    <div class="border-b border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-300">
        <div class="mx-auto max-w-4xl">
            <div class="flex items-center gap-2 mb-2">
                <span>✗</span>
                <span>Your employee verification request was not approved.</span>
            </div>

            @if ($showOfficeSelector)
                <div class="mt-3 flex items-end gap-3">
                    <div class="flex-1">
                        <select wire:model="selectedOfficeId"
                            class="w-full rounded-lg border border-zinc-600 bg-zinc-800 px-3 py-1.5 text-sm text-white focus:border-[#0089CB] focus:outline-none">
                            <option value="">Select an office…</option>
                            @foreach ($this->offices as $office)
                                <option value="{{ $office->id }}">{{ $office->name }}</option>
                            @endforeach
                        </select>
                        @error('selectedOfficeId') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>
                    <button wire:click="reapply"
                        class="rounded-lg bg-[#0089CB] px-4 py-1.5 text-sm font-semibold text-white hover:bg-[#0077b3] transition-colors">
                        Submit
                    </button>
                    <button wire:click="$set('showOfficeSelector', false)"
                        class="text-sm text-zinc-400 hover:text-zinc-200 transition-colors">
                        Cancel
                    </button>
                </div>
            @else
                <div class="flex items-center gap-4 mt-1">
                    <button wire:click="showReapplyForm"
                        class="text-sm font-medium text-red-200 underline hover:text-white transition-colors">
                        Apply to a Different Office
                    </button>
                    <button wire:click="continueAsStudent"
                        class="text-sm text-zinc-400 hover:text-zinc-200 transition-colors">
                        Continue as Student
                    </button>
                </div>
            @endif
        </div>
    </div>
@endif
```

- [ ] **Step 3: Include the notice in the portal layout**

In `resources/views/components/layouts/portal.blade.php`, add `@livewire('portal.onboarding-notice')` between the `</nav>` and `<main>` tags:

```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    @include('partials.head')
</head>
<body class="min-h-screen bg-zinc-900 text-zinc-100 antialiased">
    <nav class="sticky top-0 z-50 border-b border-zinc-700/60 bg-zinc-900/90 backdrop-blur-sm">
        <div class="mx-auto flex max-w-4xl items-center justify-between px-4 py-3">
            <a href="{{ route('portal.tickets.index') }}" wire:navigate
               class="text-base font-bold tracking-tight text-white">BUSRS</a>
            <div class="flex items-center gap-3">
                <a href="{{ route('portal.tickets.index') }}" wire:navigate
                   class="text-sm text-zinc-400 hover:text-zinc-200 transition-colors">My Requests</a>
                <a href="{{ route('portal.tickets.create') }}" wire:navigate
                   class="rounded-lg bg-[#0089CB] px-3.5 py-1.5 text-sm font-semibold text-white hover:bg-[#0077b3] transition-colors">
                    + New Request
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-sm text-zinc-400 hover:text-zinc-200 transition-colors">
                        Log out
                    </button>
                </form>
            </div>
        </div>
    </nav>

    @livewire('portal.onboarding-notice')

    <main class="mx-auto max-w-4xl px-4 py-8">
        {{ $slot }}
    </main>
</body>
</html>
```

- [ ] **Step 4: Run pint**

Run: `vendor/bin/pint --dirty --format agent`

- [ ] **Step 5: Commit**

```bash
git add app/Livewire/Portal/OnboardingNotice.php \
        resources/views/livewire/portal/onboarding-notice.blade.php \
        resources/views/components/layouts/portal.blade.php
git commit -m "feat: add OnboardingNotice banner for pending/rejected employee state"
```

---

### Task 5: Email Notifications

**Files:**
- Create: `app/Notifications/EmployeeVerificationRequestedNotification.php`
- Create: `app/Notifications/EmployeeVerificationResultNotification.php`

- [ ] **Step 1: Create EmployeeVerificationRequestedNotification**

Run: `php artisan make:notification --no-interaction EmployeeVerificationRequestedNotification`

Replace the generated file with:

```php
<?php

namespace App\Notifications;

use App\Models\Office;
use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmployeeVerificationRequestedNotification extends Notification
{
    public function __construct(
        public readonly User $employee,
        public readonly Office $office,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Employee Verification Request — '.$this->office->name)
            ->greeting('Hello,')
            ->line($this->employee->name.' ('.$this->employee->email.') is requesting verification as an employee of '.$this->office->name.'.')
            ->action('Review in Admin Panel', url('/admin/employee-requests'))
            ->line('Please approve or reject the request at your earliest convenience.');
    }
}
```

- [ ] **Step 2: Create EmployeeVerificationResultNotification**

Run: `php artisan make:notification --no-interaction EmployeeVerificationResultNotification`

Replace the generated file with:

```php
<?php

namespace App\Notifications;

use App\Models\Office;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmployeeVerificationResultNotification extends Notification
{
    public function __construct(
        public readonly bool $approved,
        public readonly ?Office $office = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        if ($this->approved) {
            return (new MailMessage)
                ->subject('Employee Verification Approved')
                ->greeting('Hello, '.$notifiable->name.'!')
                ->line('Your affiliation with '.$this->office->name.' has been verified.')
                ->line('You now have staff access to the admin panel.')
                ->action('Go to Admin Panel', url('/admin'));
        }

        return (new MailMessage)
            ->subject('Employee Verification Not Approved')
            ->greeting('Hello, '.$notifiable->name.',')
            ->line('Your employee verification request was not approved.')
            ->line('You can still submit service requests as a student, or apply to a different office.')
            ->action('Go to Portal', url('/portal/tickets'));
    }
}
```

- [ ] **Step 3: Run pint**

Run: `vendor/bin/pint --dirty --format agent`

- [ ] **Step 4: Commit**

```bash
git add app/Notifications/
git commit -m "feat: add EmployeeVerificationRequestedNotification and EmployeeVerificationResultNotification"
```

---

### Task 6: ApproveEmployeeAction & RejectEmployeeAction

**Files:**
- Create: `app/Filament/Actions/ApproveEmployeeAction.php`
- Create: `app/Filament/Actions/RejectEmployeeAction.php`

- [ ] **Step 1: Create ApproveEmployeeAction**

Create `app/Filament/Actions/ApproveEmployeeAction.php`:

```php
<?php

namespace App\Filament\Actions;

use App\Models\Office;
use App\Notifications\EmployeeVerificationResultNotification;
use Filament\Actions\Action;
use Illuminate\Support\Facades\DB;

class ApproveEmployeeAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'approve_employee';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Approve')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading('Approve Employee Verification')
            ->modalDescription('This will grant the user staff access for their selected office.')
            ->action(function ($record): void {
                $office = Office::findOrFail($record->pending_office_id);

                DB::transaction(function () use ($record, $office): void {
                    $record->syncRoles(['staff']);
                    $record->offices()->attach($office->id);
                    $record->update([
                        'onboarding_status' => null,
                        'pending_office_id' => null,
                    ]);
                });

                $record->notify(new EmployeeVerificationResultNotification(approved: true, office: $office));
            })
            ->successNotificationTitle('Employee verified and granted staff access');
    }
}
```

- [ ] **Step 2: Create RejectEmployeeAction**

Create `app/Filament/Actions/RejectEmployeeAction.php`:

```php
<?php

namespace App\Filament\Actions;

use App\Enums\OnboardingStatus;
use App\Notifications\EmployeeVerificationResultNotification;
use Filament\Actions\Action;

class RejectEmployeeAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'reject_employee';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Reject')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Reject Employee Verification')
            ->modalDescription('The user will be notified and can re-apply or continue as a student.')
            ->action(function ($record): void {
                $record->update([
                    'onboarding_status' => OnboardingStatus::Rejected,
                    'pending_office_id' => null,
                ]);

                $record->notify(new EmployeeVerificationResultNotification(approved: false));
            })
            ->successNotificationTitle('Verification request rejected');
    }
}
```

- [ ] **Step 3: Run pint**

Run: `vendor/bin/pint --dirty --format agent`

- [ ] **Step 4: Commit**

```bash
git add app/Filament/Actions/ApproveEmployeeAction.php \
        app/Filament/Actions/RejectEmployeeAction.php
git commit -m "feat: add ApproveEmployeeAction and RejectEmployeeAction Filament actions"
```

---

### Task 7: EmployeeRequestResource

**Files:**
- Create: `app/Filament/Resources/EmployeeRequestResource.php`
- Create: `app/Filament/Resources/EmployeeRequestResource/Pages/ListEmployeeRequests.php`

- [ ] **Step 1: Create the resource class**

Create `app/Filament/Resources/EmployeeRequestResource.php`:

```php
<?php

namespace App\Filament\Resources;

use App\Enums\OnboardingStatus;
use App\Filament\Actions\ApproveEmployeeAction;
use App\Filament\Actions\RejectEmployeeAction;
use App\Filament\Resources\EmployeeRequestResource\Pages;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class EmployeeRequestResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserPlus;

    protected static string|UnitEnum|null $navigationGroup = 'Administration';

    protected static ?int $navigationSort = 4;

    protected static ?string $modelLabel = 'Employee Request';

    protected static ?string $pluralModelLabel = 'Employee Requests';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'office_admin']) ?? false;
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->where('onboarding_status', OnboardingStatus::PendingEmployee)
            ->with('pendingOffice');

        $user = auth()->user();

        if ($user === null) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->hasRole('super_admin')) {
            return $query;
        }

        $officeIds = $user->offices()->pluck('offices.id');

        return $query->whereIn('pending_office_id', $officeIds);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'asc')
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('pendingOffice.name')
                    ->label('Requested Office')
                    ->badge(),
                TextColumn::make('onboarding_completed_at')
                    ->label('Applied')
                    ->since()
                    ->sortable(),
            ])
            ->recordActions([
                ApproveEmployeeAction::make(),
                RejectEmployeeAction::make(),
            ]);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmployeeRequests::route('/'),
        ];
    }
}
```

- [ ] **Step 2: Create the ListEmployeeRequests page**

Create `app/Filament/Resources/EmployeeRequestResource/Pages/ListEmployeeRequests.php`:

```php
<?php

namespace App\Filament\Resources\EmployeeRequestResource\Pages;

use App\Filament\Resources\EmployeeRequestResource;
use Filament\Resources\Pages\ListRecords;

class ListEmployeeRequests extends ListRecords
{
    protected static string $resource = EmployeeRequestResource::class;
}
```

- [ ] **Step 3: Run pint**

Run: `vendor/bin/pint --dirty --format agent`

- [ ] **Step 4: Commit**

```bash
git add app/Filament/Resources/EmployeeRequestResource.php \
        app/Filament/Resources/EmployeeRequestResource/
git commit -m "feat: add EmployeeRequestResource for admin approval/rejection of employee requests"
```

---

### Task 8: Tests

**Files:**
- Create: `tests/Feature/EmployeeOnboardingTest.php`

- [ ] **Step 1: Generate the test file**

Run: `php artisan make:test --pest --no-interaction EmployeeOnboardingTest`

- [ ] **Step 2: Write the tests**

Replace `tests/Feature/EmployeeOnboardingTest.php` with:

```php
<?php

use App\Enums\OnboardingStatus;
use App\Models\Office;
use App\Models\User;
use App\Notifications\EmployeeVerificationRequestedNotification;
use App\Notifications\EmployeeVerificationResultNotification;
use Illuminate\Support\Facades\Notification;

// ── Middleware ──────────────────────────────────────────────────────────────

test('unauthenticated users are redirected away from portal tickets', function () {
    $this->get(route('portal.tickets.index'))->assertRedirect(route('login'));
});

test('authenticated user with pending onboarding is redirected to onboarding page', function () {
    $user = User::factory()->needsOnboarding()->create()->assignRole('student');

    $this->actingAs($user)
        ->get(route('portal.tickets.index'))
        ->assertRedirect(route('portal.onboarding'));
});

test('authenticated user with completed onboarding can access portal tickets', function () {
    $user = User::factory()->create()->assignRole('student');

    $this->actingAs($user)
        ->get(route('portal.tickets.index'))
        ->assertOk();
});

test('onboarding page redirects already-onboarded users to tickets', function () {
    $user = User::factory()->create()->assignRole('student');

    $this->actingAs($user)
        ->get(route('portal.onboarding'))
        ->assertRedirect(route('portal.tickets.index'));
});

// ── Onboarding Component ────────────────────────────────────────────────────

test('choosing student sets onboarding_completed_at and redirects', function () {
    $user = User::factory()->needsOnboarding()->create()->assignRole('student');

    Livewire\Livewire::actingAs($user)
        ->test(\App\Livewire\Portal\Onboarding::class)
        ->call('chooseStudent');

    expect($user->fresh()->onboarding_completed_at)->not->toBeNull();
});

test('submitting employee request sets pending status and notifies office admins', function () {
    Notification::fake();

    $office = Office::factory()->create();
    $admin = User::factory()->create()->assignRole('office_admin');
    $admin->offices()->attach($office->id);

    $user = User::factory()->needsOnboarding()->create()->assignRole('student');

    Livewire\Livewire::actingAs($user)
        ->test(\App\Livewire\Portal\Onboarding::class)
        ->set('step', 2)
        ->set('selectedOfficeId', $office->id)
        ->call('submitEmployeeRequest');

    $fresh = $user->fresh();
    expect($fresh->onboarding_status)->toBe(OnboardingStatus::PendingEmployee);
    expect($fresh->pending_office_id)->toBe($office->id);
    expect($fresh->onboarding_completed_at)->not->toBeNull();

    Notification::assertSentTo($admin, EmployeeVerificationRequestedNotification::class);
});

test('submitting employee request without selecting office fails validation', function () {
    $user = User::factory()->needsOnboarding()->create()->assignRole('student');

    Livewire\Livewire::actingAs($user)
        ->test(\App\Livewire\Portal\Onboarding::class)
        ->set('step', 2)
        ->call('submitEmployeeRequest')
        ->assertHasErrors(['selectedOfficeId']);
});

// ── OnboardingNotice — Continue as Student ──────────────────────────────────

test('continue as student clears onboarding status', function () {
    $user = User::factory()->rejectedEmployee()->create()->assignRole('student');

    Livewire\Livewire::actingAs($user)
        ->test(\App\Livewire\Portal\OnboardingNotice::class)
        ->call('continueAsStudent');

    expect($user->fresh()->onboarding_status)->toBeNull();
});

// ── OnboardingNotice — Re-apply ─────────────────────────────────────────────

test('reapply sets new pending office and notifies new admins', function () {
    Notification::fake();

    $newOffice = Office::factory()->create();
    $newAdmin = User::factory()->create()->assignRole('office_admin');
    $newAdmin->offices()->attach($newOffice->id);

    $user = User::factory()->rejectedEmployee()->create()->assignRole('student');

    Livewire\Livewire::actingAs($user)
        ->test(\App\Livewire\Portal\OnboardingNotice::class)
        ->set('selectedOfficeId', $newOffice->id)
        ->call('reapply');

    expect($user->fresh()->onboarding_status)->toBe(OnboardingStatus::PendingEmployee);
    expect($user->fresh()->pending_office_id)->toBe($newOffice->id);

    Notification::assertSentTo($newAdmin, EmployeeVerificationRequestedNotification::class);
});

// ── Admin Approve/Reject ────────────────────────────────────────────────────

test('approving an employee request grants staff role, attaches office, and notifies user', function () {
    Notification::fake();

    $office = Office::factory()->create();
    $pendingUser = User::factory()->pendingEmployee($office)->create()->assignRole('student');
    $admin = User::factory()->create()->assignRole('super_admin');

    Livewire\Livewire::actingAs($admin)
        ->test(\App\Filament\Resources\EmployeeRequestResource\Pages\ListEmployeeRequests::class)
        ->callTableAction('approve_employee', $pendingUser);

    $fresh = $pendingUser->fresh();
    expect($fresh->hasRole('staff'))->toBeTrue();
    expect($fresh->offices()->where('offices.id', $office->id)->exists())->toBeTrue();
    expect($fresh->onboarding_status)->toBeNull();
    expect($fresh->pending_office_id)->toBeNull();

    Notification::assertSentTo($pendingUser, EmployeeVerificationResultNotification::class,
        fn ($n) => $n->approved === true
    );
});

test('rejecting an employee request sets rejected status and notifies user', function () {
    Notification::fake();

    $office = Office::factory()->create();
    $pendingUser = User::factory()->pendingEmployee($office)->create()->assignRole('student');
    $admin = User::factory()->create()->assignRole('super_admin');

    Livewire\Livewire::actingAs($admin)
        ->test(\App\Filament\Resources\EmployeeRequestResource\Pages\ListEmployeeRequests::class)
        ->callTableAction('reject_employee', $pendingUser);

    $fresh = $pendingUser->fresh();
    expect($fresh->onboarding_status)->toBe(OnboardingStatus::Rejected);
    expect($fresh->pending_office_id)->toBeNull();

    Notification::assertSentTo($pendingUser, EmployeeVerificationResultNotification::class,
        fn ($n) => $n->approved === false
    );
});

// ── EmployeeRequestResource RBAC ────────────────────────────────────────────

test('office_admin only sees pending requests for their own office', function () {
    $myOffice = Office::factory()->create();
    $otherOffice = Office::factory()->create();

    $admin = User::factory()->create()->assignRole('office_admin');
    $admin->offices()->attach($myOffice->id);

    $myRequest = User::factory()->pendingEmployee($myOffice)->create()->assignRole('student');
    $otherRequest = User::factory()->pendingEmployee($otherOffice)->create()->assignRole('student');

    \Illuminate\Support\Facades\Auth::login($admin);

    $ids = \App\Filament\Resources\EmployeeRequestResource::getEloquentQuery()->pluck('id');
    expect($ids)->toContain($myRequest->id);
    expect($ids)->not->toContain($otherRequest->id);
});
```

- [ ] **Step 3: Run the tests**

Run: `php artisan test --compact --filter=EmployeeOnboarding`

Expected: all tests pass.

- [ ] **Step 4: Run pint**

Run: `vendor/bin/pint --dirty --format agent`

- [ ] **Step 5: Commit**

```bash
git add tests/Feature/EmployeeOnboardingTest.php
git commit -m "test: add EmployeeOnboardingTest covering middleware, flows, admin actions, RBAC"
```
