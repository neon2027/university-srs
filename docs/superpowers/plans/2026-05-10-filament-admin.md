# Filament Admin Panel Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build the complete BUSRS Filament v5 admin panel — ticket management with assign/forward/message actions, office/service CRUD, staff management, and a stats dashboard — scoped by RBAC role.

**Architecture:** Filament v5 panel at `/admin` with a single `AdminPanelProvider`. TicketResource is the core resource; RBAC (Spatie) scopes the Eloquent query so staff see only their office's tickets and super_admin sees all. Forwarding writes a `ForwardingLog` and a `TicketHistory` atomically inside a DB transaction. The messaging panel is a dedicated Livewire component embedded in the Filament ViewTicket page.

**Tech Stack:** Filament v5, Spatie Laravel Permission v7, Livewire v4, Pest v4, PHP 8.4, Laravel 13.

---

## File Map

| File | Purpose |
|------|---------|
| `app/Providers/Filament/AdminPanelProvider.php` | Panel registration, navigation groups, RBAC guard |
| `app/Filament/Resources/TicketResource.php` | Core resource — table, filters, RBAC scope |
| `app/Filament/Resources/TicketResource/Pages/ListTickets.php` | Table page |
| `app/Filament/Resources/TicketResource/Pages/ViewTicket.php` | Detail view with infolist + embedded messaging |
| `app/Filament/Resources/TicketResource/Pages/EditTicket.php` | Status/priority/assignment edit |
| `app/Filament/Actions/AssignTicketAction.php` | Reusable action — select assignee, log history |
| `app/Filament/Actions/ForwardTicketAction.php` | Reusable action — select target office, credit type, log |
| `app/Filament/Widgets/TicketStatsOverview.php` | Dashboard stat cards |
| `app/Livewire/Admin/TicketMessaging.php` | Staff-side chat Livewire component |
| `resources/views/livewire/admin/ticket-messaging.blade.php` | Messaging panel view |
| `app/Filament/Resources/OfficeResource.php` | Office CRUD + staff pivot management |
| `app/Filament/Resources/OfficeResource/Pages/ListOffices.php` | |
| `app/Filament/Resources/OfficeResource/Pages/CreateOffice.php` | |
| `app/Filament/Resources/OfficeResource/Pages/EditOffice.php` | Staff assignment relation manager |
| `app/Filament/Resources/OfficeResource/RelationManagers/StaffRelationManager.php` | Attach/detach staff from office |
| `app/Filament/Resources/ServiceCategoryResource.php` | Category CRUD |
| `app/Filament/Resources/ServiceTypeResource.php` | Service type CRUD with SLA field |
| `app/Filament/Resources/CannedResponseResource.php` | Canned responses CRUD |
| `app/Filament/Resources/UserResource.php` | Staff management — role + office assignment |
| `app/Filament/Resources/UserResource/Pages/ListUsers.php` | |
| `app/Filament/Resources/UserResource/Pages/EditUser.php` | |
| `tests/Feature/Admin/AdminPanelAccessTest.php` | Panel access by role |
| `tests/Feature/Admin/TicketResourceTest.php` | Table, filters, scoping |
| `tests/Feature/Admin/AssignTicketTest.php` | Assign action |
| `tests/Feature/Admin/ForwardTicketTest.php` | Forward action + ForwardingLog |
| `tests/Feature/Admin/TicketMessagingTest.php` | Admin messaging panel |
| `tests/Feature/Admin/OfficeResourceTest.php` | Office CRUD + staff pivot |
| `tests/Feature/Admin/ServiceResourceTest.php` | Category + type CRUD |

---

## Task 1: Install Filament v5 & Scaffold Panel

**Files:**
- Create: `app/Providers/Filament/AdminPanelProvider.php` (generated)
- Modify: `bootstrap/providers.php` (auto-updated by installer)

- [ ] **Step 1: Require Filament**

```bash
composer require filament/filament:"^5.0" --no-interaction
```

Expected: Filament v5.x installs without conflict.

- [ ] **Step 2: Install the panel**

```bash
php artisan filament:install --panels --no-interaction
```

When prompted for a panel ID, enter: `admin`

Expected: `app/Providers/Filament/AdminPanelProvider.php` is created. `bootstrap/providers.php` now includes `App\Providers\Filament\AdminPanelProvider::class`.

- [ ] **Step 3: Run migrations (Filament adds its own cache table)**

```bash
php artisan migrate --no-interaction
```

- [ ] **Step 4: Write the access test**

```bash
php artisan make:test --pest AdminPanelAccessTest --no-interaction
```

Add to `tests/Feature/AdminPanelAccessTest.php`:

```php
<?php

use App\Models\User;
use Filament\Facades\Filament;

beforeEach(fn () => Filament::setCurrentPanel(Filament::getPanel('admin')));

test('super_admin can access admin panel', function () {
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    $this->actingAs($user)
        ->get('/admin')
        ->assertSuccessful();
});

test('staff can access admin panel', function () {
    $user = User::factory()->create();
    $user->assignRole('staff');

    $this->actingAs($user)
        ->get('/admin')
        ->assertSuccessful();
});

test('student cannot access admin panel', function () {
    $user = User::factory()->create();
    $user->assignRole('student');

    $this->actingAs($user)
        ->get('/admin')
        ->assertRedirect();
});

test('unauthenticated user is redirected from admin panel', function () {
    $this->get('/admin')->assertRedirect();
});
```

- [ ] **Step 5: Run test to verify it fails (expected, Filament not configured yet)**

```bash
php artisan test --compact --filter=AdminPanelAccessTest
```

Expected: FAIL — redirect or 302 for all users.

- [ ] **Step 6: Commit install**

```bash
git add composer.json composer.lock bootstrap/providers.php app/Providers/Filament/AdminPanelProvider.php
git commit -m "chore: install Filament v5 and scaffold admin panel"
```

---

## Task 2: Configure AdminPanelProvider — RBAC, Navigation & Branding

**Files:**
- Modify: `app/Providers/Filament/AdminPanelProvider.php`
- Modify: `app/Models/User.php`

- [ ] **Step 1: Replace AdminPanelProvider with full configuration**

Replace the entire contents of `app/Providers/Filament/AdminPanelProvider.php`:

```php
<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors(['primary' => Color::Sky])
            ->brandName('BUSRS Admin')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([])
            ->navigationGroups([
                'Tickets',
                'Service Catalog',
                'Administration',
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([Authenticate::class]);
    }
}
```

- [ ] **Step 2: Add `canAccessPanel()` to User model**

Add this method to `app/Models/User.php` (add the `Panel` import too):

```php
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
```

Change the class declaration:

```php
class User extends Authenticatable implements FilamentUser
```

Add the method inside the class:

```php
public function canAccessPanel(Panel $panel): bool
{
    return $this->hasAnyRole(['super_admin', 'office_admin', 'staff']);
}
```

- [ ] **Step 3: Run access tests**

```bash
php artisan test --compact --filter=AdminPanelAccessTest
```

Expected: All 4 tests PASS.

- [ ] **Step 4: Run Pint**

```bash
vendor/bin/pint --dirty --format agent
```

