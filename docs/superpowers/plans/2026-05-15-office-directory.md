# Office Directory & Service Catalog Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add a public Office Directory with per-office service listings, inline citizen charter and work instruction viewing, and a pre-filled ticket request shortcut for authenticated students.

**Architecture:** Two new public Livewire pages (OfficeList, OfficeDetail) rendered under a lightweight public layout, served on unauthenticated routes. File columns added to `offices` and `service_types` tables; uploads managed via Filament admin. `CreateTicket` gains a `mount()` that accepts a `serviceTypeId` parameter and jumps directly to step 4.

**Tech Stack:** Laravel 13, Livewire 4, Tailwind CSS v4, Filament v5 (FileUpload), Alpine.js, `public` storage disk.

---

## File Map

| Action | Path |
|--------|------|
| Create | `database/migrations/YYYY_MM_DD_HHMMSS_add_document_columns_to_offices_and_service_types.php` |
| Modify | `app/Models/Office.php` — add `citizen_charter` to `#[Fillable]` |
| Modify | `app/Models/ServiceType.php` — add `work_instruction` to `#[Fillable]` |
| Modify | `app/Filament/Resources/OfficeResource.php` — add FileUpload field |
| Modify | `app/Filament/Resources/ServiceTypeResource.php` — add FileUpload field |
| Create | `resources/views/components/layouts/public.blade.php` |
| Create | `app/Livewire/Public/OfficeList.php` |
| Create | `resources/views/livewire/public/office-list.blade.php` |
| Create | `app/Livewire/Public/OfficeDetail.php` |
| Create | `resources/views/livewire/public/office-detail.blade.php` |
| Modify | `routes/web.php` — add two public routes |
| Modify | `resources/views/welcome.blade.php` — update office dropdown links |
| Modify | `resources/views/components/layouts/portal.blade.php` — add Offices nav link |
| Modify | `app/Livewire/Portal/CreateTicket.php` — add `mount()` pre-fill |
| Create | `tests/Feature/Public/OfficeDirectoryTest.php` |
| Create | `tests/Feature/Portal/CreateTicketPreFillTest.php` |

---

### Task 1: Migration — add file columns

**Files:**
- Create: `database/migrations/YYYY_MM_DD_HHMMSS_add_document_columns_to_offices_and_service_types.php`
- Modify: `app/Models/Office.php`
- Modify: `app/Models/ServiceType.php`

- [ ] **Step 1: Generate the migration**

```bash
php artisan make:migration add_document_columns_to_offices_and_service_types --no-interaction
```

- [ ] **Step 2: Fill in the migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('offices', function (Blueprint $table) {
            $table->string('citizen_charter')->nullable()->after('email');
        });

        Schema::table('service_types', function (Blueprint $table) {
            $table->string('work_instruction')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('offices', function (Blueprint $table) {
            $table->dropColumn('citizen_charter');
        });

        Schema::table('service_types', function (Blueprint $table) {
            $table->dropColumn('work_instruction');
        });
    }
};
```

- [ ] **Step 3: Update `Office` model fillable**

In `app/Models/Office.php`, change the `#[Fillable]` attribute:

```php
#[Fillable(['name', 'slug', 'description', 'email', 'citizen_charter', 'is_active', 'sort_order'])]
```

- [ ] **Step 4: Update `ServiceType` model fillable**

In `app/Models/ServiceType.php`, change the `#[Fillable]` attribute:

```php
#[Fillable(['service_category_id', 'name', 'slug', 'description', 'work_instruction', 'sla_days', 'is_active', 'sort_order'])]
```

- [ ] **Step 5: Run the migration**

```bash
php artisan migrate
```

Expected: migration runs successfully, both columns added.

- [ ] **Step 6: Commit**

```bash
git add database/migrations/ app/Models/Office.php app/Models/ServiceType.php
git commit -m "feat: add citizen_charter and work_instruction file columns"
```

---

### Task 2: Admin file upload fields

**Files:**
- Modify: `app/Filament/Resources/OfficeResource.php`
- Modify: `app/Filament/Resources/ServiceTypeResource.php`

- [ ] **Step 1: Write the failing test**

```bash
php artisan make:test --pest Admin/OfficeFileUploadTest --no-interaction
```

