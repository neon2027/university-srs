# Public Ticket Tracker Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build a public `/track` page where anyone can look up a ticket by number + last name, read the conversation thread, reply with text, and upload attachments when the admin requests one.

**Architecture:** Session-based verification — on correct lookup, the ticket ID is stored in the PHP session and the component switches to the detail state. Guest messages store `sender_id = null` and a `guest_name` field. Admin replies gain a `requests_attachment` toggle that surfaces an upload form on the public page.

**Tech Stack:** Livewire 4, PHP 8.4, Laravel 13, Tailwind CSS 4, Laravel RateLimiter, Pest 4.

---

## File Map

| Action | Path | Purpose |
|---|---|---|
| Modify | `database/migrations/` (new file) | Make `sender_id` nullable, add `guest_name` + `requests_attachment` to `ticket_messages` |
| Modify | `database/migrations/` (new file) | Make `uploader_id` nullable on `ticket_attachments` |
| Modify | `app/Models/TicketMessage.php` | Add new columns to `#[Fillable]` |
| Modify | `database/factories/TicketMessageFactory.php` | Add `guestReply()` and `requestingAttachment()` states |
| Modify | `app/Livewire/Admin/TicketMessaging.php` | Add `$requestsAttachment` property, save it on send |
| Modify | `resources/views/livewire/admin/ticket-messaging.blade.php` | Add toggle, handle null-sender display, add "Via public tracker" badge |
| Modify | `routes/web.php` | Add `GET /track` route |
| Modify | `resources/views/components/layouts/public.blade.php` | Add "Track Ticket" to header nav and footer |
| Create | `app/Livewire/Public/TicketTracker.php` | Lookup + detail Livewire component |
| Create | `resources/views/livewire/public/ticket-tracker.blade.php` | Component view (both states) |
| Create | `tests/Feature/Public/TicketTrackerTest.php` | Feature tests |
| Create | `tests/Feature/Admin/TicketMessagingRequestAttachmentTest.php` | Admin toggle tests |

---

## Task 1: Schema Migrations, Model, and Factory Updates

**Files:**
- Create: `database/migrations/2026_05_16_000001_add_guest_tracking_fields_to_ticket_messages_table.php`
- Create: `database/migrations/2026_05_16_000002_make_uploader_id_nullable_on_ticket_attachments_table.php`
- Modify: `app/Models/TicketMessage.php`
- Modify: `database/factories/TicketMessageFactory.php`
- Test: `tests/Feature/TicketSupportTablesTest.php` (add assertions to existing file)

- [ ] **Step 1: Write the failing schema test**

Add these two tests to `tests/Feature/TicketSupportTablesTest.php`:

```php
test('ticket_messages sender_id is nullable', function () {
    $columns = Schema::getColumns('ticket_messages');
    $senderCol = collect($columns)->firstWhere('name', 'sender_id');
    expect($senderCol['nullable'])->toBeTrue();
});

test('ticket_messages has guest_name and requests_attachment columns', function () {
    expect(Schema::hasColumn('ticket_messages', 'guest_name'))->toBeTrue();
    expect(Schema::hasColumn('ticket_messages', 'requests_attachment'))->toBeTrue();
});

test('ticket_attachments uploader_id is nullable', function () {
    $columns = Schema::getColumns('ticket_attachments');
    $uploaderCol = collect($columns)->firstWhere('name', 'uploader_id');
    expect($uploaderCol['nullable'])->toBeTrue();
});
```

Add `use Illuminate\Support\Facades\Schema;` at the top of the test file.

- [ ] **Step 2: Run to confirm failure**

```bash
php artisan test --compact --filter="sender_id is nullable|guest_name and requests_attachment|uploader_id is nullable"
```

Expected: 3 FAIL.

- [ ] **Step 3: Create migration 1 — ticket_messages changes**

```bash
php artisan make:migration add_guest_tracking_fields_to_ticket_messages_table --no-interaction
```