- [ ] **Step 5: Commit**

```bash
git add app/Providers/Filament/AdminPanelProvider.php app/Models/User.php
git commit -m "feat: configure admin panel with RBAC and navigation groups"
```

---

## Task 3: Dashboard Stats Widget

**Files:**
- Create: `app/Filament/Widgets/TicketStatsOverview.php`
- Test: `tests/Feature/Admin/TicketStatsTest.php`

- [ ] **Step 1: Write the failing test**

```bash
php artisan make:test --pest Admin/TicketStatsTest --no-interaction
```

```php
<?php

use App\Filament\Widgets\TicketStatsOverview;
use App\Models\Ticket;
use App\Models\User;
use App\Enums\TicketStatus;
use Livewire\Livewire;

beforeEach(function () {
    $user = User::factory()->create();
    $user->assignRole('super_admin');
    $this->actingAs($user);
});

test('stats widget shows pending ticket count', function () {
    Ticket::factory()->count(3)->create(['status' => TicketStatus::Pending]);
    Ticket::factory()->count(2)->create(['status' => TicketStatus::Resolved]);

    Livewire::test(TicketStatsOverview::class)
        ->assertSeeText('3');
});
```

- [ ] **Step 2: Run test — confirm it fails**

```bash
php artisan test --compact --filter=TicketStatsTest
```

Expected: FAIL — class not found.

- [ ] **Step 3: Generate and implement the widget**

```bash
php artisan make:filament-widget TicketStatsOverview --stats-overview --no-interaction
```

Replace `app/Filament/Widgets/TicketStatsOverview.php` with:

```php
<?php

namespace App\Filament\Widgets;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TicketStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $query = Ticket::query();

        if (auth()->user()->hasAnyRole(['staff', 'office_admin'])) {
            $officeIds = auth()->user()->offices()->pluck('offices.id');
            $query->whereIn('office_id', $officeIds);
        }

        return [
            Stat::make('Pending', (clone $query)->where('status', TicketStatus::Pending)->count())
                ->color('warning')
                ->icon('heroicon-o-clock'),
            Stat::make('In Progress', (clone $query)->where('status', TicketStatus::InProgress)->count())
                ->color('info')
                ->icon('heroicon-o-arrow-path'),
            Stat::make('Forwarded', (clone $query)->where('status', TicketStatus::Forwarded)->count())
                ->color('primary')
                ->icon('heroicon-o-arrow-right-circle'),
            Stat::make('Resolved Today', (clone $query)
                ->where('status', TicketStatus::Resolved)
                ->whereDate('resolved_at', today())
                ->count())
                ->color('success')
                ->icon('heroicon-o-check-circle'),
        ];
    }
}
```

- [ ] **Step 4: Register widget in panel provider**

In `AdminPanelProvider.php`, update the `->widgets([])` line:

```php
->widgets([
    \App\Filament\Widgets\TicketStatsOverview::class,
])
```

- [ ] **Step 5: Run test**

```bash
php artisan test --compact --filter=TicketStatsTest
```

Expected: PASS.

- [ ] **Step 6: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Filament/Widgets/TicketStatsOverview.php app/Providers/Filament/AdminPanelProvider.php tests/Feature/Admin/TicketStatsTest.php
git commit -m "feat: add ticket stats overview widget to admin dashboard"
```

---

## Task 4: TicketResource — Table with Columns, Filters & RBAC Scope

**Files:**
- Create: `app/Filament/Resources/TicketResource.php`
- Create: `app/Filament/Resources/TicketResource/Pages/ListTickets.php`
- Create: `app/Filament/Resources/TicketResource/Pages/ViewTicket.php`
- Create: `app/Filament/Resources/TicketResource/Pages/EditTicket.php`
- Test: `tests/Feature/Admin/TicketResourceTest.php`

- [ ] **Step 1: Write the failing tests**

```bash
php artisan make:test --pest Admin/TicketResourceTest --no-interaction
```

```php
<?php

use App\Filament\Resources\TicketResource\Pages\ListTickets;
use App\Models\Office;
use App\Models\Ticket;
use App\Models\User;
use App\Enums\TicketStatus;
use Livewire\Livewire;

function makeStaff(Office $office): User
{
    $user = User::factory()->create();
    $user->assignRole('staff');
    $user->offices()->attach($office, ['is_primary' => true]);
    return $user;
}

test('super_admin sees all tickets', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');
    Ticket::factory()->count(5)->create();

    $this->actingAs($admin);

    Livewire::test(ListTickets::class)
        ->assertCountTableRecords(5);
});

test('staff only sees their office tickets', function () {
    $office = Office::factory()->create();
    $otherOffice = Office::factory()->create();
    $staff = makeStaff($office);

    Ticket::factory()->count(3)->create(['office_id' => $office->id]);
    Ticket::factory()->count(2)->create(['office_id' => $otherOffice->id]);

    $this->actingAs($staff);

    Livewire::test(ListTickets::class)
        ->assertCountTableRecords(3);
});

test('ticket table shows status badge', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');
    Ticket::factory()->create(['status' => TicketStatus::Pending]);

    $this->actingAs($admin);

    Livewire::test(ListTickets::class)
        ->assertSee('Pending');
});
```

- [ ] **Step 2: Run test to confirm failure**

```bash
php artisan test --compact --filter=TicketResourceTest
```

Expected: FAIL — class not found.

- [ ] **Step 3: Generate the resource**

```bash
php artisan make:filament-resource Ticket --view --no-interaction
```

- [ ] **Step 4: Implement TicketResource**

Replace `app/Filament/Resources/TicketResource.php`:

```php
<?php