```php
<?php

use App\Filament\Resources\OfficeResource\Pages\EditOffice;
use App\Models\Office;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;

beforeEach(fn () => $this->seed(RoleSeeder::class));

test('super_admin can see citizen_charter upload field on office edit', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');
    $office = Office::factory()->create();

    $this->actingAs($admin);

    Livewire::test(EditOffice::class, ['record' => $office->id])
        ->assertFormFieldExists('citizen_charter');
});
```

- [ ] **Step 2: Run test to verify it fails**

```bash
php artisan test --compact --filter="super_admin can see citizen_charter"
```

Expected: FAIL — field does not exist yet.

- [ ] **Step 3: Add FileUpload to OfficeResource**

In `app/Filament/Resources/OfficeResource.php`, add the import and update `form()`:

```php
use Filament\Forms\Components\FileUpload;
```

Inside `form()`, add after the `description` field:

```php
FileUpload::make('citizen_charter')
    ->label('Citizen Charter')
    ->disk('public')
    ->directory('citizen-charters')
    ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
    ->maxSize(10240)
    ->columnSpanFull(),
```

- [ ] **Step 4: Add FileUpload to ServiceTypeResource**

In `app/Filament/Resources/ServiceTypeResource.php`, add the import and update `form()`:

```php
use Filament\Forms\Components\FileUpload;
```

Inside `form()`, add after the `description` field:

```php
FileUpload::make('work_instruction')
    ->label('Work Instruction')
    ->disk('public')
    ->directory('work-instructions')
    ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
    ->maxSize(10240)
    ->columnSpanFull(),
```

- [ ] **Step 5: Run test to verify it passes**

```bash
php artisan test --compact --filter="super_admin can see citizen_charter"
```

Expected: PASS.

- [ ] **Step 6: Ensure storage symlink exists**

```bash
php artisan storage:link
```

Expected: "The [public/storage] link has been connected to [storage/app/public]." (or already exists message).

- [ ] **Step 7: Commit**

```bash
git add app/Filament/Resources/OfficeResource.php app/Filament/Resources/ServiceTypeResource.php tests/Feature/Admin/OfficeFileUploadTest.php
git commit -m "feat: add citizen charter and work instruction file upload to admin"
```

---

### Task 3: Public layout component

**Files:**
- Create: `resources/views/components/layouts/public.blade.php`

- [ ] **Step 1: Create the layout**

```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ isset($title) ? $title . ' — ' . config('app.name') : config('app.name') }}</title>
    <link rel="icon" href="/favicon.ico" sizes="any">
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white antialiased">

    <header class="sticky top-0 z-50 w-full border-b border-gray-200 bg-white/95 backdrop-blur-lg">
        <nav class="mx-auto flex h-14 w-full max-w-6xl items-center justify-between px-4">
            <a href="{{ route('home') }}"
               class="flex flex-col rounded-md px-2 py-1 leading-none hover:bg-black/5 transition-colors">
                <span class="text-[10px] font-extrabold tracking-widest text-[#0089CB]">BICOL UNIVERSITY</span>
                <span class="text-[9px] font-medium tracking-widest text-gray-500">SERVICE REQUEST SYSTEM</span>
            </a>

            <div class="flex items-center gap-4">
                <a href="{{ route('offices.index') }}"
                   class="text-sm font-medium transition-colors {{ request()->routeIs('offices.*') ? 'text-gray-900' : 'text-gray-500 hover:text-gray-900' }}">
                    Offices
                </a>

                @auth
                    <a href="{{ route('portal.tickets.index') }}"
                       class="rounded-md bg-[#0089CB] px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-[#0077b3]">
                        My Portal
                    </a>
                @else
                    <a href="{{ route('auth.google') }}"
                       class="rounded-md bg-[#0089CB] px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-[#0077b3]">
                        Sign In
                    </a>
                @endauth
            </div>
        </nav>
    </header>

    <main>
        {{ $slot }}
    </main>

    <footer class="mt-20 border-t border-gray-100 bg-gray-50 py-10">
        <div class="mx-auto max-w-6xl px-4 text-center text-sm text-gray-400">
            © {{ date('Y') }} {{ config('app.name') }} · Bicol University
        </div>
    </footer>

</body>
</html>
```

- [ ] **Step 2: Commit**

```bash
git add resources/views/components/layouts/public.blade.php
git commit -m "feat: add public layout component"
```

---

### Task 4: OfficeList Livewire component

**Files:**
- Create: `app/Livewire/Public/OfficeList.php`
- Create: `resources/views/livewire/public/office-list.blade.php`
- Create: `tests/Feature/Public/OfficeDirectoryTest.php`