Fill the generated file:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ticket_messages', function (Blueprint $table) {
            $table->dropForeign(['sender_id']);
            $table->unsignedBigInteger('sender_id')->nullable()->change();
            $table->foreign('sender_id')->references('id')->on('users')->nullOnDelete();
            $table->string('guest_name')->nullable()->after('sender_id');
            $table->boolean('requests_attachment')->default(false)->after('is_canned_response');
        });
    }

    public function down(): void
    {
        Schema::table('ticket_messages', function (Blueprint $table) {
            $table->dropForeign(['sender_id']);
            $table->dropColumn(['guest_name', 'requests_attachment']);
            $table->unsignedBigInteger('sender_id')->nullable(false)->change();
            $table->foreign('sender_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
```

- [ ] **Step 4: Create migration 2 — ticket_attachments nullable uploader**

```bash
php artisan make:migration make_uploader_id_nullable_on_ticket_attachments_table --no-interaction
```

Fill the generated file:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ticket_attachments', function (Blueprint $table) {
            $table->dropForeign(['uploader_id']);
            $table->unsignedBigInteger('uploader_id')->nullable()->change();
            $table->foreign('uploader_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ticket_attachments', function (Blueprint $table) {
            $table->dropForeign(['uploader_id']);
            $table->unsignedBigInteger('uploader_id')->nullable(false)->change();
            $table->foreign('uploader_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
```

- [ ] **Step 5: Run migrations**

```bash
php artisan migrate --no-interaction
```

Expected: 2 migrations run successfully.

- [ ] **Step 6: Update `TicketMessage` model fillable**

In `app/Models/TicketMessage.php`, replace the `#[Fillable]` attribute:

```php
#[Fillable(['ticket_id', 'sender_id', 'guest_name', 'body', 'is_internal_note', 'is_canned_response', 'requests_attachment', 'seen_at'])]
```

Also update `casts()` to include the new boolean:

```php
protected function casts(): array
{
    return [
        'is_internal_note' => 'boolean',
        'is_canned_response' => 'boolean',
        'requests_attachment' => 'boolean',
        'seen_at' => 'datetime',
    ];
}
```

- [ ] **Step 7: Update `TicketMessageFactory`**

In `database/factories/TicketMessageFactory.php`, add two states:

```php
public function guestReply(string $guestName = 'Test User'): static
{
    return $this->state(fn (array $attributes) => [
        'sender_id' => null,
        'guest_name' => $guestName,
    ]);
}

public function requestingAttachment(): static
{
    return $this->state(fn (array $attributes) => ['requests_attachment' => true]);
}
```

- [ ] **Step 8: Run schema tests**

```bash
php artisan test --compact --filter="sender_id is nullable|guest_name and requests_attachment|uploader_id is nullable"
```

Expected: 3 PASS.

- [ ] **Step 9: Commit**

```bash
git add database/migrations/ app/Models/TicketMessage.php database/factories/TicketMessageFactory.php tests/Feature/TicketSupportTablesTest.php
git commit -m "feat: add guest tracking schema — nullable sender, guest_name, requests_attachment, nullable uploader"
```

---

## Task 2: Admin Messaging — "Request Attachment" Toggle

**Files:**
- Modify: `app/Livewire/Admin/TicketMessaging.php`
- Modify: `resources/views/livewire/admin/ticket-messaging.blade.php`
- Create: `tests/Feature/Admin/TicketMessagingRequestAttachmentTest.php`

- [ ] **Step 1: Create the test file**

```bash
php artisan make:test --pest TicketMessagingRequestAttachmentTest --no-interaction
```

- [ ] **Step 2: Write the failing tests**

Replace the generated file at `tests/Feature/TicketMessagingRequestAttachmentTest.php` with:

```php
<?php

use App\Livewire\Admin\TicketMessaging;
use App\Models\Office;
use App\Models\ServiceCategory;
use App\Models\ServiceType;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;

beforeEach(fn () => $this->seed(RoleSeeder::class));

function makeAdminTicket(): array
{
    $staff = User::factory()->create();
    $staff->assignRole('staff');

    $student = User::factory()->create();
    $office = Office::factory()->create();
    $staff->offices()->attach($office, ['is_primary' => true]);

    $category = ServiceCategory::factory()->for($office)->create();
    $serviceType = ServiceType::factory()->for($category)->create();
    $ticket = Ticket::factory()
        ->for($student, 'requester')
        ->for($office)
        ->for($serviceType)
        ->create(['assigned_to' => $staff->id]);

    return compact('staff', 'ticket');
}

test('admin can send a reply with requests_attachment flag', function () {
    ['staff' => $staff, 'ticket' => $ticket] = makeAdminTicket();

    Livewire::actingAs($staff)
        ->test(TicketMessaging::class, ['ticket' => $ticket])
        ->set('body', 'Please upload your documents.')
        ->set('requestsAttachment', true)
        ->call('send')
        ->assertHasNoErrors();

    expect(
        TicketMessage::where('ticket_id', $ticket->id)
            ->where('requests_attachment', true)
            ->where('body', 'Please upload your documents.')
            ->exists()
    )->toBeTrue();
});

test('requests_attachment resets to false after send', function () {
    ['staff' => $staff, 'ticket' => $ticket] = makeAdminTicket();

    Livewire::actingAs($staff)
        ->test(TicketMessaging::class, ['ticket' => $ticket])
        ->set('body', 'Upload needed.')
        ->set('requestsAttachment', true)
        ->call('send')
        ->assertSet('requestsAttachment', false);
});

test('guest messages are displayed with guest_name and via-public-tracker badge', function () {
    ['staff' => $staff, 'ticket' => $ticket] = makeAdminTicket();

    TicketMessage::factory()->guestReply('Ana Reyes')->create([
        'ticket_id' => $ticket->id,
        'body' => 'I have uploaded the form.',
        'is_internal_note' => false,
    ]);

    Livewire::actingAs($staff)
        ->test(TicketMessaging::class, ['ticket' => $ticket])
        ->assertSee('Ana Reyes')
        ->assertSee('Via public tracker')
        ->assertSee('I have uploaded the form.');
});
```

- [ ] **Step 3: Run to confirm failure**

```bash
php artisan test --compact --filter="TicketMessagingRequestAttachment"
```

Expected: 3 FAIL.

- [ ] **Step 4: Update `TicketMessaging.php`**

Add `public bool $requestsAttachment = false;` as a property, and in `send()` add `'requests_attachment' => $this->requestsAttachment` to the `TicketMessage::create()` call and reset after:

```php
public Ticket $ticket;

public string $body = '';

public bool $isInternalNote = false;

public bool $requestsAttachment = false;

public ?string $errorMessage = null;

// ... (applyCannedResponse unchanged)

public function send(): void
{
    $user = auth()->user();

    if (! $this->canReply($user)) {
        $this->errorMessage = 'You can only reply to tickets assigned to you or tickets from your office.';

        return;
    }

    if ($this->isInternalNote && ! $user->hasAnyRole(['staff', 'office_admin', 'super_admin'])) {
        $this->isInternalNote = false;
    }

    $this->body = trim($this->body);

    $this->validate([
        'body' => 'required|string|max:5000',
    ]);

    TicketMessage::create([
        'ticket_id' => $this->ticket->id,
        'sender_id' => auth()->id(),
        'body' => $this->body,
        'is_internal_note' => $this->isInternalNote,
        'is_canned_response' => false,
        'requests_attachment' => $this->requestsAttachment,
    ]);

    $this->body = '';
    $this->isInternalNote = false;
    $this->requestsAttachment = false;
    $this->errorMessage = null;
}
```

- [ ] **Step 5: Update `ticket-messaging.blade.php` — add toggle + guest badge**

**Change 1:** In `loadMessages()`, the `with('sender')` already eager-loads the sender relation. No change needed there — `sender` will simply be `null` for guest messages.

In the view, find the `@php` block that computes `$initials` and replace it to handle null senders:

```php
@php
    $senderName = $message->sender?->name ?? $message->guest_name ?? 'Unknown';
    $initials = \Illuminate\Support\Str::of($senderName)
        ->explode(' ')
        ->take(2)
        ->map(fn ($word) => \Illuminate\Support\Str::substr($word, 0, 1))
        ->implode('');
@endphp
```

**Change 2:** In the message header where `$message->sender->name` is rendered, use `$senderName`:

```html
<p class="bam-name">{{ $senderName }}</p>
<p class="bam-meta">
    {{ $message->is_internal_note ? 'added an internal note' : 'added a public reply' }}
    @if ($message->sender === null)
        <span class="bam-chip" style="font-size:10px;padding:2px 7px;">Via public tracker</span>
    @endif
    {{ $message->created_at->diffForHumans() }}
</p>
```

**Change 3:** In the reply form, add the "Request attachment" checkbox next to the Internal note checkbox:

```html
<div class="bam-form-top">
    <h4 class="bam-form-title">{{ $isInternalNote ? 'Add Internal Note' : 'Reply to Requester' }}</h4>

    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <label class="bam-checkbox">
            <input type="checkbox" wire:model.live="isInternalNote">
            <span>Internal note</span>
        </label>
        @unless ($isInternalNote)
            <label class="bam-checkbox">
                <input type="checkbox" wire:model.live="requestsAttachment">
                <span>Request attachment</span>
            </label>
        @endunless
    </div>
</div>
```

- [ ] **Step 6: Run tests**

```bash
php artisan test --compact --filter="TicketMessagingRequestAttachment"
```

Expected: 3 PASS.

- [ ] **Step 7: Commit**

```bash
git add app/Livewire/Admin/TicketMessaging.php resources/views/livewire/admin/ticket-messaging.blade.php tests/Feature/TicketMessagingRequestAttachmentTest.php
git commit -m "feat: add requests_attachment toggle to admin messaging and guest message attribution"
```

---

## Task 3: TicketTracker Component — Lookup State + Route + Navigation

**Files:**
- Create: `app/Livewire/Public/TicketTracker.php`
- Create: `resources/views/livewire/public/ticket-tracker.blade.php`
- Create: `tests/Feature/Public/TicketTrackerTest.php`

- [ ] **Step 1: Create the test file**

```bash
php artisan make:test --pest TicketTrackerTest --no-interaction
```

- [ ] **Step 2: Write failing lookup tests**

Replace `tests/Feature/TicketTrackerTest.php` with:

```php
<?php

use App\Livewire\Public\TicketTracker;
use App\Models\Office;
use App\Models\ServiceCategory;
use App\Models\ServiceType;
use App\Models\Ticket;
use App\Models\User;
use Livewire\Livewire;

function makePublicTicket(string $requesterName = 'Juan Santos'): Ticket
{
    $user = User::factory()->create(['name' => $requesterName]);
    $office = Office::factory()->create();
    $category = ServiceCategory::factory()->for($office)->create();
    $serviceType = ServiceType::factory()->for($category)->create();

    return Ticket::factory()
        ->for($user, 'requester')
        ->for($office)
        ->for($serviceType)
        ->create();
}

test('lookup form is shown by default', function () {
    Livewire::test(TicketTracker::class)
        ->assertSee('Track your Ticket')
        ->assertDontSee('Search another ticket');
});

test('wrong ticket number returns generic error', function () {
    Livewire::test(TicketTracker::class)
        ->set('ticketNumber', 'WRONG-T-99-9999')
        ->set('lastName', 'Santos')
        ->call('lookup')
        ->assertSet('lookupError', 'Ticket not found or details do not match.');
});

test('wrong last name returns generic error', function () {
    $ticket = makePublicTicket('Juan Santos');

    Livewire::test(TicketTracker::class)
        ->set('ticketNumber', $ticket->ulid)
        ->set('lastName', 'WrongName')
        ->call('lookup')
        ->assertSet('lookupError', 'Ticket not found or details do not match.');
});

test('correct credentials store ticket id in session', function () {
    $ticket = makePublicTicket('Juan Santos');

    Livewire::test(TicketTracker::class)
        ->set('ticketNumber', $ticket->ulid)
        ->set('lastName', 'Santos')
        ->call('lookup')
        ->assertSet('lookupError', '');

    expect(session('tracker.ticket_id'))->toBe($ticket->id);
});

test('last name match is case-insensitive', function () {
    $ticket = makePublicTicket('Maria Dela Cruz');

    Livewire::test(TicketTracker::class)
        ->set('ticketNumber', $ticket->ulid)
        ->set('lastName', 'dela cruz')
        ->call('lookup')
        ->assertSet('lookupError', '');
});

test('empty fields are rejected', function () {
    Livewire::test(TicketTracker::class)
        ->set('ticketNumber', '')
        ->set('lastName', '')
        ->call('lookup')
        ->assertHasErrors(['ticketNumber', 'lastName']);
});

test('detail view is shown when session is already set', function () {
    $ticket = makePublicTicket('Ana Reyes');
    session(['tracker.ticket_id' => $ticket->id]);

    Livewire::test(TicketTracker::class)
        ->assertSee($ticket->subject)
        ->assertSee('Search another ticket');
});

test('clear session returns to lookup form', function () {
    $ticket = makePublicTicket('Ana Reyes');
    session(['tracker.ticket_id' => $ticket->id]);

    Livewire::test(TicketTracker::class)
        ->call('clearSession')
        ->assertDontSee('Search another ticket')
        ->assertSee('Track your Ticket');
});
```

- [ ] **Step 3: Run to confirm failure**

```bash
php artisan test --compact --filter="TicketTrackerTest"
```

Expected: all FAIL (class not found).

- [ ] **Step 4: Create `TicketTracker.php`**

```bash
php artisan make:livewire Public/TicketTracker --no-interaction
```

Replace the generated `app/Livewire/Public/TicketTracker.php`:

```php
<?php

namespace App\Livewire\Public;

use App\Models\Ticket;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.public')]
class TicketTracker extends Component
{
    use WithFileUploads;

    public string $ticketNumber = '';

    public string $lastName = '';

    public string $lookupError = '';

    public string $replyBody = '';

    public array $attachmentFiles = [];

    public function lookup(): void
    {
        $this->validate([
            'ticketNumber' => 'required|string',
            'lastName' => 'required|string',
        ]);

        $key = 'ticket-tracker:' . request()->ip();

        $executed = RateLimiter::attempt($key, 10, function () {
            $ticket = Ticket::where('ulid', trim($this->ticketNumber))
                ->with('requester')
                ->first();

            if (! $ticket) {
                $this->lookupError = 'Ticket not found or details do not match.';

                return;
            }

            $requesterLastName = collect(explode(' ', $ticket->requester->name))->last();

            if (strtolower(trim($this->lastName)) !== strtolower(trim($requesterLastName))) {
                $this->lookupError = 'Ticket not found or details do not match.';

                return;
            }

            session(['tracker.ticket_id' => $ticket->id]);
            $this->lookupError = '';
            $this->ticketNumber = '';
            $this->lastName = '';
        });

        if (! $executed) {
            $this->lookupError = 'Too many attempts. Please try again later.';
        }
    }

    public function sendReply(): void
    {
        $ticket = $this->verifiedTicket();

        $this->validate(['replyBody' => 'required|string|max:5000']);

        $ticket->messages()->create([
            'sender_id' => null,
            'guest_name' => $ticket->requester->name,
            'body' => $this->replyBody,
            'is_internal_note' => false,
            'is_canned_response' => false,
        ]);

        $this->replyBody = '';
    }

    public function uploadAttachment(int $messageId): void
    {
        $ticket = $this->verifiedTicket();

        $this->validate([
            "attachmentFiles.{$messageId}" => 'required|file|mimes:pdf,jpg,jpeg,png,gif,webp,doc,docx,xlsx,csv|max:10240',
        ]);

        $file = $this->attachmentFiles[$messageId];
        $path = $file->store('ticket-attachments', 'local');

        $ticket->attachments()->create([
            'ticket_message_id' => $messageId,
            'uploader_id' => null,
            'disk' => 'local',
            'path' => $path,
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size_bytes' => $file->getSize(),
        ]);

        unset($this->attachmentFiles[$messageId]);
    }

    public function clearSession(): void
    {
        session()->forget('tracker.ticket_id');
    }

    public function render(): View
    {
        if (! session()->has('tracker.ticket_id')) {
            return view('livewire.public.ticket-tracker', [
                'isVerified' => false,
                'ticket' => null,
                'messages' => collect(),
            ]);
        }

        $ticket = $this->verifiedTicket()->load(['office', 'serviceType', 'requester']);
        $messages = $ticket->messages()
            ->where('is_internal_note', false)
            ->with('sender')
            ->oldest()
            ->get();

        return view('livewire.public.ticket-tracker', [
            'isVerified' => true,
            'ticket' => $ticket,
            'messages' => $messages,
        ]);
    }

    private function verifiedTicket(): Ticket
    {
        return Ticket::with('requester')
            ->findOrFail(session('tracker.ticket_id'));
    }
}
```

- [ ] **Step 5: Create `ticket-tracker.blade.php`** (lookup form state only)

```php
<div class="mx-auto max-w-2xl px-4 py-16">
    @if (! $isVerified)
        {{-- Lookup Form --}}
        <div class="mb-8 text-center">
            <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-[#0089CB]/10">
                <x-heroicon-o-magnifying-glass class="h-6 w-6 text-[#0089CB]" />
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Track your Ticket</h1>
            <p class="mt-2 text-gray-500">Enter your ticket number and last name to view your request status.</p>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-8 shadow-sm">
            @if ($lookupError)
                <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
                    {{ $lookupError }}
                </div>
            @endif

            <form wire:submit.prevent="lookup" class="space-y-5">
                <div>
                    <label for="ticket-number" class="mb-1.5 block text-sm font-semibold text-gray-700">Ticket Number</label>
                    <input
                        id="ticket-number"
                        type="text"
                        wire:model="ticketNumber"
                        placeholder="e.g. OSS-T-26-0001"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 font-mono text-sm focus:border-[#0089CB] focus:outline-none focus:ring-2 focus:ring-[#0089CB]/20"
                    >
                    @error('ticketNumber')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="last-name" class="mb-1.5 block text-sm font-semibold text-gray-700">Last Name</label>
                    <input
                        id="last-name"
                        type="text"
                        wire:model="lastName"
                        placeholder="Your last name"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-[#0089CB] focus:outline-none focus:ring-2 focus:ring-[#0089CB]/20"
                    >
                    @error('lastName')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    class="w-full rounded-lg bg-[#0089CB] px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-[#0077b3] disabled:opacity-60"
                >
                    <span wire:loading.remove>Find my ticket</span>
                    <span wire:loading>Searching...</span>
                </button>
            </form>
        </div>
    @else
        {{-- Ticket Detail (placeholder for Task 5) --}}
        <p>Ticket found.</p>
        <button wire:click="clearSession" class="text-sm text-gray-500 underline">Search another ticket</button>
    @endif
</div>
```

- [ ] **Step 6: Run lookup tests**

```bash
php artisan test --compact --filter="TicketTrackerTest"
```

Expected: all PASS (or at most the detail-state tests pending Task 4).

- [ ] **Step 7: Add route in `routes/web.php`**

Add the import at the top with the other Public imports:

```php
use App\Livewire\Public\TicketTracker;
```

Add the route after the offices routes:

```php
Route::get('/track', TicketTracker::class)->name('track.ticket');
```

- [ ] **Step 8: Update public layout header — add "Track Ticket" link**

In `resources/views/components/layouts/public.blade.php`, replace the `<nav>` inner contents:

```html
<nav class="mx-auto flex h-14 w-full max-w-5xl items-center justify-between px-4">
    <a href="{{ route('home') }}" class="flex flex-col rounded-md px-2 py-1 leading-none transition-colors hover:bg-black/5">
        <span class="text-[10px] font-extrabold tracking-widest text-[#0089CB]">BICOL UNIVERSITY</span>
        <span class="text-[9px] font-medium tracking-widest text-gray-500">SERVICE REQUEST SYSTEM</span>
    </a>
    <div class="flex items-center gap-3">
        <a href="{{ route('track.ticket') }}"
           class="inline-flex items-center gap-1.5 rounded-md px-3 py-1.5 text-sm font-medium text-gray-600 transition-colors hover:bg-black/5 hover:text-gray-900">
            Track Ticket
        </a>
        <a href="{{ route('auth.google') }}"
           class="inline-flex items-center gap-2 rounded-md bg-[#0089CB] px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-[#0077b3]">
            Sign In
        </a>
    </div>
</nav>
```

- [ ] **Step 9: Update public layout footer — add link under Support**

In `resources/views/components/layouts/public.blade.php`, inside the Support `<ul>`, add after the FAQs `<li>`:

```html
<li><a href="{{ route('track.ticket') }}" class="transition-colors hover:text-gray-900">Track your ticket</a></li>
```

- [ ] **Step 10: Verify the route is accessible**

```bash
php artisan route:list --name=track.ticket
```

Expected: one row showing `GET /track → App\Livewire\Public\TicketTracker`.

- [ ] **Step 11: Commit**

```bash
git add app/Livewire/Public/TicketTracker.php resources/views/livewire/public/ticket-tracker.blade.php tests/Feature/TicketTrackerTest.php routes/web.php resources/views/components/layouts/public.blade.php
git commit -m "feat: add TicketTracker component, /track route, and public navigation links"
```

---

## Task 5: TicketTracker — Detail State (Messages + Text Reply)

**Files:**
- Modify: `resources/views/livewire/public/ticket-tracker.blade.php`
- Modify: `tests/Feature/Public/TicketTrackerTest.php`

- [ ] **Step 1: Write the failing detail-state tests**

Append these tests to `tests/Feature/TicketTrackerTest.php`:

```php
test('internal notes are not shown in detail state', function () {
    $ticket = makePublicTicket('Ana Reyes');
    $staff = User::factory()->create();

    \App\Models\TicketMessage::factory()->create([
        'ticket_id' => $ticket->id,
        'sender_id' => $staff->id,
        'body' => 'Secret internal note',
        'is_internal_note' => true,
    ]);

    session(['tracker.ticket_id' => $ticket->id]);

    Livewire::test(TicketTracker::class)
        ->assertDontSee('Secret internal note');
});

test('public messages are shown in detail state', function () {
    $ticket = makePublicTicket('Ana Reyes');
    $staff = User::factory()->create();

    \App\Models\TicketMessage::factory()->create([
        'ticket_id' => $ticket->id,
        'sender_id' => $staff->id,
        'body' => 'Your request is being processed.',
        'is_internal_note' => false,
    ]);

    session(['tracker.ticket_id' => $ticket->id]);

    Livewire::test(TicketTracker::class)
        ->assertSee('Your request is being processed.');
});

test('guest can send a text reply', function () {
    $ticket = makePublicTicket('Ana Reyes');
    session(['tracker.ticket_id' => $ticket->id]);

    Livewire::test(TicketTracker::class)
        ->set('replyBody', 'I have submitted all required documents.')
        ->call('sendReply')
        ->assertHasNoErrors()
        ->assertSet('replyBody', '');

    expect(
        \App\Models\TicketMessage::where('ticket_id', $ticket->id)
            ->whereNull('sender_id')
            ->where('guest_name', 'Ana Reyes')
            ->where('body', 'I have submitted all required documents.')
            ->exists()
    )->toBeTrue();
});

test('empty reply is rejected', function () {
    $ticket = makePublicTicket('Ana Reyes');
    session(['tracker.ticket_id' => $ticket->id]);

    Livewire::test(TicketTracker::class)
        ->set('replyBody', '')
        ->call('sendReply')
        ->assertHasErrors(['replyBody']);
});
```

- [ ] **Step 2: Run to confirm failure**

```bash
php artisan test --compact --filter="internal notes|public messages|guest can send a text|empty reply"
```

Expected: FAIL.

- [ ] **Step 3: Replace `ticket-tracker.blade.php` with full detail state**

Replace the entire view with:

```php
<div class="mx-auto max-w-3xl px-4 py-12">
    @if (! $isVerified)
        {{-- Lookup Form --}}
        <div class="mb-8 text-center">
            <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-[#0089CB]/10">
                <x-heroicon-o-magnifying-glass class="h-6 w-6 text-[#0089CB]" />
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Track your Ticket</h1>
            <p class="mt-2 text-gray-500">Enter your ticket number and last name to view your request status.</p>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-8 shadow-sm">
            @if ($lookupError)
                <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
                    {{ $lookupError }}
                </div>
            @endif

            <form wire:submit.prevent="lookup" class="space-y-5">
                <div>
                    <label for="ticket-number" class="mb-1.5 block text-sm font-semibold text-gray-700">Ticket Number</label>
                    <input
                        id="ticket-number"
                        type="text"
                        wire:model="ticketNumber"
                        placeholder="e.g. OSS-T-26-0001"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 font-mono text-sm focus:border-[#0089CB] focus:outline-none focus:ring-2 focus:ring-[#0089CB]/20"
                    >
                    @error('ticketNumber')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="last-name" class="mb-1.5 block text-sm font-semibold text-gray-700">Last Name</label>
                    <input
                        id="last-name"
                        type="text"
                        wire:model="lastName"
                        placeholder="Your last name"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-[#0089CB] focus:outline-none focus:ring-2 focus:ring-[#0089CB]/20"
                    >
                    @error('lastName')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    class="w-full rounded-lg bg-[#0089CB] px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-[#0077b3] disabled:opacity-60"
                >
                    <span wire:loading.remove>Find my ticket</span>
                    <span wire:loading>Searching...</span>
                </button>
            </form>
        </div>

    @else
        {{-- Ticket Detail --}}

        {{-- Header --}}
        <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="mb-1 font-mono text-xs font-bold text-[#0089CB]">{{ $ticket->ulid }}</p>
                <h1 class="text-2xl font-bold text-gray-900">{{ $ticket->subject }}</h1>
                <p class="mt-1 text-sm text-gray-500">{{ $ticket->office->name }} · {{ $ticket->serviceType->name }}</p>
            </div>
            <div class="flex items-center gap-3">
                @php
                    $statusColors = [
                        'warning' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
                        'info'    => 'bg-blue-50 text-blue-700 border-blue-200',
                        'primary' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                        'success' => 'bg-green-50 text-green-700 border-green-200',
                        'gray'    => 'bg-gray-100 text-gray-600 border-gray-200',
                        'danger'  => 'bg-red-50 text-red-700 border-red-200',
                    ];
                    $colorClass = $statusColors[$ticket->status->color()] ?? 'bg-gray-100 text-gray-600 border-gray-200';
                @endphp
                <span class="rounded-full border px-3 py-1 text-xs font-bold {{ $colorClass }}">
                    {{ $ticket->status->label() }}
                </span>
                <button wire:click="clearSession" class="text-sm text-gray-400 underline transition-colors hover:text-gray-600">
                    Search another ticket
                </button>
            </div>
        </div>

        {{-- Message Thread --}}
        <div class="space-y-4">
            @forelse ($messages as $message)
                @php
                    $isGuest = $message->sender_id === null;
                    $senderName = $isGuest ? ($message->guest_name ?? 'Requester') : $message->sender->name;
                    $initials = \Illuminate\Support\Str::of($senderName)->explode(' ')->take(2)->map(fn ($w) => \Illuminate\Support\Str::substr($w, 0, 1))->implode('');
                    $isAdmin = ! $isGuest;
                @endphp

                <div class="rounded-xl border {{ $isAdmin ? 'border-blue-100 bg-blue-50' : 'border-gray-200 bg-white' }} p-5" wire:key="pub-msg-{{ $message->id }}">
                    <div class="mb-3 flex items-center gap-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg {{ $isAdmin ? 'bg-[#0089CB]/20 text-[#0089CB]' : 'bg-violet-100 text-violet-700' }} text-xs font-bold">
                            {{ $initials }}
                        </div>
                        <div>
                            <p class="text-sm font-bold text-gray-900">{{ $senderName }}</p>
                            <p class="text-xs text-gray-400">{{ $message->created_at->diffForHumans() }}</p>
                        </div>
                        @if ($isAdmin)
                            <span class="ml-auto rounded-full bg-[#0089CB]/10 px-2.5 py-0.5 text-xs font-semibold text-[#0089CB]">Staff</span>
                        @endif
                    </div>

                    <p class="whitespace-pre-wrap text-sm text-gray-700">{{ $message->body }}</p>

                    {{-- Attachment upload (shown only when admin requested it) --}}
                    @if ($message->requests_attachment)
                        <div class="mt-4 rounded-lg border border-dashed border-[#0089CB]/40 bg-[#0089CB]/5 p-4">
                            <p class="mb-3 text-xs font-semibold text-[#0089CB]">
                                <x-heroicon-o-paper-clip class="mr-1 inline h-3.5 w-3.5" />
                                Attachment requested
                            </p>
                            <form wire:submit.prevent="uploadAttachment({{ $message->id }})" class="flex items-center gap-3">
                                <input
                                    type="file"
                                    wire:model="attachmentFiles.{{ $message->id }}"
                                    class="text-xs text-gray-600"
                                    accept=".pdf,.jpg,.jpeg,.png,.gif,.webp,.doc,.docx,.xlsx,.csv"
                                >
                                <button
                                    type="submit"
                                    wire:loading.attr="disabled"
                                    class="shrink-0 rounded-lg bg-[#0089CB] px-3 py-1.5 text-xs font-semibold text-white transition-colors hover:bg-[#0077b3] disabled:opacity-60"
                                >
                                    <span wire:loading.remove wire:target="uploadAttachment({{ $message->id }})">Upload</span>
                                    <span wire:loading wire:target="uploadAttachment({{ $message->id }})">Uploading...</span>
                                </button>
                            </form>
                            @error("attachmentFiles.{$message->id}")
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif
                </div>
            @empty
                <div class="rounded-xl border border-dashed border-gray-200 py-12 text-center text-sm text-gray-400">
                    No messages yet. Your ticket is being reviewed.
                </div>
            @endforelse
        </div>

        {{-- Reply Form --}}
        <div class="mt-6 rounded-xl border border-gray-200 bg-white p-6">
            <h3 class="mb-4 text-sm font-bold text-gray-900">Reply to this ticket</h3>
            <form wire:submit.prevent="sendReply" class="space-y-4">
                <textarea
                    wire:model="replyBody"
                    rows="4"
                    placeholder="Type your message..."
                    class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-[#0089CB] focus:outline-none focus:ring-2 focus:ring-[#0089CB]/20"
                ></textarea>
                @error('replyBody')
                    <p class="text-xs text-red-600">{{ $message }}</p>
                @enderror
                <div class="flex justify-end">
                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        class="rounded-lg bg-[#0089CB] px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-[#0077b3] disabled:opacity-60"
                    >
                        <span wire:loading.remove wire:target="sendReply">Send Reply</span>
                        <span wire:loading wire:target="sendReply">Sending...</span>
                    </button>
                </div>
            </form>
        </div>
    @endif
</div>
```

- [ ] **Step 4: Run all tracker tests**

```bash
php artisan test --compact --filter="TicketTrackerTest"
```

Expected: all PASS.

- [ ] **Step 5: Commit**

```bash
git add resources/views/livewire/public/ticket-tracker.blade.php tests/Feature/TicketTrackerTest.php
git commit -m "feat: implement ticket tracker detail state with message thread and text reply"
```

---

## Task 6: TicketTracker — File Upload + Final Cleanup

**Files:**
- Modify: `tests/Feature/Public/TicketTrackerTest.php`

- [ ] **Step 1: Write failing upload tests**

Append to `tests/Feature/TicketTrackerTest.php`:

```php
test('upload button is not shown for messages without requests_attachment', function () {
    $ticket = makePublicTicket('Ana Reyes');
    $staff = User::factory()->create();

    \App\Models\TicketMessage::factory()->create([
        'ticket_id' => $ticket->id,
        'sender_id' => $staff->id,
        'body' => 'Regular reply, no attachment needed.',
        'is_internal_note' => false,
        'requests_attachment' => false,
    ]);

    session(['tracker.ticket_id' => $ticket->id]);

    Livewire::test(TicketTracker::class)
        ->assertDontSee('Attachment requested');
});

test('upload button is shown for messages with requests_attachment', function () {
    $ticket = makePublicTicket('Ana Reyes');
    $staff = User::factory()->create();

    \App\Models\TicketMessage::factory()->requestingAttachment()->create([
        'ticket_id' => $ticket->id,
        'sender_id' => $staff->id,
        'body' => 'Please upload your clearance.',
        'is_internal_note' => false,
    ]);

    session(['tracker.ticket_id' => $ticket->id]);

    Livewire::test(TicketTracker::class)
        ->assertSee('Attachment requested');
});

test('guest can upload an attachment for a requesting message', function () {
    Storage::fake('local');

    $ticket = makePublicTicket('Ana Reyes');
    $staff = User::factory()->create();

    $requestingMessage = \App\Models\TicketMessage::factory()->requestingAttachment()->create([
        'ticket_id' => $ticket->id,
        'sender_id' => $staff->id,
        'body' => 'Please provide your certificate.',
        'is_internal_note' => false,
    ]);

    session(['tracker.ticket_id' => $ticket->id]);

    $file = \Illuminate\Http\UploadedFile::fake()->create('certificate.pdf', 200, 'application/pdf');

    Livewire::test(TicketTracker::class)
        ->set("attachmentFiles.{$requestingMessage->id}", $file)
        ->call('uploadAttachment', $requestingMessage->id)
        ->assertHasNoErrors();

    expect(
        \App\Models\TicketAttachment::where('ticket_id', $ticket->id)
            ->where('ticket_message_id', $requestingMessage->id)
            ->whereNull('uploader_id')
            ->where('original_filename', 'certificate.pdf')
            ->exists()
    )->toBeTrue();
});

test('invalid file type is rejected on upload', function () {
    Storage::fake('local');

    $ticket = makePublicTicket('Ana Reyes');
    $staff = User::factory()->create();

    $requestingMessage = \App\Models\TicketMessage::factory()->requestingAttachment()->create([
        'ticket_id' => $ticket->id,
        'sender_id' => $staff->id,
        'body' => 'Upload needed.',
        'is_internal_note' => false,
    ]);

    session(['tracker.ticket_id' => $ticket->id]);

    $file = \Illuminate\Http\UploadedFile::fake()->create('script.exe', 100, 'application/x-msdownload');

    Livewire::test(TicketTracker::class)
        ->set("attachmentFiles.{$requestingMessage->id}", $file)
        ->call('uploadAttachment', $requestingMessage->id)
        ->assertHasErrors(["attachmentFiles.{$requestingMessage->id}"]);
});
```

Add `use Illuminate\Support\Facades\Storage;` at the top of the test file.

- [ ] **Step 2: Run to confirm failure**

```bash
php artisan test --compact --filter="upload button|guest can upload|invalid file"
```

Expected: FAIL.

- [ ] **Step 3: Run all upload tests (component already has uploadAttachment implemented)**

```bash
php artisan test --compact --filter="TicketTrackerTest"
```

Expected: all PASS (the component was already fully implemented in Task 4).

- [ ] **Step 4: Run Pint**

```bash
vendor/bin/pint --dirty --format agent
```

Fix any formatting issues reported.

- [ ] **Step 5: Run the full test suite**

```bash
php artisan test --compact
```

Expected: all PASS.

- [ ] **Step 6: Final commit**

```bash
git add tests/Feature/TicketTrackerTest.php
git commit -m "test: add file upload tests for public ticket tracker"
```

---

## Summary of Commits

1. `feat: add guest tracking schema — nullable sender, guest_name, requests_attachment, nullable uploader`
2. `feat: add requests_attachment toggle to admin messaging and guest message attribution`
3. `feat: add TicketTracker component, /track route, and public navigation links`
4. `feat: implement ticket tracker detail state with message thread and text reply`
5. `test: add file upload tests for public ticket tracker`