namespace App\Filament\Resources;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Filament\Resources\TicketResource\Pages;
use App\Models\Office;
use App\Models\Ticket;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TicketResource extends Resource
{
    protected static ?string $model = Ticket::class;
    protected static ?string $navigationIcon = 'heroicon-o-ticket';
    protected static ?string $navigationGroup = 'Tickets';
    protected static ?int $navigationSort = 1;
    protected static ?string $recordRouteKeyName = 'ulid';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with(['office', 'serviceType', 'requester', 'assignee']);

        $user = auth()->user();

        if ($user->hasAnyRole(['staff', 'office_admin'])) {
            $officeIds = $user->offices()->pluck('offices.id');
            $query->whereIn('office_id', $officeIds);
        }

        return $query;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('ulid')
                    ->label('Ticket ID')
                    ->fontFamily('mono')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('requester.name')
                    ->label('Requester')
                    ->searchable(),
                Tables\Columns\TextColumn::make('office.name')
                    ->label('Office')
                    ->badge()
                    ->searchable(),
                Tables\Columns\TextColumn::make('serviceType.name')
                    ->label('Service')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (TicketStatus $state) => match ($state) {
                        TicketStatus::Pending => 'warning',
                        TicketStatus::Assigned, TicketStatus::InProgress => 'info',
                        TicketStatus::Forwarded => 'primary',
                        TicketStatus::Resolved, TicketStatus::Closed => 'success',
                        TicketStatus::OnHold => 'gray',
                        TicketStatus::Cancelled => 'danger',
                    })
                    ->formatStateUsing(fn (TicketStatus $state) => $state->label()),
                Tables\Columns\TextColumn::make('priority')
                    ->badge()
                    ->color(fn (TicketPriority $state) => match ($state) {
                        TicketPriority::Urgent => 'danger',
                        TicketPriority::High => 'warning',
                        TicketPriority::Normal => 'info',
                        TicketPriority::Low => 'gray',
                    })
                    ->formatStateUsing(fn (TicketPriority $state) => ucfirst($state->value)),
                Tables\Columns\TextColumn::make('assignee.name')
                    ->label('Assigned To')
                    ->default('—')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(collect(TicketStatus::cases())->mapWithKeys(
                        fn (TicketStatus $s) => [$s->value => $s->label()]
                    )),
                Tables\Filters\SelectFilter::make('priority')
                    ->options(collect(TicketPriority::cases())->mapWithKeys(
                        fn (TicketPriority $p) => [$p->value => ucfirst($p->value)]
                    )),
                Tables\Filters\SelectFilter::make('office')
                    ->relationship('office', 'name')
                    ->visible(fn () => auth()->user()->hasRole('super_admin')),
                Tables\Filters\Filter::make('unassigned')
                    ->label('Unassigned only')
                    ->query(fn (Builder $q) => $q->whereNull('assigned_to'))
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('status')
                    ->options(collect(TicketStatus::cases())->mapWithKeys(
                        fn (TicketStatus $s) => [$s->value => $s->label()]
                    ))
                    ->required(),
                Forms\Components\Select::make('priority')
                    ->options(collect(TicketPriority::cases())->mapWithKeys(
                        fn (TicketPriority $p) => [$p->value => ucfirst($p->value)]
                    ))
                    ->required(),
                Forms\Components\Select::make('assigned_to')
                    ->label('Assignee')
                    ->relationship('assignee', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTickets::route('/'),
            'view' => Pages\ViewTicket::route('/{record:ulid}'),
            'edit' => Pages\EditTicket::route('/{record:ulid}/edit'),
        ];
    }
}
```

- [ ] **Step 5: Implement the three page classes**

Replace `app/Filament/Resources/TicketResource/Pages/ListTickets.php`:

```php
<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Resources\TicketResource;
use Filament\Resources\Pages\ListRecords;

class ListTickets extends ListRecords
{
    protected static string $resource = TicketResource::class;
}
```

Replace `app/Filament/Resources/TicketResource/Pages/ViewTicket.php`:

```php
<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Resources\TicketResource;
use Filament\Resources\Pages\ViewRecord;

class ViewTicket extends ViewRecord
{
    protected static string $resource = TicketResource::class;
}
```

Replace `app/Filament/Resources/TicketResource/Pages/EditTicket.php`:

```php
<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Resources\TicketResource;
use Filament\Resources\Pages\EditRecord;

class EditTicket extends EditRecord
{
    protected static string $resource = TicketResource::class;
}
```

- [ ] **Step 6: Add Office factory (needed for tests)**

Check if `database/factories/OfficeFactory.php` exists. If so, confirm it uses `name` and `is_active`. The factory must call `Role::firstOrCreate` in RoleSeeder before using factories in tests — add `RefreshDatabase` in `Pest.php` if not already there.

Verify `tests/Pest.php` has:

```php
uses(Tests\TestCase::class, Illuminate\Foundation\Testing\RefreshDatabase::class)->in('Feature');
```

- [ ] **Step 7: Run tests**

```bash
php artisan test --compact --filter=TicketResourceTest
```

Expected: All 3 PASS.

- [ ] **Step 8: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Filament/Resources/TicketResource.php \
        app/Filament/Resources/TicketResource/Pages/ \
        tests/Feature/Admin/TicketResourceTest.php
git commit -m "feat: add TicketResource with RBAC-scoped table, filters, and columns"
```

---

## Task 5: ViewTicket Page — Infolist Detail Layout

**Files:**
- Modify: `app/Filament/Resources/TicketResource/Pages/ViewTicket.php`
- Modify: `app/Filament/Resources/TicketResource.php` (add `infolist()`)

- [ ] **Step 1: Write the failing test**

Add to `tests/Feature/Admin/TicketResourceTest.php`:

```php
use App\Filament\Resources\TicketResource\Pages\ViewTicket;

test('view ticket page renders ticket details', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');
    $ticket = Ticket::factory()->create();

    $this->actingAs($admin);

    Livewire::test(ViewTicket::class, ['record' => $ticket->ulid])
        ->assertSuccessful()
        ->assertSee($ticket->ulid);
});
```

- [ ] **Step 2: Run to confirm failure**

```bash
php artisan test --compact --filter="view ticket page renders"
```

Expected: FAIL — infolist not configured.

- [ ] **Step 3: Add `infolist()` to TicketResource**

Add this method to `TicketResource.php` after `form()`:

```php
public static function infolist(Infolist $infolist): Infolist
{
    return $infolist
        ->schema([
            \Filament\Infolists\Components\Section::make('Ticket Details')
                ->columns(2)
                ->schema([
                    \Filament\Infolists\Components\TextEntry::make('ulid')
                        ->label('Ticket ID')
                        ->fontFamily('mono')
                        ->copyable(),
                    \Filament\Infolists\Components\TextEntry::make('status')
                        ->badge()
                        ->color(fn (TicketStatus $state) => match ($state) {
                            TicketStatus::Pending => 'warning',
                            TicketStatus::Assigned, TicketStatus::InProgress => 'info',
                            TicketStatus::Forwarded => 'primary',
                            TicketStatus::Resolved, TicketStatus::Closed => 'success',
                            TicketStatus::OnHold => 'gray',
                            TicketStatus::Cancelled => 'danger',
                        })
                        ->formatStateUsing(fn (TicketStatus $state) => $state->label()),
                    \Filament\Infolists\Components\TextEntry::make('requester.name')
                        ->label('Requester'),
                    \Filament\Infolists\Components\TextEntry::make('office.name')
                        ->label('Office'),
                    \Filament\Infolists\Components\TextEntry::make('serviceType.name')
                        ->label('Service Type'),
                    \Filament\Infolists\Components\TextEntry::make('priority')
                        ->formatStateUsing(fn (TicketPriority $state) => ucfirst($state->value))
                        ->badge()
                        ->color(fn (TicketPriority $state) => match ($state) {
                            TicketPriority::Urgent => 'danger',
                            TicketPriority::High => 'warning',
                            TicketPriority::Normal => 'info',
                            TicketPriority::Low => 'gray',
                        }),
                    \Filament\Infolists\Components\TextEntry::make('assignee.name')
                        ->label('Assigned To')
                        ->default('Unassigned'),
                    \Filament\Infolists\Components\TextEntry::make('created_at')
                        ->label('Submitted')
                        ->since(),
                    \Filament\Infolists\Components\TextEntry::make('resolved_at')
                        ->label('Resolved')
                        ->since()
                        ->default('Not yet resolved'),
                ]),
        ]);
}
```

Add import at the top of `TicketResource.php`:

```php
use Filament\Infolists\Infolist;
```

- [ ] **Step 4: Update ViewTicket to include header actions**