- [ ] **Step 1: Write the failing test**

```bash
php artisan make:test --pest Public/OfficeDirectoryTest --no-interaction
```

```php
<?php

use App\Livewire\Public\OfficeList;
use App\Models\Office;
use Livewire\Livewire;

test('office list shows active offices', function () {
    $active = Office::factory()->create(['name' => 'Finance Office', 'is_active' => true]);
    $inactive = Office::factory()->create(['name' => 'Hidden Office', 'is_active' => false]);

    Livewire::test(OfficeList::class)
        ->assertSee('Finance Office')
        ->assertDontSee('Hidden Office');
});

test('office list page is publicly accessible', function () {
    Office::factory()->create(['is_active' => true]);

    $this->get(route('offices.index'))
        ->assertOk();
});
```

- [ ] **Step 2: Run test to verify it fails**

```bash
php artisan test --compact --filter=OfficeDirectoryTest
```

Expected: FAIL — class and route do not exist yet.

- [ ] **Step 3: Create the Livewire component class**

```bash
php artisan make:livewire Public/OfficeList --no-interaction
```

Replace `app/Livewire/Public/OfficeList.php` with:

```php
<?php

namespace App\Livewire\Public;

use App\Models\Office;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.public')]
class OfficeList extends Component
{
    public function render(): View
    {
        $offices = Office::active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('livewire.public.office-list', compact('offices'));
    }
}
```

- [ ] **Step 4: Create the view**

Replace `resources/views/livewire/public/office-list.blade.php` with:

```blade
<div>
    {{-- Page header --}}
    <div class="bg-gradient-to-br from-[#0089CB]/5 to-white border-b border-gray-100">
        <div class="mx-auto max-w-6xl px-4 py-14">
            <p class="text-sm font-semibold tracking-widest text-[#0089CB] uppercase mb-3">Bicol University</p>
            <h1 class="text-4xl font-bold text-gray-900 mb-3">University Offices</h1>
            <p class="text-lg text-gray-500 max-w-xl">
                Browse offices, explore the services they provide, and submit requests directly.
            </p>
        </div>
    </div>

    {{-- Office grid --}}
    <div class="mx-auto max-w-6xl px-4 py-12">
        @if ($offices->isEmpty())
            <p class="text-gray-500 text-center py-16">No offices available at this time.</p>
        @else
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($offices as $office)
                    <a href="{{ route('offices.show', $office->slug) }}"
                       class="group flex flex-col rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition-all hover:border-[#0089CB]/50 hover:shadow-md">
                        <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-xl bg-[#0089CB]/10">
                            <x-heroicon-o-building-office-2 class="h-6 w-6 text-[#0089CB]" />
                        </div>
                        <h2 class="text-base font-semibold text-gray-900 group-hover:text-[#0089CB] transition-colors">
                            {{ $office->name }}
                        </h2>
                        @if ($office->description)
                            <p class="mt-2 flex-1 text-sm text-gray-500 line-clamp-2">{{ $office->description }}</p>
                        @endif
                        @if ($office->email)
                            <p class="mt-3 text-xs text-gray-400">{{ $office->email }}</p>
                        @endif
                        <div class="mt-5 flex items-center gap-1 text-sm font-semibold text-[#0089CB]">
                            View services
                            <x-heroicon-o-arrow-right class="h-4 w-4 transition-transform group-hover:translate-x-0.5" />
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</div>
```

- [ ] **Step 5: Add the route**

In `routes/web.php`, add before the portal group:

```php
use App\Livewire\Public\OfficeList;
use App\Livewire\Public\OfficeDetail;

Route::get('/offices', OfficeList::class)->name('offices.index');
Route::get('/offices/{slug}', OfficeDetail::class)->name('offices.show');
```

- [ ] **Step 6: Run test to verify it passes**

```bash
php artisan test --compact --filter=OfficeDirectoryTest
```

Expected: PASS for both tests.

- [ ] **Step 7: Commit**

```bash
git add app/Livewire/Public/OfficeList.php resources/views/livewire/public/office-list.blade.php routes/web.php tests/Feature/Public/OfficeDirectoryTest.php
git commit -m "feat: add public office list page"
```

---

### Task 5: OfficeDetail Livewire component

