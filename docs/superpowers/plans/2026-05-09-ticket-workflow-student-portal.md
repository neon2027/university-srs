# Ticket Workflow — Student Portal Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.
> **Do NOT run any git commit commands.** The user commits manually.

**Goal:** Build the student-facing portal at `/portal/*` — ticket submission (5-step Livewire form with dynamic fields), ticket list, and ticket detail with status timeline and chat.

**Architecture:** Three Livewire full-page components under a shared portal layout. Routes are guarded by `auth + verified + role:student` middleware. All ticket data writes to the existing `tickets`, `ticket_history`, `ticket_messages`, and `ticket_attachments` tables.

**Tech Stack:** Laravel 13, Livewire 4, Flux UI 2, Tailwind CSS v4, Pest 4, spatie/laravel-permission v7.

---

## File Map

| Action | Path | Purpose |
|--------|------|---------|
| Create | `resources/views/components/layouts/portal.blade.php` | Shared portal layout (nav + slot) |
| Modify | `routes/web.php` | Add `/portal/*` routes |
| Create | `app/Livewire/Portal/TicketList.php` | My Tickets list component |
| Create | `resources/views/livewire/portal/ticket-list.blade.php` | Ticket list view |
| Create | `app/Livewire/Portal/CreateTicket.php` | 5-step submission form component |
| Create | `resources/views/livewire/portal/create-ticket.blade.php` | Submission form view |
| Create | `app/Livewire/Portal/TicketDetail.php` | Ticket detail (timeline + chat) |
| Create | `resources/views/livewire/portal/ticket-detail.blade.php` | Detail view |
| Create | `tests/Feature/Portal/PortalAccessTest.php` | Route + middleware tests |
| Create | `tests/Feature/Portal/TicketListTest.php` | List component tests |
| Create | `tests/Feature/Portal/CreateTicketStepsTest.php` | Steps 1–3 navigation tests |
| Create | `tests/Feature/Portal/CreateTicketFieldsTest.php` | Step 4 dynamic field tests |
| Create | `tests/Feature/Portal/CreateTicketSubmitTest.php` | Step 5 submit + ticket creation tests |
| Create | `tests/Feature/Portal/TicketDetailTest.php` | Timeline tests |
| Create | `tests/Feature/Portal/TicketChatTest.php` | Chat + seen tracking tests |

---

## Task 1: Portal Routes, Layout & Access Guards

**Files:**
- Create: `resources/views/components/layouts/portal.blade.php`
- Modify: `routes/web.php`
- Create: `tests/Feature/Portal/PortalAccessTest.php`

- [ ] **Step 1: Write failing access tests**

```bash
php artisan make:test --pest PortalAccessTest
```

Move the generated file to `tests/Feature/Portal/` (create the directory first):
```bash
mkdir -p tests/Feature/Portal
mv tests/Feature/PortalAccessTest.php tests/Feature/Portal/PortalAccessTest.php
```

Replace the file contents:

```php
<?php

use App\Models\User;
use Database\Seeders\RoleSeeder;

beforeEach(fn () => $this->seed(RoleSeeder::class));

test('unauthenticated user is redirected from portal', function () {
    $this->get('/portal/tickets')->assertRedirect();
});

test('staff role cannot access portal', function () {
    $user = User::factory()->create();
    $user->assignRole('staff');

    $this->actingAs($user)
        ->get('/portal/tickets')
        ->assertForbidden();
});

test('student can access portal ticket list', function () {
    $user = User::factory()->create();
    $user->assignRole('student');

    $this->actingAs($user)
        ->get('/portal/tickets')
        ->assertOk();
});

test('portal named routes exist', function () {
    expect(route('portal.tickets.index'))->toContain('/portal/tickets');
    expect(route('portal.tickets.create'))->toContain('/portal/tickets/create');
});
```

- [ ] **Step 2: Run tests — expect failures**

```bash
php artisan test --compact tests/Feature/Portal/PortalAccessTest.php
```