Replace `app/Filament/Resources/TicketResource/Pages/ViewTicket.php`:

```php
<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Resources\TicketResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewTicket extends ViewRecord
{
    protected static string $resource = TicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
```

- [ ] **Step 5: Run test**

```bash
php artisan test --compact --filter=TicketResourceTest
```

Expected: All pass.

- [ ] **Step 6: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Filament/Resources/TicketResource.php \
        app/Filament/Resources/TicketResource/Pages/ViewTicket.php
git commit -m "feat: add ViewTicket infolist with ticket detail layout"
```

---

## Task 6: AssignTicketAction

**Files:**
- Create: `app/Filament/Actions/AssignTicketAction.php`
- Modify: `app/Filament/Resources/TicketResource.php`
- Modify: `app/Filament/Resources/TicketResource/Pages/ViewTicket.php`
- Test: `tests/Feature/Admin/AssignTicketTest.php`

- [ ] **Step 1: Write the failing test**

```bash
php artisan make:test --pest Admin/AssignTicketTest --no-interaction
```

```php
<?php

use App\Filament\Resources\TicketResource\Pages\ViewTicket;
use App\Models\Ticket;
use App\Models\TicketHistory;
use App\Models\User;
use App\Enums\TicketStatus;
use App\Enums\EventType;
use Livewire\Livewire;

test('assign action updates ticket status and creates history', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');
    $staff = User::factory()->create();
    $ticket = Ticket::factory()->create(['status' => TicketStatus::Pending]);

    $this->actingAs($admin);

    Livewire::test(ViewTicket::class, ['record' => $ticket->ulid])
        ->callAction('assign_ticket', data: ['assignee_id' => $staff->id])
        ->assertHasNoActionErrors();

    $ticket->refresh();
    expect($ticket->assigned_to)->toBe($staff->id)
        ->and($ticket->status)->toBe(TicketStatus::Assigned);

    expect(TicketHistory::where('ticket_id', $ticket->id)
        ->where('event_type', EventType::Assigned)
        ->exists())->toBeTrue();
});
```

- [ ] **Step 2: Run to confirm failure**

```bash
php artisan test --compact --filter=AssignTicketTest
```

Expected: FAIL — action not found.

- [ ] **Step 3: Create AssignTicketAction**

Create `app/Filament/Actions/AssignTicketAction.php`:

```php
<?php

namespace App\Filament\Actions;

use App\Enums\EventType;
use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\TicketHistory;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms;
use Illuminate\Support\Facades\DB;

class AssignTicketAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'assign_ticket';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Assign')
            ->icon('heroicon-o-user-plus')
            ->color('info')
            ->form([
                Forms\Components\Select::make('assignee_id')
                    ->label('Assign to')
                    ->options(function () {
                        $user = auth()->user();
                        if ($user->hasRole('super_admin')) {
                            return User::whereHas('roles', fn ($q) => $q->whereIn('name', ['staff', 'office_admin']))
                                ->pluck('name', 'id');
                        }

                        return $user->offices->flatMap->staff->pluck('name', 'id');
                    })
                    ->searchable()
                    ->required(),
            ])
            ->action(function (Ticket $record, array $data): void {
                DB::transaction(function () use ($record, $data): void {
                    $record->update([
                        'assigned_to' => $data['assignee_id'],
                        'status' => TicketStatus::Assigned,
                    ]);

                    TicketHistory::create([
                        'ticket_id' => $record->id,
                        'actor_id' => auth()->id(),
                        'event_type' => EventType::Assigned,
                        'meta' => ['assignee_id' => $data['assignee_id']],
                    ]);
                });
            })
            ->successNotificationTitle('Ticket assigned');
    }
}
```

- [ ] **Step 4: Check TicketHistory migration for `meta` column**

Run:

```bash
php artisan tinker --execute 'Schema::getColumnListing("ticket_histories");'
```

If `meta` column is missing, create a migration:

```bash
php artisan make:migration add_meta_to_ticket_histories_table --no-interaction
```

In the migration:

```php
public function up(): void
{
    Schema::table('ticket_histories', function (Blueprint $table) {
        $table->json('meta')->nullable()->after('event_type');
    });
}

public function down(): void
{
    Schema::table('ticket_histories', function (Blueprint $table) {
        $table->dropColumn('meta');
    });
}
```

Update `TicketHistory` model's `casts()`:

```php
protected function casts(): array
{
    return [
        'event_type' => EventType::class,
        'meta' => 'array',
    ];
}
```

Run: `php artisan migrate --no-interaction`

- [ ] **Step 5: Register action in ViewTicket header and TicketResource table**

In `ViewTicket.php`:

```php
use App\Filament\Actions\AssignTicketAction;