**Files:**
- Create: `app/Livewire/Public/OfficeDetail.php`
- Create: `resources/views/livewire/public/office-detail.blade.php`
- Modify: `tests/Feature/Public/OfficeDirectoryTest.php`

- [ ] **Step 1: Add failing tests to OfficeDirectoryTest.php**

Append to `tests/Feature/Public/OfficeDirectoryTest.php`:

```php
use App\Models\ServiceCategory;
use App\Models\ServiceType;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use App\Livewire\Public\OfficeDetail;

test('office detail shows office name and services', function () {
    $office = Office::factory()->create(['is_active' => true]);
    $category = ServiceCategory::factory()->create(['office_id' => $office->id, 'is_active' => true]);
    $service = ServiceType::factory()->create(['service_category_id' => $category->id, 'is_active' => true]);

    Livewire::test(OfficeDetail::class, ['slug' => $office->slug])
        ->assertSee($office->name)
        ->assertSee($category->name)
        ->assertSee($service->name);
});

test('office detail shows citizen charter viewer when file is set', function () {
    $office = Office::factory()->create([
        'is_active' => true,
        'citizen_charter' => 'citizen-charters/charter.pdf',
    ]);

    Livewire::test(OfficeDetail::class, ['slug' => $office->slug])
        ->assertSee('View Citizen Charter');
});

test('office detail hides citizen charter viewer when no file', function () {
    $office = Office::factory()->create(['is_active' => true, 'citizen_charter' => null]);

    Livewire::test(OfficeDetail::class, ['slug' => $office->slug])
        ->assertDontSee('View Citizen Charter');
});

test('office detail shows work instruction viewer for service when file is set', function () {
    $office = Office::factory()->create(['is_active' => true]);
    $category = ServiceCategory::factory()->create(['office_id' => $office->id, 'is_active' => true]);
    ServiceType::factory()->create([
        'service_category_id' => $category->id,
        'is_active' => true,
        'work_instruction' => 'work-instructions/guide.pdf',
    ]);

    Livewire::test(OfficeDetail::class, ['slug' => $office->slug])
        ->assertSee('View Work Instruction');
});

test('office detail shows request button for authenticated onboarded student', function () {
    $this->seed(RoleSeeder::class);
    $user = User::factory()->create(['onboarding_completed_at' => now()]);
    $user->assignRole('student');

    $office = Office::factory()->create(['is_active' => true]);
    $category = ServiceCategory::factory()->create(['office_id' => $office->id, 'is_active' => true]);
    ServiceType::factory()->create(['service_category_id' => $category->id, 'is_active' => true]);

    $this->actingAs($user);

    Livewire::test(OfficeDetail::class, ['slug' => $office->slug])
        ->assertSee('Request this service');
});

test('office detail shows sign in link for guests', function () {
    $office = Office::factory()->create(['is_active' => true]);
    $category = ServiceCategory::factory()->create(['office_id' => $office->id, 'is_active' => true]);
    ServiceType::factory()->create(['service_category_id' => $category->id, 'is_active' => true]);

    Livewire::test(OfficeDetail::class, ['slug' => $office->slug])
        ->assertSee('Sign in to request');
});

test('office detail returns 404 for inactive office', function () {
    $office = Office::factory()->create(['is_active' => false]);

    $this->get(route('offices.show', $office->slug))
        ->assertNotFound();
});
```

- [ ] **Step 2: Run tests to verify they fail**

```bash
php artisan test --compact --filter=OfficeDirectoryTest
```

Expected: new tests FAIL — OfficeDetail class doesn't exist yet.

- [ ] **Step 3: Create the Livewire component class**

```bash
php artisan make:livewire Public/OfficeDetail --no-interaction
```

Replace `app/Livewire/Public/OfficeDetail.php` with:

```php
<?php

namespace App\Livewire\Public;

use App\Models\Office;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('components.layouts.public')]
class OfficeDetail extends Component
{
    #[Locked]
    public string $slug = '';

    public function mount(string $slug): void
    {
        $this->slug = $slug;
    }

    public function render(): View
    {
        $office = Office::where('slug', $this->slug)
            ->where('is_active', true)
            ->with([
                'serviceCategories' => function ($q) {
                    $q->where('is_active', true)
                        ->orderBy('sort_order')
                        ->with([
                            'serviceTypes' => function ($q2) {
                                $q2->where('is_active', true)->orderBy('sort_order');
                            },
                        ]);
                },
            ])
            ->firstOrFail();

        $canRequest = auth()->check()
            && auth()->user()->hasRole('student')
            && auth()->user()->onboarding_completed_at !== null;

        return view('livewire.public.office-detail', compact('office', 'canRequest'));
    }
}
```