Expected: 3–4 failures (routes don't exist yet).

- [ ] **Step 3: Create the portal layout**

Create `resources/views/components/layouts/portal.blade.php`:

```html
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
                <livewire:actions.logout />
            </div>
        </div>
    </nav>
    <main class="mx-auto max-w-4xl px-4 py-8">
        {{ $slot }}
    </main>
</body>
</html>
```

- [ ] **Step 4: Create stub Livewire components so routes can load**

```bash
php artisan make:livewire Portal/TicketList --no-interaction
php artisan make:livewire Portal/CreateTicket --no-interaction
php artisan make:livewire Portal/TicketDetail --no-interaction
```

In each generated component, add the layout attribute. Open `app/Livewire/Portal/TicketList.php` and replace:

```php
<?php

namespace App\Livewire\Portal;

use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.portal')]
class TicketList extends Component
{
    public function render(): View
    {
        return view('livewire.portal.ticket-list');
    }
}
```

Open `app/Livewire/Portal/CreateTicket.php` and replace:

```php
<?php

namespace App\Livewire\Portal;

use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.portal')]
class CreateTicket extends Component
{
    public function render(): View
    {
        return view('livewire.portal.create-ticket');
    }
}
```

Open `app/Livewire/Portal/TicketDetail.php` and replace:

```php
<?php

namespace App\Livewire\Portal;

use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('components.layouts.portal')]
class TicketDetail extends Component
{
    #[Locked]
    public string $ulid = '';

    public function mount(string $ulid): void
    {
        $this->ulid = $ulid;
    }

    public function render(): View
    {
        return view('livewire.portal.ticket-detail');
    }
}
```

Replace each generated stub view with a minimal placeholder. `resources/views/livewire/portal/ticket-list.blade.php`:

```html
<div>Ticket List</div>
```

`resources/views/livewire/portal/create-ticket.blade.php`:

```html
<div>Create Ticket</div>
```

`resources/views/livewire/portal/ticket-detail.blade.php`:

```html
<div>Ticket Detail</div>
```

- [ ] **Step 5: Add portal routes to `routes/web.php`**

Open `routes/web.php`. Add after the existing `require __DIR__.'/settings.php';` line:

```php
use App\Livewire\Portal\CreateTicket;
use App\Livewire\Portal\TicketDetail;
use App\Livewire\Portal\TicketList;

Route::middleware(['auth', 'verified', 'role:student'])
    ->prefix('portal')
    ->name('portal.')
    ->group(function () {
        Route::get('/tickets', TicketList::class)->name('tickets.index');
        Route::get('/tickets/create', CreateTicket::class)->name('tickets.create');
        Route::get('/tickets/{ulid}', TicketDetail::class)->name('tickets.show');
    });
```

- [ ] **Step 6: Run tests — expect all pass**

```bash
php artisan test --compact tests/Feature/Portal/PortalAccessTest.php
```

Expected: 4 passed.

- [ ] **Step 7: Run Pint**

```bash
vendor/bin/pint --dirty --format agent
```

---

## Task 2: My Tickets List

**Files:**
- Modify: `app/Livewire/Portal/TicketList.php`
- Modify: `resources/views/livewire/portal/ticket-list.blade.php`
- Create: `tests/Feature/Portal/TicketListTest.php`

- [ ] **Step 1: Write failing tests**

Create `tests/Feature/Portal/TicketListTest.php`:

```php
<?php

use App\Enums\TicketStatus;
use App\Models\Office;
use App\Models\ServiceCategory;
use App\Models\ServiceType;
use App\Models\Ticket;
use App\Models\User;
use Database\Seeders\RoleSeeder;

beforeEach(fn () => $this->seed(RoleSeeder::class));

function makeStudent(): User
{
    $user = User::factory()->create();
    $user->assignRole('student');
    return $user;
}

function makeTicket(User $requester): Ticket
{
    $office = Office::factory()->create();
    $category = ServiceCategory::factory()->for($office)->create();
    $serviceType = ServiceType::factory()->for($category)->create();
    return Ticket::factory()
        ->for($requester, 'requester')
        ->for($office)
        ->for($serviceType)
        ->create();
}

test('student sees only their own tickets', function () {
    $student = makeStudent();
    $other = makeStudent();

    $mine = makeTicket($student);
    $mine->update(['subject' => 'My Request']);
    $theirs = makeTicket($other);
    $theirs->update(['subject' => 'Their Request']);

    $this->actingAs($student)
        ->get(route('portal.tickets.index'))
        ->assertSee('My Request')
        ->assertDontSee('Their Request');
});

test('empty state shown when student has no tickets', function () {
    $student = makeStudent();

    $this->actingAs($student)
        ->get(route('portal.tickets.index'))
        ->assertSee('No requests yet');
});

test('ticket status badge is shown', function () {
    $student = makeStudent();
    $ticket = makeTicket($student);
    $ticket->update(['status' => TicketStatus::Pending]);

    $this->actingAs($student)
        ->get(route('portal.tickets.index'))
        ->assertSee('Pending');
});

test('ticket ulid is shown on list', function () {
    $student = makeStudent();
    $ticket = makeTicket($student);

    $this->actingAs($student)
        ->get(route('portal.tickets.index'))
        ->assertSee($ticket->ulid);
});
```

- [ ] **Step 2: Run tests — expect failures**

```bash
php artisan test --compact tests/Feature/Portal/TicketListTest.php
```

Expected: failures (view shows stub text).

- [ ] **Step 3: Implement TicketList component**

Replace `app/Livewire/Portal/TicketList.php`:

```php
<?php

namespace App\Livewire\Portal;

use App\Models\Ticket;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.portal')]
class TicketList extends Component
{
    public function render(): View
    {
        $tickets = Ticket::with(['office', 'serviceType'])
            ->where('requester_id', auth()->id())
            ->latest()
            ->limit(50)
            ->get();

        return view('livewire.portal.ticket-list', compact('tickets'));
    }
}
```

- [ ] **Step 4: Implement ticket list view**

Replace `resources/views/livewire/portal/ticket-list.blade.php`:

```html
<div>
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold">My Requests</h1>
        <a href="{{ route('portal.tickets.create') }}" wire:navigate
           class="rounded-lg bg-[#0089CB] px-4 py-2 text-sm font-semibold text-white hover:bg-[#0077b3]">
            + New Request
        </a>
    </div>

    @if ($tickets->isEmpty())
        <div class="rounded-xl border border-zinc-700/60 bg-zinc-800/50 px-6 py-12 text-center">
            <p class="text-zinc-400">No requests yet.</p>
            <a href="{{ route('portal.tickets.create') }}" wire:navigate
               class="mt-4 inline-block text-sm text-[#0089CB] hover:underline">
                Submit your first request →
            </a>
        </div>
    @else
        <div class="space-y-3">
            @foreach ($tickets as $ticket)
                <a href="{{ route('portal.tickets.show', $ticket->ulid) }}" wire:navigate
                   class="block rounded-xl border border-zinc-700/60 bg-zinc-800/50 px-5 py-4 hover:border-zinc-600 transition-colors">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="truncate font-semibold">{{ $ticket->subject }}</p>
                            <p class="mt-0.5 text-sm text-zinc-400">
                                {{ $ticket->office->name }} · {{ $ticket->serviceType->name }}
                            </p>
                        </div>
                        <span @class([
                            'shrink-0 rounded-full px-2.5 py-0.5 text-xs font-semibold',
                            'bg-zinc-700 text-zinc-300'       => $ticket->status === \App\Enums\TicketStatus::Pending,
                            'bg-blue-900/50 text-blue-300'    => $ticket->status === \App\Enums\TicketStatus::Assigned,
                            'bg-amber-900/50 text-amber-300'  => $ticket->status === \App\Enums\TicketStatus::InProgress,
                            'bg-purple-900/50 text-purple-300'=> $ticket->status === \App\Enums\TicketStatus::OnHold,
                            'bg-sky-900/50 text-sky-300'      => $ticket->status === \App\Enums\TicketStatus::Forwarded,
                            'bg-green-900/50 text-green-300'  => $ticket->status === \App\Enums\TicketStatus::Resolved,
                            'bg-zinc-600/50 text-zinc-400'    => in_array($ticket->status, [
                                \App\Enums\TicketStatus::Closed,
                                \App\Enums\TicketStatus::Cancelled,
                            ]),
                        ])>{{ $ticket->status->name }}</span>
                    </div>
                    <p class="mt-2 text-xs text-zinc-500">
                        <span class="font-mono">{{ $ticket->ulid }}</span>
                        · {{ $ticket->updated_at->diffForHumans() }}
                    </p>
                </a>
            @endforeach
        </div>
    @endif
</div>
```

- [ ] **Step 5: Run tests — expect all pass**

```bash
php artisan test --compact tests/Feature/Portal/TicketListTest.php
```

Expected: 4 passed.

- [ ] **Step 6: Run Pint**

```bash
vendor/bin/pint --dirty --format agent
```

---

## Task 3: Submission Form — Steps 1–3 (Office → Category → Service)

**Files:**
- Modify: `app/Livewire/Portal/CreateTicket.php`
- Modify: `resources/views/livewire/portal/create-ticket.blade.php`
- Create: `tests/Feature/Portal/CreateTicketStepsTest.php`

- [ ] **Step 1: Write failing tests**

Create `tests/Feature/Portal/CreateTicketStepsTest.php`:

```php
<?php

use App\Livewire\Portal\CreateTicket;
use App\Models\Office;
use App\Models\ServiceCategory;
use App\Models\ServiceType;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;

beforeEach(fn () => $this->seed(RoleSeeder::class));

function student(): User
{
    $u = User::factory()->create();
    $u->assignRole('student');
    return $u;
}

test('form starts on step 1', function () {
    Livewire::actingAs(student())
        ->test(CreateTicket::class)
        ->assertSet('step', 1);
});

test('cannot advance from step 1 without an office', function () {
    Livewire::actingAs(student())
        ->test(CreateTicket::class)
        ->call('nextStep')
        ->assertSet('step', 1)
        ->assertHasErrors(['officeId']);
});

test('advances to step 2 after selecting office', function () {
    $office = Office::factory()->create();

    Livewire::actingAs(student())
        ->test(CreateTicket::class)
        ->set('officeId', $office->id)
        ->call('nextStep')
        ->assertSet('step', 2)
        ->assertHasNoErrors();
});

test('categories shown are filtered to selected office', function () {
    $office = Office::factory()->create();
    $other  = Office::factory()->create();
    ServiceCategory::factory()->for($office)->create(['name' => 'My Category']);
    ServiceCategory::factory()->for($other)->create(['name' => 'Other Category']);

    Livewire::actingAs(student())
        ->test(CreateTicket::class)
        ->set('officeId', $office->id)
        ->set('step', 2)
        ->assertSee('My Category')
        ->assertDontSee('Other Category');
});

test('changing office resets category and service', function () {
    $office1   = Office::factory()->create();
    $office2   = Office::factory()->create();
    $category  = ServiceCategory::factory()->for($office1)->create();

    Livewire::actingAs(student())
        ->test(CreateTicket::class)
        ->set('officeId', $office1->id)
        ->set('serviceCategoryId', $category->id)
        ->set('officeId', $office2->id)
        ->assertSet('serviceCategoryId', null)
        ->assertSet('serviceTypeId', null);
});

test('advances to step 3 after selecting category', function () {
    $office   = Office::factory()->create();
    $category = ServiceCategory::factory()->for($office)->create();

    Livewire::actingAs(student())
        ->test(CreateTicket::class)
        ->set('officeId', $office->id)
        ->set('step', 2)
        ->set('serviceCategoryId', $category->id)
        ->call('nextStep')
        ->assertSet('step', 3)
        ->assertHasNoErrors();
});

test('advances to step 4 after selecting service type', function () {
    $office      = Office::factory()->create();
    $category    = ServiceCategory::factory()->for($office)->create();
    $serviceType = ServiceType::factory()->for($category)->create();

    Livewire::actingAs(student())
        ->test(CreateTicket::class)
        ->set('officeId', $office->id)
        ->set('serviceCategoryId', $category->id)
        ->set('step', 3)
        ->set('serviceTypeId', $serviceType->id)
        ->call('nextStep')
        ->assertSet('step', 4)
        ->assertHasNoErrors();
});

test('can navigate back from step 2 to step 1', function () {
    Livewire::actingAs(student())
        ->test(CreateTicket::class)
        ->set('step', 2)
        ->call('prevStep')
        ->assertSet('step', 1);
});
```

- [ ] **Step 2: Run tests — expect failures**

```bash
php artisan test --compact tests/Feature/Portal/CreateTicketStepsTest.php
```

- [ ] **Step 3: Implement CreateTicket component (steps 1–3)**

Replace `app/Livewire/Portal/CreateTicket.php`:

```php
<?php

namespace App\Livewire\Portal;

use App\Models\Office;
use App\Models\ServiceCategory;
use App\Models\ServiceType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.portal')]
class CreateTicket extends Component
{
    public int $step = 1;
    public ?int $officeId = null;
    public ?int $serviceCategoryId = null;
    public ?int $serviceTypeId = null;
    public array $customFields = [];
    public array $fileUploads = [];

    public function updatedOfficeId(): void
    {
        $this->serviceCategoryId = null;
        $this->serviceTypeId = null;
        $this->customFields = [];
        $this->fileUploads = [];
    }

    public function updatedServiceCategoryId(): void
    {
        $this->serviceTypeId = null;
        $this->customFields = [];
        $this->fileUploads = [];
    }

    public function updatedServiceTypeId(): void
    {
        $this->customFields = [];
        $this->fileUploads = [];
    }

    #[Computed]
    public function offices(): Collection
    {
        return Office::active()->orderBy('sort_order')->orderBy('name')->get();
    }

    #[Computed]
    public function categories(): Collection
    {
        if (! $this->officeId) {
            return collect();
        }

        return ServiceCategory::where('office_id', $this->officeId)
            ->active()
            ->orderBy('sort_order')
            ->get();
    }

    #[Computed]
    public function serviceTypes(): Collection
    {
        if (! $this->serviceCategoryId) {
            return collect();
        }

        return ServiceType::where('service_category_id', $this->serviceCategoryId)
            ->active()
            ->orderBy('sort_order')
            ->get();
    }

    public function nextStep(): void
    {
        $this->validate(match ($this->step) {
            1 => ['officeId' => 'required|exists:offices,id'],
            2 => ['serviceCategoryId' => 'required|exists:service_categories,id'],
            3 => ['serviceTypeId' => 'required|exists:service_types,id'],
            default => [],
        });

        $this->step++;
    }

    public function prevStep(): void
    {
        if ($this->step > 1) {
            $this->step--;
        }
    }

    public function render(): View
    {
        return view('livewire.portal.create-ticket');
    }
}
```

- [ ] **Step 4: Implement create-ticket view (steps 1–3, placeholders for 4–5)**

Replace `resources/views/livewire/portal/create-ticket.blade.php`:

```html
<div>
    {{-- Progress bar --}}
    <div class="mb-8">
        <div class="mb-1.5 flex justify-between text-xs text-zinc-400">
            <span>Step {{ $step }} of 5</span>
            <span>{{ round(($step - 1) / 4 * 100) }}%</span>
        </div>
        <div class="h-1.5 w-full rounded-full bg-zinc-700">
            <div class="h-1.5 rounded-full bg-[#0089CB] transition-all"
                 style="width: {{ round(($step - 1) / 4 * 100) }}%"></div>
        </div>
    </div>

    @if ($step === 1)
        <h2 class="mb-1 text-xl font-bold">Which office can help you?</h2>
        <p class="mb-6 text-sm text-zinc-400">Select the office that handles your request.</p>

        <div class="space-y-3">
            @foreach ($this->offices as $office)
                <button type="button" wire:click="$set('officeId', {{ $office->id }})"
                        @class([
                            'w-full rounded-xl border px-5 py-4 text-left transition-colors',
                            'border-[#0089CB] bg-blue-900/20' => $officeId === $office->id,
                            'border-zinc-700/60 bg-zinc-800/50 hover:border-zinc-600' => $officeId !== $office->id,
                        ])>
                    <p class="font-semibold">{{ $office->name }}</p>
                    @if ($office->description)
                        <p class="mt-0.5 text-sm text-zinc-400">{{ $office->description }}</p>
                    @endif
                </button>
            @endforeach
        </div>
        @error('officeId') <p class="mt-2 text-sm text-red-400">{{ $message }}</p> @enderror

        <div class="mt-8 flex justify-end">
            <button type="button" wire:click="nextStep"
                    class="rounded-lg bg-[#0089CB] px-6 py-2.5 text-sm font-semibold text-white hover:bg-[#0077b3]">
                Next →
            </button>
        </div>
    @endif

    @if ($step === 2)
        <h2 class="mb-1 text-xl font-bold">What type of request?</h2>
        <p class="mb-6 text-sm text-zinc-400">Select a category.</p>

        <div class="space-y-3">
            @foreach ($this->categories as $category)
                <button type="button" wire:click="$set('serviceCategoryId', {{ $category->id }})"
                        @class([
                            'w-full rounded-xl border px-5 py-4 text-left transition-colors',
                            'border-[#0089CB] bg-blue-900/20' => $serviceCategoryId === $category->id,
                            'border-zinc-700/60 bg-zinc-800/50 hover:border-zinc-600' => $serviceCategoryId !== $category->id,
                        ])>
                    <p class="font-semibold">{{ $category->name }}</p>
                    @if ($category->description)
                        <p class="mt-0.5 text-sm text-zinc-400">{{ $category->description }}</p>
                    @endif
                </button>
            @endforeach
        </div>
        @error('serviceCategoryId') <p class="mt-2 text-sm text-red-400">{{ $message }}</p> @enderror

        <div class="mt-8 flex justify-between">
            <button type="button" wire:click="prevStep"
                    class="rounded-lg border border-zinc-600 px-6 py-2.5 text-sm font-semibold text-zinc-300 hover:border-zinc-500">
                ← Back
            </button>
            <button type="button" wire:click="nextStep"
                    class="rounded-lg bg-[#0089CB] px-6 py-2.5 text-sm font-semibold text-white hover:bg-[#0077b3]">
                Next →
            </button>
        </div>
    @endif

    @if ($step === 3)
        <h2 class="mb-1 text-xl font-bold">Select a specific service</h2>
        <p class="mb-6 text-sm text-zinc-400">Choose the exact service you need.</p>

        <div class="space-y-3">
            @foreach ($this->serviceTypes as $type)
                <button type="button" wire:click="$set('serviceTypeId', {{ $type->id }})"
                        @class([
                            'w-full rounded-xl border px-5 py-4 text-left transition-colors',
                            'border-[#0089CB] bg-blue-900/20' => $serviceTypeId === $type->id,
                            'border-zinc-700/60 bg-zinc-800/50 hover:border-zinc-600' => $serviceTypeId !== $type->id,
                        ])>
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="font-semibold">{{ $type->name }}</p>
                            @if ($type->description)
                                <p class="mt-0.5 text-sm text-zinc-400">{{ $type->description }}</p>
                            @endif
                        </div>
                        @if ($type->sla_days)
                            <span class="shrink-0 rounded-full bg-zinc-700 px-2.5 py-0.5 text-xs text-zinc-300">
                                {{ $type->sla_days }}d SLA
                            </span>
                        @endif
                    </div>
                </button>
            @endforeach
        </div>
        @error('serviceTypeId') <p class="mt-2 text-sm text-red-400">{{ $message }}</p> @enderror

        <div class="mt-8 flex justify-between">
            <button type="button" wire:click="prevStep"
                    class="rounded-lg border border-zinc-600 px-6 py-2.5 text-sm font-semibold text-zinc-300 hover:border-zinc-500">
                ← Back
            </button>
            <button type="button" wire:click="nextStep"
                    class="rounded-lg bg-[#0089CB] px-6 py-2.5 text-sm font-semibold text-white hover:bg-[#0077b3]">
                Next →
            </button>
        </div>
    @endif

    @if ($step === 4)
        <p class="text-zinc-400">Step 4 coming in next task.</p>
    @endif

    @if ($step === 5)
        <p class="text-zinc-400">Step 5 coming in next task.</p>
    @endif
</div>
```

- [ ] **Step 5: Run tests — expect all pass**

```bash
php artisan test --compact tests/Feature/Portal/CreateTicketStepsTest.php
```

Expected: 8 passed.

- [ ] **Step 6: Run Pint**

```bash
vendor/bin/pint --dirty --format agent
```

---

## Task 4: Submission Form — Step 4 (Dynamic Fields)

**Files:**
- Modify: `app/Livewire/Portal/CreateTicket.php`
- Modify: `resources/views/livewire/portal/create-ticket.blade.php`
- Create: `tests/Feature/Portal/CreateTicketFieldsTest.php`

- [ ] **Step 1: Write failing tests**

Create `tests/Feature/Portal/CreateTicketFieldsTest.php`:

```php
<?php

use App\Enums\FieldType;
use App\Livewire\Portal\CreateTicket;
use App\Models\Office;
use App\Models\ServiceCategory;
use App\Models\ServiceType;
use App\Models\ServiceTypeField;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;

beforeEach(fn () => $this->seed(RoleSeeder::class));

function studentUser(): User
{
    $u = User::factory()->create();
    $u->assignRole('student');
    return $u;
}

function setupService(): array
{
    $office      = Office::factory()->create();
    $category    = ServiceCategory::factory()->for($office)->create();
    $serviceType = ServiceType::factory()->for($category)->create();
    return compact('office', 'category', 'serviceType');
}

test('required text field blocks advance when empty', function () {
    ['office' => $office, 'category' => $category, 'serviceType' => $serviceType] = setupService();
    $field = ServiceTypeField::factory()->for($serviceType)->create([
        'field_type'  => FieldType::Text,
        'is_required' => true,
    ]);

    Livewire::actingAs(studentUser())
        ->test(CreateTicket::class)
        ->set('step', 4)
        ->set('officeId', $office->id)
        ->set('serviceCategoryId', $category->id)
        ->set('serviceTypeId', $serviceType->id)
        ->call('nextStep')
        ->assertSet('step', 4)
        ->assertHasErrors(["customFields.{$field->id}"]);
});

test('optional field allows advance when empty', function () {
    ['office' => $office, 'category' => $category, 'serviceType' => $serviceType] = setupService();
    ServiceTypeField::factory()->for($serviceType)->create([
        'field_type'  => FieldType::Text,
        'is_required' => false,
    ]);

    Livewire::actingAs(studentUser())
        ->test(CreateTicket::class)
        ->set('step', 4)
        ->set('officeId', $office->id)
        ->set('serviceCategoryId', $category->id)
        ->set('serviceTypeId', $serviceType->id)
        ->call('nextStep')
        ->assertSet('step', 5)
        ->assertHasNoErrors();
});

test('filled required field allows advance', function () {
    ['office' => $office, 'category' => $category, 'serviceType' => $serviceType] = setupService();
    $field = ServiceTypeField::factory()->for($serviceType)->create([
        'field_type'  => FieldType::Text,
        'is_required' => true,
    ]);

    Livewire::actingAs(studentUser())
        ->test(CreateTicket::class)
        ->set('step', 4)
        ->set('officeId', $office->id)
        ->set('serviceCategoryId', $category->id)
        ->set('serviceTypeId', $serviceType->id)
        ->set("customFields.{$field->id}", 'BU-2024-001')
        ->call('nextStep')
        ->assertSet('step', 5)
        ->assertHasNoErrors();
});

test('service type fields are shown in step 4', function () {
    ['office' => $office, 'category' => $category, 'serviceType' => $serviceType] = setupService();
    ServiceTypeField::factory()->for($serviceType)->create([
        'label'       => 'Student ID Number',
        'field_type'  => FieldType::Text,
        'is_required' => true,
    ]);

    Livewire::actingAs(studentUser())
        ->test(CreateTicket::class)
        ->set('step', 4)
        ->set('officeId', $office->id)
        ->set('serviceCategoryId', $category->id)
        ->set('serviceTypeId', $serviceType->id)
        ->assertSee('Student ID Number');
});
```

- [ ] **Step 2: Run tests — expect failures**

```bash
php artisan test --compact tests/Feature/Portal/CreateTicketFieldsTest.php
```

- [ ] **Step 3: Add fields computed property and step-4 validation to CreateTicket**

In `app/Livewire/Portal/CreateTicket.php`, add these imports at the top of the file (after the existing use statements):

```php
use App\Enums\FieldType;
use App\Models\ServiceTypeField;
use Livewire\WithFileUploads;
```

Add `use WithFileUploads;` inside the class body (after `class CreateTicket extends Component`).

Add this computed property after `serviceTypes()`:

```php
#[Computed]
public function fields(): Collection
{
    if (! $this->serviceTypeId) {
        return collect();
    }

    return ServiceTypeField::where('service_type_id', $this->serviceTypeId)
        ->orderBy('sort_order')
        ->get();
}
```

Replace the `nextStep()` method with this version that handles step 4:

```php
public function nextStep(): void
{
    $this->validate(match ($this->step) {
        1 => ['officeId' => 'required|exists:offices,id'],
        2 => ['serviceCategoryId' => 'required|exists:service_categories,id'],
        3 => ['serviceTypeId' => 'required|exists:service_types,id'],
        4 => $this->buildFieldRules(),
        default => [],
    });

    $this->step++;
}

private function buildFieldRules(): array
{
    $rules = [];

    foreach ($this->fields as $field) {
        if ($field->field_type === FieldType::File) {
            $fileRule = 'nullable|file|max:10240|mimes:pdf,jpg,jpeg,png';
            $rules["fileUploads.{$field->id}"] = $field->is_required
                ? 'required|file|max:10240|mimes:pdf,jpg,jpeg,png'
                : $fileRule;
        } elseif ($field->is_required) {
            $rules["customFields.{$field->id}"] = 'required';
        }
    }

    return $rules;
}
```

- [ ] **Step 4: Replace step 4 placeholder in the view**

In `resources/views/livewire/portal/create-ticket.blade.php`, replace the step 4 block:

```html
    @if ($step === 4)
        <p class="text-zinc-400">Step 4 coming in next task.</p>
    @endif
```

With:

```html
    @if ($step === 4)
        <h2 class="mb-1 text-xl font-bold">Fill in the details</h2>
        <p class="mb-6 text-sm text-zinc-400">Answer the questions below to complete your request.</p>

        <div class="space-y-5">
            @foreach ($this->fields as $field)
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-zinc-300">
                        {{ $field->label }}
                        @if ($field->is_required)
                            <span class="text-red-400">*</span>
                        @endif
                    </label>

                    @if ($field->field_type === \App\Enums\FieldType::Text)
                        <input type="text" wire:model="customFields.{{ $field->id }}"
                               class="w-full rounded-lg border border-zinc-600 bg-zinc-800 px-4 py-2.5 text-sm text-zinc-100 focus:border-[#0089CB] focus:outline-none">

                    @elseif ($field->field_type === \App\Enums\FieldType::Textarea)
                        <textarea wire:model="customFields.{{ $field->id }}" rows="4"
                                  class="w-full rounded-lg border border-zinc-600 bg-zinc-800 px-4 py-2.5 text-sm text-zinc-100 focus:border-[#0089CB] focus:outline-none"></textarea>

                    @elseif ($field->field_type === \App\Enums\FieldType::Select)
                        <select wire:model="customFields.{{ $field->id }}"
                                class="w-full rounded-lg border border-zinc-600 bg-zinc-800 px-4 py-2.5 text-sm text-zinc-100 focus:border-[#0089CB] focus:outline-none">
                            <option value="">-- Select --</option>
                            @foreach ($field->options ?? [] as $option)
                                <option value="{{ $option }}">{{ $option }}</option>
                            @endforeach
                        </select>

                    @elseif ($field->field_type === \App\Enums\FieldType::Checkbox)
                        <input type="checkbox" wire:model="customFields.{{ $field->id }}"
                               class="h-4 w-4 rounded border-zinc-600 text-[#0089CB]">

                    @elseif ($field->field_type === \App\Enums\FieldType::Date)
                        <input type="date" wire:model="customFields.{{ $field->id }}"
                               class="w-full rounded-lg border border-zinc-600 bg-zinc-800 px-4 py-2.5 text-sm text-zinc-100 focus:border-[#0089CB] focus:outline-none">

                    @elseif ($field->field_type === \App\Enums\FieldType::File)
                        <input type="file" wire:model="fileUploads.{{ $field->id }}"
                               accept=".pdf,.jpg,.jpeg,.png"
                               class="block w-full text-sm text-zinc-400 file:mr-4 file:rounded-lg file:border-0 file:bg-zinc-700 file:px-4 file:py-2 file:text-sm file:text-zinc-200 hover:file:bg-zinc-600">
                    @endif

                    @error("customFields.{$field->id}")
                        <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                    @error("fileUploads.{$field->id}")
                        <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            @endforeach
        </div>

        <div class="mt-8 flex justify-between">
            <button type="button" wire:click="prevStep"
                    class="rounded-lg border border-zinc-600 px-6 py-2.5 text-sm font-semibold text-zinc-300 hover:border-zinc-500">
                ← Back
            </button>
            <button type="button" wire:click="nextStep"
                    class="rounded-lg bg-[#0089CB] px-6 py-2.5 text-sm font-semibold text-white hover:bg-[#0077b3]">
                Next →
            </button>
        </div>
    @endif
```

- [ ] **Step 5: Run tests — expect all pass**

```bash
php artisan test --compact tests/Feature/Portal/CreateTicketFieldsTest.php
```

Expected: 4 passed.

- [ ] **Step 6: Run Pint**

```bash
vendor/bin/pint --dirty --format agent
```

---

## Task 5: Submission Form — Step 5 (Review + Submit)

**Files:**
- Modify: `app/Livewire/Portal/CreateTicket.php`
- Modify: `resources/views/livewire/portal/create-ticket.blade.php`
- Create: `tests/Feature/Portal/CreateTicketSubmitTest.php`

- [ ] **Step 1: Write failing tests**

Create `tests/Feature/Portal/CreateTicketSubmitTest.php`:

```php
<?php

use App\Enums\EventType;
use App\Enums\FieldType;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Livewire\Portal\CreateTicket;
use App\Models\Office;
use App\Models\ServiceCategory;
use App\Models\ServiceType;
use App\Models\ServiceTypeField;
use App\Models\Ticket;
use App\Models\TicketHistory;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;

beforeEach(fn () => $this->seed(RoleSeeder::class));

function submittingStudent(): User
{
    $u = User::factory()->create();
    $u->assignRole('student');
    return $u;
}

function serviceSetup(): array
{
    $office      = Office::factory()->create();
    $category    = ServiceCategory::factory()->for($office)->create();
    $serviceType = ServiceType::factory()->for($category)->create(['name' => 'Grade Report']);
    return compact('office', 'category', 'serviceType');
}

test('submit creates ticket with correct data', function () {
    $student = submittingStudent();
    ['office' => $office, 'category' => $category, 'serviceType' => $serviceType] = serviceSetup();

    Livewire::actingAs($student)
        ->test(CreateTicket::class)
        ->set('step', 5)
        ->set('officeId', $office->id)
        ->set('serviceCategoryId', $category->id)
        ->set('serviceTypeId', $serviceType->id)
        ->call('submit');

    $ticket = Ticket::first();
    expect($ticket)->not->toBeNull()
        ->and($ticket->requester_id)->toBe($student->id)
        ->and($ticket->office_id)->toBe($office->id)
        ->and($ticket->service_type_id)->toBe($serviceType->id)
        ->and($ticket->status)->toBe(TicketStatus::Pending)
        ->and($ticket->priority)->toBe(TicketPriority::Normal)
        ->and($ticket->subject)->toBe('Grade Report')
        ->and($ticket->ulid)->not->toBeEmpty();
});

test('submit creates initial ticket history row', function () {
    $student = submittingStudent();
    ['office' => $office, 'category' => $category, 'serviceType' => $serviceType] = serviceSetup();

    Livewire::actingAs($student)
        ->test(CreateTicket::class)
        ->set('step', 5)
        ->set('officeId', $office->id)
        ->set('serviceCategoryId', $category->id)
        ->set('serviceTypeId', $serviceType->id)
        ->call('submit');

    $ticket  = Ticket::first();
    $history = TicketHistory::where('ticket_id', $ticket->id)->first();

    expect($history)->not->toBeNull()
        ->and($history->event_type)->toBe(EventType::Created)
        ->and($history->actor_id)->toBe($student->id);
});

test('submit stores custom field answers in ticket', function () {
    $student = submittingStudent();
    ['office' => $office, 'category' => $category, 'serviceType' => $serviceType] = serviceSetup();
    $field = ServiceTypeField::factory()->for($serviceType)->create([
        'field_type'  => FieldType::Text,
        'is_required' => false,
    ]);

    Livewire::actingAs($student)
        ->test(CreateTicket::class)
        ->set('step', 5)
        ->set('officeId', $office->id)
        ->set('serviceCategoryId', $category->id)
        ->set('serviceTypeId', $serviceType->id)
        ->set("customFields.{$field->id}", 'For scholarship application')
        ->call('submit');

    $ticket = Ticket::first();
    expect($ticket->custom_fields[(string) $field->id])->toBe('For scholarship application');
});

test('submit redirects to ticket detail page', function () {
    $student = submittingStudent();
    ['office' => $office, 'category' => $category, 'serviceType' => $serviceType] = serviceSetup();

    Livewire::actingAs($student)
        ->test(CreateTicket::class)
        ->set('step', 5)
        ->set('officeId', $office->id)
        ->set('serviceCategoryId', $category->id)
        ->set('serviceTypeId', $serviceType->id)
        ->call('submit')
        ->assertRedirect();
});
```

- [ ] **Step 2: Run tests — expect failures**

```bash
php artisan test --compact tests/Feature/Portal/CreateTicketSubmitTest.php
```

- [ ] **Step 3: Add submit method and computed helpers to CreateTicket**

In `app/Livewire/Portal/CreateTicket.php`, add these imports:

```php
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Enums\EventType;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\TicketHistory;
```

Add these computed properties after `fields()`:

```php
#[Computed]
public function selectedOffice(): ?Office
{
    return $this->officeId ? Office::find($this->officeId) : null;
}

#[Computed]
public function selectedCategory(): ?ServiceCategory
{
    return $this->serviceCategoryId ? ServiceCategory::find($this->serviceCategoryId) : null;
}

#[Computed]
public function selectedServiceType(): ?ServiceType
{
    return $this->serviceTypeId ? ServiceType::find($this->serviceTypeId) : null;
}
```

Add the `submit()` method after `prevStep()`:

```php
public function submit(): void
{
    $serviceType = $this->selectedServiceType;

    $ticket = Ticket::create([
        'requester_id'    => auth()->id(),
        'office_id'       => $this->officeId,
        'service_type_id' => $this->serviceTypeId,
        'status'          => TicketStatus::Pending,
        'priority'        => TicketPriority::Normal,
        'subject'         => $serviceType->name,
        'custom_fields'   => $this->customFields,
    ]);

    TicketHistory::create([
        'ticket_id'  => $ticket->id,
        'actor_id'   => auth()->id(),
        'event_type' => EventType::Created,
    ]);

    foreach ($this->fields as $field) {
        if ($field->field_type === FieldType::File && ! empty($this->fileUploads[$field->id])) {
            $file = $this->fileUploads[$field->id];
            $path = $file->store("attachments/{$ticket->ulid}", 'public');

            TicketAttachment::create([
                'ticket_id'         => $ticket->id,
                'uploader_id'       => auth()->id(),
                'disk'              => 'public',
                'path'              => $path,
                'original_filename' => $file->getClientOriginalName(),
                'mime_type'         => $file->getMimeType(),
                'size_bytes'        => $file->getSize(),
            ]);
        }
    }

    $this->redirect(route('portal.tickets.show', $ticket->ulid), navigate: true);
}
```

- [ ] **Step 4: Replace step 5 placeholder in the view**

In `resources/views/livewire/portal/create-ticket.blade.php`, replace:

```html
    @if ($step === 5)
        <p class="text-zinc-400">Step 5 coming in next task.</p>
    @endif
```

With:

```html
    @if ($step === 5)
        <h2 class="mb-1 text-xl font-bold">Review your request</h2>
        <p class="mb-6 text-sm text-zinc-400">Check everything looks correct before submitting.</p>

        <div class="space-y-3 rounded-xl border border-zinc-700/60 bg-zinc-800/50 p-5 text-sm">
            <div class="flex gap-3">
                <span class="w-28 shrink-0 text-zinc-400">Office</span>
                <span class="font-medium">{{ $this->selectedOffice?->name }}</span>
            </div>
            <div class="flex gap-3">
                <span class="w-28 shrink-0 text-zinc-400">Category</span>
                <span class="font-medium">{{ $this->selectedCategory?->name }}</span>
            </div>
            <div class="flex gap-3">
                <span class="w-28 shrink-0 text-zinc-400">Service</span>
                <span class="font-medium">{{ $this->selectedServiceType?->name }}</span>
            </div>
            @foreach ($this->fields as $field)
                @if (! empty($customFields[$field->id]))
                    <div class="flex gap-3">
                        <span class="w-28 shrink-0 text-zinc-400">{{ $field->label }}</span>
                        <span class="font-medium">
                            {{ is_array($customFields[$field->id])
                                ? implode(', ', $customFields[$field->id])
                                : $customFields[$field->id] }}
                        </span>
                    </div>
                @endif
            @endforeach
        </div>

        <div class="mt-8 flex justify-between">
            <button type="button" wire:click="prevStep"
                    class="rounded-lg border border-zinc-600 px-6 py-2.5 text-sm font-semibold text-zinc-300 hover:border-zinc-500">
                ← Back
            </button>
            <button type="button" wire:click="submit"
                    wire:loading.attr="disabled"
                    class="rounded-lg bg-[#0089CB] px-8 py-2.5 text-sm font-semibold text-white hover:bg-[#0077b3] disabled:opacity-60">
                <span wire:loading.remove wire:target="submit">Submit Request</span>
                <span wire:loading wire:target="submit">Submitting…</span>
            </button>
        </div>
    @endif
```

- [ ] **Step 5: Run tests — expect all pass**

```bash
php artisan test --compact tests/Feature/Portal/CreateTicketSubmitTest.php
```

Expected: 4 passed.

- [ ] **Step 6: Run Pint**

```bash
vendor/bin/pint --dirty --format agent
```

---

## Task 6: Ticket Detail — Status Timeline

**Files:**
- Modify: `app/Livewire/Portal/TicketDetail.php`
- Modify: `resources/views/livewire/portal/ticket-detail.blade.php`
- Create: `tests/Feature/Portal/TicketDetailTest.php`

- [ ] **Step 1: Write failing tests**

Create `tests/Feature/Portal/TicketDetailTest.php`:

```php
<?php

use App\Enums\EventType;
use App\Livewire\Portal\TicketDetail;
use App\Models\Office;
use App\Models\ServiceCategory;
use App\Models\ServiceType;
use App\Models\Ticket;
use App\Models\TicketHistory;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;

beforeEach(fn () => $this->seed(RoleSeeder::class));

function detailStudent(): User
{
    $u = User::factory()->create();
    $u->assignRole('student');
    return $u;
}

function detailTicket(User $student): Ticket
{
    $office      = Office::factory()->create();
    $category    = ServiceCategory::factory()->for($office)->create();
    $serviceType = ServiceType::factory()->for($category)->create();
    return Ticket::factory()->for($student, 'requester')->for($office)->for($serviceType)->create();
}

test('student can view their ticket detail page', function () {
    $student = detailStudent();
    $ticket  = detailTicket($student);

    $this->actingAs($student)
        ->get(route('portal.tickets.show', $ticket->ulid))
        ->assertOk()
        ->assertSee($ticket->subject);
});

test('student cannot view another students ticket', function () {
    $student = detailStudent();
    $other   = detailStudent();
    $ticket  = detailTicket($other);

    $this->actingAs($student)
        ->get(route('portal.tickets.show', $ticket->ulid))
        ->assertNotFound();
});

test('timeline shows history events in chronological order', function () {
    $student = detailStudent();
    $ticket  = detailTicket($student);

    TicketHistory::factory()->create([
        'ticket_id'  => $ticket->id,
        'actor_id'   => $student->id,
        'event_type' => EventType::Created,
        'created_at' => now()->subMinutes(10),
    ]);
    TicketHistory::factory()->create([
        'ticket_id'  => $ticket->id,
        'actor_id'   => $student->id,
        'event_type' => EventType::Assigned,
        'created_at' => now()->subMinutes(5),
    ]);

    Livewire::actingAs($student)
        ->test(TicketDetail::class, ['ulid' => $ticket->ulid])
        ->assertSeeInOrder(['Created', 'Assigned']);
});

test('ticket office and service type name are shown', function () {
    $student = detailStudent();
    $ticket  = detailTicket($student);
    $ticket->load(['office', 'serviceType']);

    Livewire::actingAs($student)
        ->test(TicketDetail::class, ['ulid' => $ticket->ulid])
        ->assertSee($ticket->office->name)
        ->assertSee($ticket->serviceType->name);
});
```

- [ ] **Step 2: Run tests — expect failures**

```bash
php artisan test --compact tests/Feature/Portal/TicketDetailTest.php
```

- [ ] **Step 3: Implement TicketDetail component**

Replace `app/Livewire/Portal/TicketDetail.php`:

```php
<?php

namespace App\Livewire\Portal;

use App\Models\Ticket;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('components.layouts.portal')]
class TicketDetail extends Component
{
    #[Locked]
    public string $ulid = '';

    public string $messageBody = '';

    public function mount(string $ulid): void
    {
        $this->ulid = $ulid;

        $ticket = $this->ticket();

        $ticket->messages()
            ->where('sender_id', '!=', auth()->id())
            ->where('is_internal_note', false)
            ->whereNull('seen_at')
            ->update(['seen_at' => now()]);
    }

    public function sendMessage(): void
    {
        $this->validate(['messageBody' => 'required|string|max:5000']);

        $this->ticket()->messages()->create([
            'sender_id'         => auth()->id(),
            'body'              => $this->messageBody,
            'is_internal_note'  => false,
            'is_canned_response'=> false,
        ]);

        $this->messageBody = '';
    }

    public function render(): View
    {
        $ticket   = $this->ticket()->load(['office', 'serviceType', 'history.actor']);
        $messages = $ticket->messages()
            ->where('is_internal_note', false)
            ->with('sender')
            ->get();

        return view('livewire.portal.ticket-detail', compact('ticket', 'messages'));
    }

    private function ticket(): Ticket
    {
        return Ticket::where('ulid', $this->ulid)
            ->where('requester_id', auth()->id())
            ->firstOrFail();
    }
}
```

- [ ] **Step 4: Implement ticket detail view**

Replace `resources/views/livewire/portal/ticket-detail.blade.php`:

```html
<div wire:poll.5s>
    <div class="mb-6">
        <a href="{{ route('portal.tickets.index') }}" wire:navigate
           class="text-sm text-zinc-400 hover:text-zinc-200">← My Requests</a>
        <h1 class="mt-3 text-xl font-bold">{{ $ticket->subject }}</h1>
        <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-zinc-400">
            <span class="font-mono text-xs">{{ $ticket->ulid }}</span>
            <span>·</span>
            <span>{{ $ticket->office->name }}</span>
            <span>·</span>
            <span>{{ $ticket->serviceType->name }}</span>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 md:grid-cols-5">

        {{-- Status Timeline --}}
        <div class="md:col-span-2">
            <div class="rounded-xl border border-zinc-700/60 bg-zinc-800/50 p-5">
                <h3 class="mb-4 text-xs font-semibold uppercase tracking-wide text-zinc-400">Status Timeline</h3>

                @forelse ($ticket->history as $event)
                    <div class="flex gap-3">
                        <div class="flex flex-col items-center">
                            <div @class([
                                'flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-xs font-bold',
                                'bg-[#0089CB] text-white' => ! $loop->last,
                                'bg-[#FE8926] text-white' => $loop->last,
                            ])>
                                @if (! $loop->last) ✓ @else ● @endif
                            </div>
                            @if (! $loop->last)
                                <div class="my-1 w-px flex-1 bg-zinc-600/60" style="min-height:16px"></div>
                            @endif
                        </div>
                        <div class="pb-4">
                            <p class="text-sm font-semibold leading-tight">{{ $event->event_type->name }}</p>
                            @if ($event->note)
                                <p class="mt-0.5 text-xs text-zinc-400">{{ $event->note }}</p>
                            @endif
                            <p class="mt-0.5 text-xs text-zinc-500">
                                {{ $event->actor?->name ?? 'System' }}
                                · {{ $event->created_at->format('M j, g:ia') }}
                            </p>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-zinc-500">No events yet.</p>
                @endforelse
            </div>
        </div>

        {{-- Chat Thread --}}
        <div class="md:col-span-3">
            <div class="flex flex-col gap-3 rounded-xl border border-zinc-700/60 bg-zinc-800/50 p-5">
                <h3 class="text-xs font-semibold uppercase tracking-wide text-zinc-400">Messages</h3>

                <div class="flex flex-col gap-3">
                    @forelse ($messages as $message)
                        <div @class([
                            'max-w-[80%] rounded-xl px-4 py-3 text-sm',
                            'self-end bg-[#0089CB]/20' => $message->sender_id === auth()->id(),
                            'self-start bg-zinc-700/50' => $message->sender_id !== auth()->id(),
                        ])>
                            <p class="mb-1 text-xs text-zinc-400">
                                {{ $message->sender_id === auth()->id() ? 'You' : $message->sender->name }}
                                · {{ $message->created_at->format('M j, g:ia') }}
                                @if ($message->sender_id === auth()->id() && $message->seen_at)
                                    · <span class="text-[#0089CB]">✓✓ Seen</span>
                                @endif
                            </p>
                            <p class="leading-snug">{{ $message->body }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-zinc-500">No messages yet. Send one below.</p>
                    @endforelse
                </div>

                <div class="mt-2 flex gap-2 border-t border-zinc-700/60 pt-4">
                    <textarea wire:model="messageBody"
                              wire:keydown.ctrl.enter="sendMessage"
                              rows="2"
                              placeholder="Type a message… (Ctrl+Enter to send)"
                              class="flex-1 resize-none rounded-lg border border-zinc-600 bg-zinc-900 px-3 py-2 text-sm text-zinc-100 focus:border-[#0089CB] focus:outline-none"></textarea>
                    <button type="button" wire:click="sendMessage"
                            class="self-end rounded-lg bg-[#0089CB] px-4 py-2 text-sm font-semibold text-white hover:bg-[#0077b3]">
                        Send
                    </button>
                </div>
                @error('messageBody')
                    <p class="text-xs text-red-400">{{ $message }}</p>
                @enderror
            </div>
        </div>

    </div>
</div>
```

- [ ] **Step 5: Run tests — expect all pass**

```bash
php artisan test --compact tests/Feature/Portal/TicketDetailTest.php
```

Expected: 4 passed.

- [ ] **Step 6: Run Pint**

```bash
vendor/bin/pint --dirty --format agent
```

---

## Task 7: Ticket Detail — Chat Thread & Seen Tracking

**Files:**
- Create: `tests/Feature/Portal/TicketChatTest.php`
- No code changes needed — implementation is already in Task 6's TicketDetail component.

- [ ] **Step 1: Write chat tests**

Create `tests/Feature/Portal/TicketChatTest.php`:

```php
<?php

use App\Livewire\Portal\TicketDetail;
use App\Models\Office;
use App\Models\ServiceCategory;
use App\Models\ServiceType;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;

beforeEach(fn () => $this->seed(RoleSeeder::class));

function chatStudent(): User
{
    $u = User::factory()->create();
    $u->assignRole('student');
    return $u;
}

function chatTicket(User $student): Ticket
{
    $office      = Office::factory()->create();
    $category    = ServiceCategory::factory()->for($office)->create();
    $serviceType = ServiceType::factory()->for($category)->create();
    return Ticket::factory()->for($student, 'requester')->for($office)->for($serviceType)->create();
}

test('student sees non-internal messages', function () {
    $student = chatStudent();
    $staff   = User::factory()->create();
    $ticket  = chatTicket($student);

    TicketMessage::factory()->create([
        'ticket_id'       => $ticket->id,
        'sender_id'       => $staff->id,
        'body'            => 'Public reply',
        'is_internal_note'=> false,
    ]);

    Livewire::actingAs($student)
        ->test(TicketDetail::class, ['ulid' => $ticket->ulid])
        ->assertSee('Public reply');
});

test('student does not see internal notes', function () {
    $student = chatStudent();
    $staff   = User::factory()->create();
    $ticket  = chatTicket($student);

    TicketMessage::factory()->create([
        'ticket_id'       => $ticket->id,
        'sender_id'       => $staff->id,
        'body'            => 'Staff internal note',
        'is_internal_note'=> true,
    ]);

    Livewire::actingAs($student)
        ->test(TicketDetail::class, ['ulid' => $ticket->ulid])
        ->assertDontSee('Staff internal note');
});

test('student can send a message', function () {
    $student = chatStudent();
    $ticket  = chatTicket($student);

    Livewire::actingAs($student)
        ->test(TicketDetail::class, ['ulid' => $ticket->ulid])
        ->set('messageBody', 'Any update on my request?')
        ->call('sendMessage')
        ->assertHasNoErrors()
        ->assertSet('messageBody', '');

    expect(
        TicketMessage::where('ticket_id', $ticket->id)
            ->where('body', 'Any update on my request?')
            ->where('is_internal_note', false)
            ->exists()
    )->toBeTrue();
});

test('empty message is rejected', function () {
    $student = chatStudent();
    $ticket  = chatTicket($student);

    Livewire::actingAs($student)
        ->test(TicketDetail::class, ['ulid' => $ticket->ulid])
        ->set('messageBody', '')
        ->call('sendMessage')
        ->assertHasErrors(['messageBody']);
});

test('opening ticket detail marks staff messages as seen', function () {
    $student = chatStudent();
    $staff   = User::factory()->create();
    $ticket  = chatTicket($student);

    $message = TicketMessage::factory()->create([
        'ticket_id'       => $ticket->id,
        'sender_id'       => $staff->id,
        'body'            => 'Hello',
        'is_internal_note'=> false,
        'seen_at'         => null,
    ]);

    Livewire::actingAs($student)
        ->test(TicketDetail::class, ['ulid' => $ticket->ulid]);

    expect($message->fresh()->seen_at)->not->toBeNull();
});
```

- [ ] **Step 2: Run tests — expect all pass**

```bash
php artisan test --compact tests/Feature/Portal/TicketChatTest.php
```

Expected: 5 passed.

- [ ] **Step 3: Run full test suite**

```bash
php artisan test --compact
```

Expected: all previous tests still pass plus new portal tests.

- [ ] **Step 4: Run Pint**

```bash
vendor/bin/pint --dirty --format agent
```

---

## Self-Review Checklist

- [x] Spec coverage: all portal routes, 5-step form, dynamic fields, file uploads, timeline, chat, seen tracking — all have tasks
- [x] No placeholders — every step has actual code
- [x] Type consistency — `Ticket`, `TicketHistory`, `TicketMessage`, `TicketAttachment` match existing models; `TicketStatus::Pending`, `TicketPriority::Normal`, `EventType::Created` match existing enums
- [x] `protected $table = 'ticket_history'` on `TicketHistory` is already in the model — no plan task needed
- [x] File upload handling deferred compression — `compressed_size_bytes` nullable, not set here

**Note:** Plan 2 (Admin Panel) covers the Filament resource, ticket actions (assign, status change, forward), credit logic, and staff messaging. It requires installing Filament before it can begin.