protected function getHeaderActions(): array
{
    return [
        AssignTicketAction::make(),
        Actions\EditAction::make(),
    ];
}
```

In `TicketResource.php` table `->actions([...]`:

```php
->actions([
    Tables\Actions\ViewAction::make(),
    \App\Filament\Actions\AssignTicketAction::make(),
])
```

- [ ] **Step 6: Run tests**

```bash
php artisan test --compact --filter=AssignTicketTest
```

Expected: PASS.

- [ ] **Step 7: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Filament/Actions/AssignTicketAction.php \
        app/Filament/Resources/TicketResource.php \
        app/Filament/Resources/TicketResource/Pages/ViewTicket.php \
        app/Models/TicketHistory.php \
        tests/Feature/Admin/AssignTicketTest.php
git commit -m "feat: add AssignTicketAction with history logging"
```

---

## Task 7: ForwardTicketAction

**Files:**
- Create: `app/Filament/Actions/ForwardTicketAction.php`
- Test: `tests/Feature/Admin/ForwardTicketTest.php`

- [ ] **Step 1: Write the failing test**

```bash
php artisan make:test --pest Admin/ForwardTicketTest --no-interaction
```

```php
<?php

use App\Filament\Resources\TicketResource\Pages\ViewTicket;
use App\Models\ForwardingLog;
use App\Models\Office;
use App\Models\Ticket;
use App\Models\TicketHistory;
use App\Models\User;
use App\Enums\CreditType;
use App\Enums\EventType;
use App\Enums\TicketStatus;
use Livewire\Livewire;

test('forward action creates forwarding log and ticket history', function () {
    $admin = User::factory()->create();
    $admin->assignRole('super_admin');
    $fromOffice = Office::factory()->create();
    $toOffice = Office::factory()->create();
    $ticket = Ticket::factory()->create([
        'office_id' => $fromOffice->id,
        'status' => TicketStatus::Pending,
    ]);

    $this->actingAs($admin);

    Livewire::test(ViewTicket::class, ['record' => $ticket->ulid])
        ->callAction('forward_ticket', data: [
            'to_office_id' => $toOffice->id,
            'credit_type' => CreditType::AcceptCredit->value,
            'note' => 'Please handle this',
        ])
        ->assertHasNoActionErrors();

    $ticket->refresh();
    expect($ticket->status)->toBe(TicketStatus::Forwarded)
        ->and($ticket->office_id)->toBe($toOffice->id);

    expect(ForwardingLog::where('ticket_id', $ticket->id)->exists())->toBeTrue();

    $log = ForwardingLog::where('ticket_id', $ticket->id)->first();
    expect($log->from_office_id)->toBe($fromOffice->id)
        ->and($log->to_office_id)->toBe($toOffice->id)
        ->and($log->credit_type)->toBe(CreditType::AcceptCredit);

    expect(TicketHistory::where('ticket_id', $ticket->id)
        ->where('event_type', EventType::Forwarded)
        ->exists())->toBeTrue();
});
```

- [ ] **Step 2: Run to confirm failure**

```bash
php artisan test --compact --filter=ForwardTicketTest
```

Expected: FAIL.

- [ ] **Step 3: Create ForwardTicketAction**

Create `app/Filament/Actions/ForwardTicketAction.php`:

```php
<?php

namespace App\Filament\Actions;

use App\Enums\CreditType;
use App\Enums\EventType;
use App\Enums\TicketStatus;
use App\Models\ForwardingLog;
use App\Models\Office;
use App\Models\Ticket;
use App\Models\TicketHistory;
use Filament\Actions\Action;
use Filament\Forms;
use Illuminate\Support\Facades\DB;

class ForwardTicketAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'forward_ticket';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Forward')
            ->icon('heroicon-o-arrow-right-circle')
            ->color('warning')
            ->form([
                Forms\Components\Select::make('to_office_id')
                    ->label('Forward to Office')
                    ->options(function (Ticket $record) {
                        return Office::where('is_active', true)
                            ->where('id', '!=', $record->office_id)
                            ->pluck('name', 'id');
                    })
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('credit_type')
                    ->label('Credit Attribution')
                    ->options([
                        CreditType::AcceptCredit->value => 'Accept Credit — both offices receive credit',
                        CreditType::ReferenceOnly->value => 'Reference Only — credit stays with originating office',
                    ])
                    ->required()
                    ->helperText('Determines how this ticket contributes to performance metrics.'),
                Forms\Components\Textarea::make('note')
                    ->label('Forwarding Note')
                    ->placeholder('Reason for forwarding...')
                    ->rows(3),
            ])
            ->action(function (Ticket $record, array $data): void {
                DB::transaction(function () use ($record, $data): void {
                    $fromOfficeId = $record->office_id;

                    ForwardingLog::create([
                        'ticket_id' => $record->id,
                        'from_office_id' => $fromOfficeId,
                        'to_office_id' => $data['to_office_id'],
                        'forwarded_by' => auth()->id(),
                        'credit_type' => $data['credit_type'],
                        'note' => $data['note'] ?? null,
                        'forwarded_at' => now(),
                    ]);

                    $record->update([
                        'office_id' => $data['to_office_id'],
                        'status' => TicketStatus::Forwarded,
                        'assigned_to' => null,
                    ]);

                    TicketHistory::create([
                        'ticket_id' => $record->id,
                        'actor_id' => auth()->id(),
                        'event_type' => EventType::Forwarded,
                        'meta' => [
                            'from_office_id' => $fromOfficeId,
                            'to_office_id' => $data['to_office_id'],
                            'credit_type' => $data['credit_type'],
                        ],
                    ]);
                });
            })
            ->successNotificationTitle('Ticket forwarded');
    }
}
```

- [ ] **Step 4: Register action in ViewTicket**

In `ViewTicket.php`:

```php
use App\Filament\Actions\ForwardTicketAction;

protected function getHeaderActions(): array
{
    return [
        AssignTicketAction::make(),
        ForwardTicketAction::make(),
        Actions\EditAction::make(),
    ];
}
```

Also add to TicketResource table actions:

```php
->actions([
    Tables\Actions\ViewAction::make(),
    \App\Filament\Actions\AssignTicketAction::make(),
    \App\Filament\Actions\ForwardTicketAction::make(),
])
```

- [ ] **Step 5: Run tests**

```bash
php artisan test --compact --filter=ForwardTicketTest
```

Expected: PASS.

- [ ] **Step 6: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Filament/Actions/ForwardTicketAction.php \
        app/Filament/Resources/TicketResource/Pages/ViewTicket.php \
        app/Filament/Resources/TicketResource.php \
        tests/Feature/Admin/ForwardTicketTest.php
git commit -m "feat: add ForwardTicketAction with ForwardingLog and credit attribution"
```

---

## Task 8: Admin Messaging Panel (Livewire embedded in ViewTicket)

**Files:**
- Create: `app/Livewire/Admin/TicketMessaging.php`
- Create: `resources/views/livewire/admin/ticket-messaging.blade.php`
- Modify: `app/Filament/Resources/TicketResource.php` (infolist)
- Test: `tests/Feature/Admin/TicketMessagingTest.php`

- [ ] **Step 1: Write the failing test**

```bash
php artisan make:test --pest Admin/TicketMessagingTest --no-interaction
```

```php
<?php

use App\Livewire\Admin\TicketMessaging;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;
use Livewire\Livewire;

test('admin can send a message to student', function () {
    $admin = User::factory()->create();
    $admin->assignRole('staff');
    $ticket = Ticket::factory()->create();

    $this->actingAs($admin);

    Livewire::test(TicketMessaging::class, ['ticket' => $ticket])
        ->set('body', 'Hello from staff')
        ->set('isInternalNote', false)
        ->call('send')
        ->assertSet('body', '');

    expect(TicketMessage::where('ticket_id', $ticket->id)
        ->where('body', 'Hello from staff')
        ->where('is_internal_note', false)
        ->exists())->toBeTrue();
});

test('admin can send an internal note invisible to student', function () {
    $admin = User::factory()->create();
    $admin->assignRole('staff');
    $ticket = Ticket::factory()->create();

    $this->actingAs($admin);

    Livewire::test(TicketMessaging::class, ['ticket' => $ticket])
        ->set('body', 'Internal staff note')
        ->set('isInternalNote', true)
        ->call('send')
        ->assertHasNoErrors();

    expect(TicketMessage::where('ticket_id', $ticket->id)
        ->where('is_internal_note', true)
        ->exists())->toBeTrue();
});

test('admin can use canned response to prefill message', function () {
    $admin = User::factory()->create();
    $admin->assignRole('staff');
    $ticket = Ticket::factory()->create();

    $this->actingAs($admin);

    Livewire::test(TicketMessaging::class, ['ticket' => $ticket])
        ->call('applyCannedResponse', 'Thank you for contacting us.')
        ->assertSet('body', 'Thank you for contacting us.');
});
```

- [ ] **Step 2: Run to confirm failure**

```bash
php artisan test --compact --filter=TicketMessagingTest
```

Expected: FAIL.

- [ ] **Step 3: Create TicketMessaging Livewire component**

```bash
php artisan make:livewire Admin/TicketMessaging --no-interaction
```

Replace `app/Livewire/Admin/TicketMessaging.php`:

```php
<?php

namespace App\Livewire\Admin;

use App\Models\CannedResponse;
use App\Models\Ticket;
use App\Models\TicketMessage;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

class TicketMessaging extends Component
{
    use WithFileUploads;

    public Ticket $ticket;

    public string $body = '';

    public bool $isInternalNote = false;

    public ?int $selectedCannedResponseId = null;

    #[Computed]
    public function messages(): Collection
    {
        return TicketMessage::with('sender')
            ->where('ticket_id', $this->ticket->id)
            ->where(function ($q) {
                if (! auth()->user()->hasAnyRole(['staff', 'office_admin', 'super_admin'])) {
                    $q->where('is_internal_note', false);
                }
            })
            ->orderBy('created_at')
            ->get();
    }

    #[Computed]
    public function cannedResponses(): Collection
    {
        return CannedResponse::active()
            ->forOffice($this->ticket->office_id)
            ->orderBy('title')
            ->get();
    }

    public function applyCannedResponse(string $body): void
    {
        $this->body = $body;
    }

    public function send(): void
    {
        $this->validate([
            'body' => 'required|string|max:5000',
        ]);

        TicketMessage::create([
            'ticket_id' => $this->ticket->id,
            'sender_id' => auth()->id(),
            'body' => $this->body,
            'is_internal_note' => $this->isInternalNote,
        ]);

        $this->body = '';
        $this->isInternalNote = false;
        unset($this->messages);
    }

    public function render(): View
    {
        return view('livewire.admin.ticket-messaging');
    }
}
```

- [ ] **Step 4: Create the messaging view**

Create `resources/views/livewire/admin/ticket-messaging.blade.php`:

```blade
<div class="space-y-4">
    {{-- Messages list --}}
    <div class="space-y-3 max-h-96 overflow-y-auto pr-2">
        @forelse ($this->messages as $message)
            <div @class([
                'rounded-lg p-3 text-sm',
                'bg-amber-50 border border-amber-200' => $message->is_internal_note,
                'bg-gray-50 border border-gray-200' => ! $message->is_internal_note,
            ])>
                <div class="flex items-center justify-between mb-1">
                    <span class="font-medium text-gray-900">{{ $message->sender->name }}</span>
                    <div class="flex items-center gap-2">
                        @if ($message->is_internal_note)
                            <span class="text-[10px] font-semibold uppercase tracking-wide text-amber-600 bg-amber-100 px-1.5 py-0.5 rounded">Internal</span>
                        @endif
                        <span class="text-xs text-gray-400">{{ $message->created_at->diffForHumans() }}</span>
                    </div>
                </div>
                <p class="text-gray-700 whitespace-pre-wrap">{{ $message->body }}</p>
            </div>
        @empty
            <p class="text-sm text-gray-400 text-center py-4">No messages yet.</p>
        @endforelse
    </div>

    {{-- Canned responses --}}
    @if ($this->cannedResponses->isNotEmpty())
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Canned Responses</label>
            <select wire:change="applyCannedResponse($event.target.value)"
                    class="w-full rounded-md border border-gray-300 text-sm px-3 py-1.5 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                <option value="">— Select a template —</option>
                @foreach ($this->cannedResponses as $canned)
                    <option value="{{ $canned->body }}">{{ $canned->title }}</option>
                @endforeach
            </select>
        </div>
    @endif

    {{-- Compose --}}
    <div class="space-y-2">
        <textarea wire:model="body"
                  rows="3"
                  placeholder="Write a message..."
                  class="w-full rounded-md border border-gray-300 text-sm px-3 py-2 focus:ring-2 focus:ring-sky-500 focus:border-sky-500 resize-none"></textarea>

        @error('body')
            <p class="text-xs text-red-500">{{ $message }}</p>
        @enderror

        <div class="flex items-center justify-between">
            <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer select-none">
                <input type="checkbox" wire:model="isInternalNote"
                       class="rounded border-gray-300 text-amber-500 focus:ring-amber-500" />
                <span>Internal note <span class="text-xs text-gray-400">(staff only)</span></span>
            </label>

            <button wire:click="send"
                    wire:loading.attr="disabled"
                    @class([
                        'px-4 py-1.5 rounded-md text-sm font-semibold text-white transition-colors',
                        'bg-sky-600 hover:bg-sky-700' => ! $isInternalNote,
                        'bg-amber-500 hover:bg-amber-600' => $isInternalNote,
                    ])>
                <span wire:loading.remove>{{ $isInternalNote ? 'Add Note' : 'Send' }}</span>
                <span wire:loading>Sending…</span>
            </button>
        </div>
    </div>
</div>
```

- [ ] **Step 5: Embed TicketMessaging in the infolist**

In `TicketResource.php`'s `infolist()` method, add after the details section:

```php
\Filament\Infolists\Components\Section::make('Messages')
    ->schema([
        \Filament\Infolists\Components\ViewEntry::make('messaging')
            ->label('')
            ->view('livewire.admin.ticket-messaging-wrapper')
            ->state(fn ($record) => $record),
    ]),
```

Create `resources/views/livewire/admin/ticket-messaging-wrapper.blade.php`:

```blade
@livewire('admin.ticket-messaging', ['ticket' => $state])
```

- [ ] **Step 6: Run tests**

```bash
php artisan test --compact --filter=TicketMessagingTest
```

Expected: All 3 PASS.

- [ ] **Step 7: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Livewire/Admin/TicketMessaging.php \
        resources/views/livewire/admin/ticket-messaging.blade.php \
        resources/views/livewire/admin/ticket-messaging-wrapper.blade.php \
        app/Filament/Resources/TicketResource.php \
        tests/Feature/Admin/TicketMessagingTest.php
git commit -m "feat: add admin ticket messaging panel with internal notes and canned responses"
```

---

## Task 9: OfficeResource — CRUD + Staff Relation Manager

**Files:**
- Create: `app/Filament/Resources/OfficeResource.php`
- Create: `app/Filament/Resources/OfficeResource/Pages/ListOffices.php`
- Create: `app/Filament/Resources/OfficeResource/Pages/CreateOffice.php`
- Create: `app/Filament/Resources/OfficeResource/Pages/EditOffice.php`
- Create: `app/Filament/Resources/OfficeResource/RelationManagers/StaffRelationManager.php`
- Test: `tests/Feature/Admin/OfficeResourceTest.php`

- [ ] **Step 1: Write failing tests**

```bash
php artisan make:test --pest Admin/OfficeResourceTest --no-interaction
```

```php
<?php

use App\Filament\Resources\OfficeResource\Pages\CreateOffice;
use App\Filament\Resources\OfficeResource\Pages\EditOffice;
use App\Filament\Resources\OfficeResource\Pages\ListOffices;
use App\Models\Office;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $user = User::factory()->create();
    $user->assignRole('super_admin');
    $this->actingAs($user);
});

test('office resource lists offices', function () {
    Office::factory()->count(3)->create();

    Livewire::test(ListOffices::class)
        ->assertSuccessful()
        ->assertCountTableRecords(3);
});

test('super_admin can create an office', function () {
    Livewire::test(CreateOffice::class)
        ->fillForm([
            'name' => 'Health Services Office',
            'email' => 'health@bicol-u.edu.ph',
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Office::where('name', 'Health Services Office')->exists())->toBeTrue();
});

test('office resource is restricted to super_admin only', function () {
    $staff = User::factory()->create();
    $staff->assignRole('staff');

    $this->actingAs($staff);

    $this->get('/admin/offices')->assertForbidden();
});
```

- [ ] **Step 2: Run to confirm failure**

```bash
php artisan test --compact --filter=OfficeResourceTest
```

- [ ] **Step 3: Generate and implement OfficeResource**

```bash
php artisan make:filament-resource Office --no-interaction
```

Replace `app/Filament/Resources/OfficeResource.php`:

```php
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OfficeResource\Pages;
use App\Filament\Resources\OfficeResource\RelationManagers;
use App\Models\Office;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OfficeResource extends Resource
{
    protected static ?string $model = Office::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?string $navigationGroup = 'Administration';
    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        return auth()->user()->hasRole('super_admin');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('email')
                ->email()
                ->nullable(),
            Forms\Components\Textarea::make('description')
                ->nullable()
                ->rows(3),
            Forms\Components\TextInput::make('sort_order')
                ->numeric()
                ->default(0),
            Forms\Components\Toggle::make('is_active')
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('email'),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
                Tables\Columns\TextColumn::make('staff_count')
                    ->label('Staff')
                    ->counts('staff'),
                Tables\Columns\TextColumn::make('sort_order')->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getRelationManagers(): array
    {
        return [RelationManagers\StaffRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOffices::route('/'),
            'create' => Pages\CreateOffice::route('/create'),
            'edit' => Pages\EditOffice::route('/{record}/edit'),
        ];
    }
}
```

- [ ] **Step 4: Implement page stubs**

`app/Filament/Resources/OfficeResource/Pages/ListOffices.php`:

```php
<?php

namespace App\Filament\Resources\OfficeResource\Pages;

use App\Filament\Resources\OfficeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOffices extends ListRecords
{
    protected static string $resource = OfficeResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
```

`app/Filament/Resources/OfficeResource/Pages/CreateOffice.php`:

```php
<?php

namespace App\Filament\Resources\OfficeResource\Pages;

use App\Filament\Resources\OfficeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOffice extends CreateRecord
{
    protected static string $resource = OfficeResource::class;
}
```

`app/Filament/Resources/OfficeResource/Pages/EditOffice.php`:

```php
<?php

namespace App\Filament\Resources\OfficeResource\Pages;

use App\Filament\Resources\OfficeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOffice extends EditRecord
{
    protected static string $resource = OfficeResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
```

- [ ] **Step 5: Generate and implement StaffRelationManager**

```bash
php artisan make:filament-relation-manager OfficeResource staff name --no-interaction
```

Replace `app/Filament/Resources/OfficeResource/RelationManagers/StaffRelationManager.php`:

```php
<?php

namespace App\Filament\Resources\OfficeResource\RelationManagers;

use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class StaffRelationManager extends RelationManager
{
    protected static string $relationship = 'staff';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Toggle::make('is_primary')
                ->label('Primary office for this staff member'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('email'),
                Tables\Columns\IconColumn::make('pivot.is_primary')
                    ->label('Primary')
                    ->boolean(),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->form(fn (Tables\Actions\AttachAction $action) => [
                        $action->getRecordSelect(),
                        Forms\Components\Toggle::make('is_primary')->label('Primary office'),
                    ])
                    ->preloadRecordSelect(),
            ])
            ->actions([
                Tables\Actions\DetachAction::make(),
            ]);
    }
}
```

- [ ] **Step 6: Run tests**

```bash
php artisan test --compact --filter=OfficeResourceTest
```

Expected: All PASS.

- [ ] **Step 7: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Filament/Resources/OfficeResource.php \
        app/Filament/Resources/OfficeResource/ \
        tests/Feature/Admin/OfficeResourceTest.php
git commit -m "feat: add OfficeResource with staff relation manager"
```

---

## Task 10: ServiceCategoryResource & ServiceTypeResource

**Files:**
- Create: `app/Filament/Resources/ServiceCategoryResource.php`
- Create: `app/Filament/Resources/ServiceCategoryResource/Pages/` (3 pages)
- Create: `app/Filament/Resources/ServiceTypeResource.php`
- Create: `app/Filament/Resources/ServiceTypeResource/Pages/` (3 pages)
- Test: `tests/Feature/Admin/ServiceResourceTest.php`

- [ ] **Step 1: Write failing tests**

```bash
php artisan make:test --pest Admin/ServiceResourceTest --no-interaction
```

```php
<?php

use App\Filament\Resources\ServiceCategoryResource\Pages\CreateServiceCategory;
use App\Filament\Resources\ServiceCategoryResource\Pages\ListServiceCategories;
use App\Filament\Resources\ServiceTypeResource\Pages\CreateServiceType;
use App\Models\Office;
use App\Models\ServiceCategory;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $user = User::factory()->create();
    $user->assignRole('super_admin');
    $this->actingAs($user);
});