- [ ] **Step 4: Create the view**

Create `resources/views/livewire/public/office-detail.blade.php`:

```blade
<div x-data="{ viewerOpen: false, viewerUrl: '', viewerType: '', viewerTitle: '' }">

    {{-- File viewer modal --}}
    <div x-show="viewerOpen"
         x-cloak
         @keydown.escape.window="viewerOpen = false"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
        <div @click.stop
             class="flex w-full max-w-5xl flex-col rounded-2xl bg-white shadow-2xl overflow-hidden max-h-[90vh]">
            <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4 shrink-0">
                <h3 class="text-base font-semibold text-gray-900" x-text="viewerTitle"></h3>
                <button @click="viewerOpen = false"
                        class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors">
                    <x-heroicon-o-x-mark class="h-5 w-5" />
                </button>
            </div>
            <div class="flex-1 overflow-auto p-2">
                <template x-if="viewerType === 'pdf'">
                    <iframe :src="viewerUrl" class="h-[75vh] w-full rounded border border-gray-100"></iframe>
                </template>
                <template x-if="viewerType === 'image'">
                    <img :src="viewerUrl" class="mx-auto max-h-[75vh] max-w-full rounded object-contain">
                </template>
            </div>
        </div>
    </div>

    {{-- Office header --}}
    <div class="bg-gradient-to-br from-[#0089CB]/5 to-white border-b border-gray-100">
        <div class="mx-auto max-w-6xl px-4 py-12">
            <a href="{{ route('offices.index') }}"
               class="mb-5 inline-flex items-center gap-1.5 text-sm font-medium text-gray-400 hover:text-gray-600 transition-colors">
                <x-heroicon-o-arrow-left class="h-4 w-4" />
                All offices
            </a>

            <div class="flex flex-wrap items-start justify-between gap-6">
                <div>
                    <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-xl bg-[#0089CB]/10">
                        <x-heroicon-o-building-office-2 class="h-7 w-7 text-[#0089CB]" />
                    </div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ $office->name }}</h1>
                    @if ($office->description)
                        <p class="mt-2 max-w-2xl text-gray-500">{{ $office->description }}</p>
                    @endif
                    @if ($office->email)
                        <a href="mailto:{{ $office->email }}"
                           class="mt-3 inline-flex items-center gap-1.5 text-sm text-gray-400 hover:text-[#0089CB] transition-colors">
                            <x-heroicon-o-envelope class="h-4 w-4" />
                            {{ $office->email }}
                        </a>
                    @endif
                </div>

                @if ($office->citizen_charter)
                    @php
                        $ext = strtolower(pathinfo($office->citizen_charter, PATHINFO_EXTENSION));
                        $type = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']) ? 'image' : 'pdf';
                        $url = Storage::disk('public')->url($office->citizen_charter);
                    @endphp
                    <button @click="viewerUrl='{{ $url }}'; viewerType='{{ $type }}'; viewerTitle='Citizen Charter'; viewerOpen=true"
                            class="inline-flex items-center gap-2 rounded-xl border border-[#0089CB]/30 bg-[#0089CB]/5 px-4 py-2.5 text-sm font-semibold text-[#0089CB] transition-all hover:bg-[#0089CB]/10 shrink-0">
                        <x-heroicon-o-document-text class="h-4 w-4" />
                        View Citizen Charter
                    </button>
                @endif
            </div>
        </div>
    </div>

    {{-- Services --}}
    <div class="mx-auto max-w-6xl px-4 py-12">
        @if ($office->serviceCategories->isEmpty())
            <div class="rounded-2xl border border-dashed border-gray-200 py-20 text-center text-gray-400">
                <x-heroicon-o-squares-2x2 class="mx-auto mb-3 h-10 w-10 text-gray-300" />
                No services listed yet.
            </div>
        @else
            <div class="space-y-10">
                @foreach ($office->serviceCategories as $category)
                    @if ($category->serviceTypes->isNotEmpty())
                        <section>
                            <h2 class="mb-1 text-xl font-bold text-gray-900">{{ $category->name }}</h2>
                            @if ($category->description)
                                <p class="mb-5 text-sm text-gray-500">{{ $category->description }}</p>
                            @else
                                <div class="mb-5"></div>
                            @endif

                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                @foreach ($category->serviceTypes as $service)
                                    <div class="flex flex-col rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                                        <div class="flex items-start justify-between gap-3 mb-3">
                                            <h3 class="text-base font-semibold text-gray-900 leading-snug">{{ $service->name }}</h3>
                                            @if ($service->sla_days)
                                                <span class="shrink-0 rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-500">
                                                    {{ $service->sla_days }}d SLA
                                                </span>
                                            @endif
                                        </div>

                                        @if ($service->description)
                                            <p class="text-sm text-gray-500 flex-1 mb-4">{{ $service->description }}</p>
                                        @else
                                            <div class="flex-1"></div>
                                        @endif

                                        <div class="flex flex-wrap items-center gap-2 pt-3 border-t border-gray-100">
                                            @if ($service->work_instruction)
                                                @php
                                                    $ext = strtolower(pathinfo($service->work_instruction, PATHINFO_EXTENSION));
                                                    $wiType = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']) ? 'image' : 'pdf';
                                                    $wiUrl = Storage::disk('public')->url($service->work_instruction);
                                                @endphp
                                                <button @click="viewerUrl='{{ $wiUrl }}'; viewerType='{{ $wiType }}'; viewerTitle='Work Instruction — {{ addslashes($service->name) }}'; viewerOpen=true"
                                                        class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-600 transition-colors hover:border-gray-300 hover:bg-gray-50">
                                                    <x-heroicon-o-document-text class="h-3.5 w-3.5" />
                                                    View Work Instruction
                                                </button>
                                            @endif

                                            @if ($canRequest)
                                                <a href="{{ route('portal.tickets.create', ['serviceTypeId' => $service->id]) }}"
                                                   class="ml-auto inline-flex items-center gap-1.5 rounded-lg bg-[#0089CB] px-3 py-1.5 text-xs font-semibold text-white transition-colors hover:bg-[#0077b3]">
                                                    <x-heroicon-o-paper-airplane class="h-3.5 w-3.5" />
                                                    Request this service
                                                </a>
                                            @else
                                                <a href="{{ route('login') }}"
                                                   class="ml-auto inline-flex items-center gap-1.5 rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-500 transition-colors hover:bg-gray-50">
                                                    Sign in to request
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    @endif
                @endforeach
            </div>
        @endif
    </div>
</div>
```