test('service category resource lists categories', function () {
    ServiceCategory::factory()->count(3)->create();

    Livewire::test(ListServiceCategories::class)
        ->assertSuccessful()
        ->assertCountTableRecords(3);
});

test('can create a service category', function () {
    $office = Office::factory()->create();

    Livewire::test(CreateServiceCategory::class)
        ->fillForm([
            'office_id' => $office->id,
            'name' => 'Academic Records',
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(ServiceCategory::where('name', 'Academic Records')->exists())->toBeTrue();
});

test('can create a service type with SLA', function () {
    $category = ServiceCategory::factory()->create();

    Livewire::test(CreateServiceType::class)
        ->fillForm([
            'service_category_id' => $category->id,
            'name' => 'Transcript Request',
            'sla_days' => 5,
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();
});
```

- [ ] **Step 2: Run to confirm failure**

```bash
php artisan test --compact --filter=ServiceResourceTest
```

- [ ] **Step 3: Generate and implement ServiceCategoryResource**

```bash
php artisan make:filament-resource ServiceCategory --no-interaction
```

Replace `app/Filament/Resources/ServiceCategoryResource.php`:

```php
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceCategoryResource\Pages;
use App\Models\ServiceCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ServiceCategoryResource extends Resource
{
    protected static ?string $model = ServiceCategory::class;
    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationGroup = 'Service Catalog';
    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        return auth()->user()->hasRole('super_admin');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('office_id')
                ->relationship('office', 'name')
                ->searchable()
                ->preload()
                ->required(),
            Forms\Components\TextInput::make('name')->required()->maxLength(255),
            Forms\Components\Textarea::make('description')->nullable()->rows(2),
            Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
            Forms\Components\Toggle::make('is_active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('office.name')->label('Office')->badge(),
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
                Tables\Columns\TextColumn::make('serviceTypes_count')
                    ->label('Service Types')
                    ->counts('serviceTypes'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('office')->relationship('office', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServiceCategories::route('/'),
            'create' => Pages\CreateServiceCategory::route('/create'),
            'edit' => Pages\EditServiceCategory::route('/{record}/edit'),
        ];
    }
}
```

Create the three page classes (ListServiceCategories, CreateServiceCategory, EditServiceCategory) following the same pattern as OfficeResource pages.

- [ ] **Step 4: Generate and implement ServiceTypeResource**

```bash
php artisan make:filament-resource ServiceType --no-interaction
```

Replace `app/Filament/Resources/ServiceTypeResource.php`:

```php
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceTypeResource\Pages;
use App\Models\ServiceType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ServiceTypeResource extends Resource
{
    protected static ?string $model = ServiceType::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'Service Catalog';
    protected static ?int $navigationSort = 2;

    public static function canAccess(): bool
    {
        return auth()->user()->hasRole('super_admin');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('service_category_id')
                ->relationship('serviceCategory', 'name')
                ->searchable()
                ->preload()
                ->required(),
            Forms\Components\TextInput::make('name')->required()->maxLength(255),
            Forms\Components\Textarea::make('description')->nullable()->rows(2),
            Forms\Components\TextInput::make('sla_days')
                ->label('SLA (days)')
                ->numeric()
                ->minValue(1)
                ->nullable(),
            Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
            Forms\Components\Toggle::make('is_active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('serviceCategory.name')->label('Category')->badge(),
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('sla_days')->label('SLA Days')->default('—'),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServiceTypes::route('/'),
            'create' => Pages\CreateServiceType::route('/create'),
            'edit' => Pages\EditServiceType::route('/{record}/edit'),
        ];
    }
}
```

Create page classes for ServiceTypeResource following the same pattern.

- [ ] **Step 5: Run tests**

```bash
php artisan test --compact --filter=ServiceResourceTest
```

Expected: All PASS.

- [ ] **Step 6: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Filament/Resources/ServiceCategoryResource.php \
        app/Filament/Resources/ServiceCategoryResource/ \
        app/Filament/Resources/ServiceTypeResource.php \
        app/Filament/Resources/ServiceTypeResource/ \
        tests/Feature/Admin/ServiceResourceTest.php
git commit -m "feat: add ServiceCategory and ServiceType resources"
```

---

## Task 11: CannedResponseResource

**Files:**
- Create: `app/Filament/Resources/CannedResponseResource.php`
- Create: `app/Filament/Resources/CannedResponseResource/Pages/` (3 pages)

- [ ] **Step 1: Generate the resource**

```bash
php artisan make:filament-resource CannedResponse --no-interaction
```

Replace `app/Filament/Resources/CannedResponseResource.php`:

```php
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CannedResponseResource\Pages;
use App\Models\CannedResponse;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CannedResponseResource extends Resource
{
    protected static ?string $model = CannedResponse::class;
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    protected static ?string $navigationGroup = 'Administration';
    protected static ?int $navigationSort = 2;

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (auth()->user()->hasAnyRole(['staff', 'office_admin'])) {
            $officeIds = auth()->user()->offices()->pluck('offices.id');
            $query->where(fn ($q) => $q->whereIn('office_id', $officeIds)->orWhereNull('office_id'));
        }

        return $query;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('office_id')
                ->relationship('office', 'name')
                ->searchable()
                ->preload()
                ->nullable()
                ->helperText('Leave empty to make this a global template.'),
            Forms\Components\TextInput::make('title')->required()->maxLength(255),
            Forms\Components\Textarea::make('body')->required()->rows(4),
            Forms\Components\Toggle::make('is_active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable(),
                Tables\Columns\TextColumn::make('office.name')->label('Office')->default('Global')->badge(),
                Tables\Columns\TextColumn::make('body')->limit(60),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCannedResponses::route('/'),
            'create' => Pages\CreateCannedResponse::route('/create'),
            'edit' => Pages\EditCannedResponse::route('/{record}/edit'),
        ];
    }
}
```

Create page classes (ListCannedResponses, CreateCannedResponse, EditCannedResponse) following the same pattern as OfficeResource.

- [ ] **Step 2: Test via the full test suite**

```bash
php artisan test --compact
```

Expected: All existing tests pass.

- [ ] **Step 3: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Filament/Resources/CannedResponseResource.php \
        app/Filament/Resources/CannedResponseResource/
git commit -m "feat: add CannedResponseResource with office-scoped filtering"
```

---

## Task 12: UserResource — Staff Management

**Files:**
- Create: `app/Filament/Resources/UserResource.php`
- Create: `app/Filament/Resources/UserResource/Pages/ListUsers.php`
- Create: `app/Filament/Resources/UserResource/Pages/EditUser.php`

- [ ] **Step 1: Generate the resource**

```bash
php artisan make:filament-resource User --no-interaction
```

Replace `app/Filament/Resources/UserResource.php`:

```php
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Spatie\Permission\Models\Role;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Administration';
    protected static ?int $navigationSort = 3;

    public static function canAccess(): bool
    {
        return auth()->user()->hasRole('super_admin');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->required()->maxLength(255),
            Forms\Components\TextInput::make('email')->email()->required()->unique(ignoreRecord: true),
            Forms\Components\Select::make('roles')
                ->label('Role')
                ->options(Role::pluck('name', 'name'))
                ->multiple()
                ->searchable(),
            Forms\Components\Select::make('offices')
                ->label('Offices')
                ->relationship('offices', 'name')
                ->multiple()
                ->searchable()
                ->preload(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('email')->searchable(),
                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Roles')
                    ->badge(),
                Tables\Columns\TextColumn::make('offices.name')
                    ->label('Offices')
                    ->badge(),
                Tables\Columns\TextColumn::make('created_at')->since()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('roles')
                    ->relationship('roles', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
```

Create `ListUsers` and `EditUser` page classes following the OfficeResource pattern.

- [ ] **Step 2: Run full test suite**

```bash
php artisan test --compact
```

Expected: All pass.

- [ ] **Step 3: Pint + commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Filament/Resources/UserResource.php \
        app/Filament/Resources/UserResource/
git commit -m "feat: add UserResource for staff and role management"
```

---

## Self-Review

**Spec coverage check:**

| Spec Requirement | Task |
|---|---|
| Dashboard with pending/forwarded/resolved overview | Task 3 (TicketStatsOverview) |
| Staff assignment with history | Task 6 (AssignTicketAction) |
| Inter-office forwarding with credit logic | Task 7 (ForwardTicketAction + ForwardingLog) |
| Internal notes toggle | Task 8 (TicketMessaging) |
| Canned responses | Task 8 + Task 11 |
| RBAC restrict staff to their office tickets | Task 4 (getEloquentQuery scope) |
| Office CRUD | Task 9 |
| Staff management with office assignment | Task 12 |
| Service catalog CRUD | Task 10 |
| Ticket history table used | Tasks 6, 7 |
| Forwarding log table used | Task 7 |

**Gaps:** Staff typing indicators (deferred — requires WebSockets, a separate feature). File compression pipeline (deferred — separate feature). These are noted in features.md but are separate system concerns.

**Type consistency:** `EventType::Assigned`, `EventType::Forwarded` match the Enum. `TicketStatus::Assigned`, `TicketStatus::Forwarded` match TicketStatus. `CreditType::AcceptCredit` and `CreditType::ReferenceOnly` match CreditType enum. All method names are consistent across tasks.

**Placeholder scan:** No TBDs. Page stubs for ServiceCategory, ServiceType, CannedResponse, and User follow the exact same pattern as OfficeResource pages — they are brief and complete.