- [ ] **Step 5: Run tests to verify they pass**

```bash
php artisan test --compact --filter=OfficeDirectoryTest
```

Expected: all tests PASS.

- [ ] **Step 6: Commit**

```bash
git add app/Livewire/Public/OfficeDetail.php resources/views/livewire/public/office-detail.blade.php tests/Feature/Public/OfficeDirectoryTest.php
git commit -m "feat: add public office detail page with file viewer and request buttons"
```

---

### Task 6: Navigation updates

**Files:**
- Modify: `resources/views/welcome.blade.php` — office dropdown links
- Modify: `resources/views/components/layouts/portal.blade.php` — add Offices link

- [ ] **Step 1: Update welcome page office dropdown links**

In `resources/views/welcome.blade.php`, find the desktop office dropdown loop (around line 76) and the mobile one (around line 144). Both currently link to `route('login')`.

**Desktop dropdown** — find:
```blade
@foreach ($offices as $office)
```
and the `href="{{ route('login') }}"` inside it. Replace the `<a>` tag href:

Before:
```blade
href="{{ route('login') }}"
```
After:
```blade
href="{{ route('offices.show', $office->slug) }}"
```

Do this for **both** the desktop loop and the mobile loop.

Also update the "View all offices" links (two instances). Before:
```blade
href="{{ route('login') }}"
```
After (for the "View all offices" links):
```blade
href="{{ route('offices.index') }}"
```

- [ ] **Step 2: Add "Offices" link to portal nav**

In `resources/views/components/layouts/portal.blade.php`, find the `<div class="portal-actions"` section. Add an Offices link before the "My Requests" link:

```blade
<a href="{{ route('offices.index') }}"
   class="portal-link {{ request()->routeIs('offices.*') ? 'portal-link-active' : '' }}">
    <span class="portal-icon"><x-heroicon-o-building-office-2 /></span>
    <span>Offices</span>
</a>
```

Add the same link to the mobile `portal-menu-panel` section:

```blade
<a href="{{ route('offices.index') }}" wire:navigate
   class="portal-link {{ request()->routeIs('offices.*') ? 'portal-link-active' : '' }}">
    <span class="portal-icon"><x-heroicon-o-building-office-2 /></span>
    <span>Offices</span>
</a>
```

- [ ] **Step 3: Build assets**

```bash
npm run build
```

Expected: build completes without errors.

- [ ] **Step 4: Commit**

```bash
git add resources/views/welcome.blade.php resources/views/components/layouts/portal.blade.php
git commit -m "feat: add Offices navigation to public and portal layouts"
```

---

### Task 7: CreateTicket pre-fill from service

**Files:**
- Modify: `app/Livewire/Portal/CreateTicket.php`
- Create: `tests/Feature/Portal/CreateTicketPreFillTest.php`

- [ ] **Step 1: Write the failing tests**

```bash
php artisan make:test --pest Portal/CreateTicketPreFillTest --no-interaction
```

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

test('mount pre-fills office, category, and service when serviceTypeId is provided', function () {
    $user = User::factory()->create(['onboarding_completed_at' => now()]);
    $user->assignRole('student');

    $office = Office::factory()->create(['is_active' => true]);
    $category = ServiceCategory::factory()->create(['office_id' => $office->id, 'is_active' => true]);
    $service = ServiceType::factory()->create(['service_category_id' => $category->id, 'is_active' => true]);

    $this->actingAs($user);

    Livewire::test(CreateTicket::class, ['serviceTypeId' => $service->id])
        ->assertSet('officeId', $office->id)
        ->assertSet('serviceCategoryId', $category->id)
        ->assertSet('serviceTypeId', $service->id)
        ->assertSet('step', 4);
});

test('mount ignores unknown or inactive serviceTypeId', function () {
    $user = User::factory()->create(['onboarding_completed_at' => now()]);
    $user->assignRole('student');

    $this->actingAs($user);

    Livewire::test(CreateTicket::class, ['serviceTypeId' => 999999])
        ->assertSet('officeId', null)
        ->assertSet('serviceTypeId', null)
        ->assertSet('step', 1);
});

test('mount starts at step 1 when no serviceTypeId is given', function () {
    $user = User::factory()->create(['onboarding_completed_at' => now()]);
    $user->assignRole('student');

    $this->actingAs($user);

    Livewire::test(CreateTicket::class)
        ->assertSet('step', 1)
        ->assertSet('officeId', null);
});
```

- [ ] **Step 2: Run tests to verify they fail**

```bash
php artisan test --compact --filter=CreateTicketPreFillTest
```

Expected: FAIL — `CreateTicket` has no `mount()` and no `serviceTypeId` parameter.

- [ ] **Step 3: Add `mount()` to CreateTicket**

In `app/Livewire/Portal/CreateTicket.php`, add a `mount()` method and `ServiceCategory` import. The current class has no `mount()`. Add after the `use WithFileUploads;` line and before `public int $step = 1;`:

Also add the import at the top (it's already there in the `use` statements, but confirm `ServiceType` is imported):

```php
use App\Models\ServiceType;
```

Then add the `mount()` method after the class properties (before `updatedOfficeId()`):

```php
public function mount(?int $serviceTypeId = null): void
{
    if ($serviceTypeId === null) {
        return;
    }

    $service = ServiceType::with('serviceCategory')
        ->where('is_active', true)
        ->find($serviceTypeId);

    if ($service === null) {
        return;
    }

    $this->serviceTypeId = $service->id;
    $this->serviceCategoryId = $service->service_category_id;
    $this->officeId = $service->serviceCategory->office_id;
    $this->step = 4;
}
```

- [ ] **Step 4: Run tests to verify they pass**

```bash
php artisan test --compact --filter=CreateTicketPreFillTest
```

Expected: all 3 tests PASS.

- [ ] **Step 5: Run full test suite to check for regressions**

```bash
php artisan test --compact
```

Expected: all tests PASS.

- [ ] **Step 6: Run Pint**

```bash
vendor/bin/pint app/Livewire/Portal/CreateTicket.php --dirty --format agent
```

- [ ] **Step 7: Commit**

```bash
git add app/Livewire/Portal/CreateTicket.php tests/Feature/Portal/CreateTicketPreFillTest.php
git commit -m "feat: pre-fill ticket form when navigating from office directory"
```
