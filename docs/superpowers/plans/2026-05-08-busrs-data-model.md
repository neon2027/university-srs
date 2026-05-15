# BUSRS Data Model Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implement the complete BUSRS database schema — users (Google OAuth), offices, service catalog, tickets, and support tables — with models, factories, migrations, enums, and seeders.

**Architecture:** Each domain group gets its own migration set and model. Spatie/laravel-permission handles RBAC with four roles. All enum columns use PHP string-backed enums with `string` DB columns for easy schema evolution. Ticket ULIDs are auto-generated in the model boot method.

**Tech Stack:** Laravel 13, PHP 8.4, spatie/laravel-permission, Pest 4

> **Note:** The user handles all git commits. Do NOT run any `git commit` commands.

---

## File Structure

**Enums (create):**
- `app/Enums/TicketStatus.php`
- `app/Enums/TicketPriority.php`
- `app/Enums/FieldType.php`
- `app/Enums/EventType.php`
- `app/Enums/CreditType.php`

**Models (create):** `Office`, `ServiceCategory`, `ServiceType`, `ServiceTypeField`, `Ticket`, `TicketHistory`, `TicketMessage`, `TicketAttachment`, `ForwardingLog`, `CannedResponse`

**Models (modify):** `User`

**Migrations (create):** 1 alter-users + 12 create-table + 1 spatie published

**Factories (create):** One per new model. **Modify:** `UserFactory`

**Seeders (create):** `RoleSeeder`, `OfficeSeeder`. **Modify:** `DatabaseSeeder`

**Tests (create):** `UserModelTest`, `OfficeModelTest`, `ServiceCatalogTest`, `TicketModelTest`, `TicketSupportTablesTest`, `ForwardingLogTest`

---

## Task 1: Install spatie/laravel-permission + seed roles

**Files:**
- Publish: `database/migrations/xxxx_create_permission_tables.php`
- Create: `database/seeders/RoleSeeder.php`
- Modify: `app/Models/User.php` (add `HasRoles`)
- Modify: `database/seeders/DatabaseSeeder.php`
- Test: `tests/Feature/UserModelTest.php`

- [ ] **Step 1: Write the failing test**

```bash
php artisan make:test --pest UserModelTest
```

Replace `tests/Feature/UserModelTest.php`:

```php
<?php

use Spatie\Permission\Models\Role;

test('four roles exist after seeding', function () {
    $this->seed(\Database\Seeders\RoleSeeder::class);

    expect(Role::pluck('name')->sort()->values()->toArray())
        ->toBe(['office_admin', 'staff', 'student', 'super_admin']);
});

test('user can be assigned a role', function () {
    $this->seed(\Database\Seeders\RoleSeeder::class);

    $user = \App\Models\User::factory()->create();
    $user->assignRole('student');

    expect($user->hasRole('student'))->toBeTrue();
});
```

- [ ] **Step 2: Run test to confirm it fails**

```bash
php artisan test --compact --filter=UserModelTest
```

Expected: FAIL (Spatie\Permission not found)

- [ ] **Step 3: Install spatie/laravel-permission**

```bash
composer require spatie/laravel-permission
```

- [ ] **Step 4: Publish and run the spatie migration**

```bash
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider" --no-interaction
php artisan migrate
```

- [ ] **Step 5: Add HasRoles to User model**

In `app/Models/User.php`, add the import and update the trait list:

```php
use Spatie\Permission\Traits\HasRoles;

// In the class body, update the use line:
use HasFactory, Notifiable, TwoFactorAuthenticatable, HasRoles;
```

- [ ] **Step 6: Create RoleSeeder**

```bash
php artisan make:seeder RoleSeeder --no-interaction
```

Replace `database/seeders/RoleSeeder.php`:

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['student', 'staff', 'office_admin', 'super_admin'] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }
    }
}
```

- [ ] **Step 7: Register in DatabaseSeeder**

Replace `database/seeders/DatabaseSeeder.php`:

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
        ]);
    }
}
```

- [ ] **Step 8: Run tests to verify they pass**

```bash
php artisan test --compact --filter=UserModelTest
```

Expected: PASS (2 tests)

- [ ] **Step 9: Run pint**

```bash
vendor/bin/pint --dirty --format agent
```

---

## Task 2: Modify users table for Google OAuth

**Files:**
- Create: `database/migrations/xxxx_modify_users_table_for_google_oauth.php`
- Modify: `app/Models/User.php`
- Modify: `database/factories/UserFactory.php`

- [ ] **Step 1: Add tests to UserModelTest**

Append to `tests/Feature/UserModelTest.php`:

```php
test('user has google_id and avatar columns', function () {
    $user = \App\Models\User::factory()->create([
        'google_id' => 'google-123',
        'avatar' => 'https://example.com/avatar.jpg',
    ]);

    expect($user->google_id)->toBe('google-123');
    expect($user->avatar)->toBe('https://example.com/avatar.jpg');
});

test('user factory produces a user without password', function () {
    $user = \App\Models\User::factory()->create();

    expect(array_key_exists('password', $user->getAttributes()))->toBeFalse();
});

test('user has initials helper', function () {
    $user = \App\Models\User::factory()->create(['name' => 'Juan Dela Cruz']);

    expect($user->initials())->toBe('JD');
});
```

- [ ] **Step 2: Run tests to confirm new ones fail**

```bash
php artisan test --compact --filter=UserModelTest
```

Expected: 2 pass, 3 fail (google_id column missing)

- [ ] **Step 3: Create the migration**

```bash
php artisan make:migration modify_users_table_for_google_oauth --no-interaction
```

Replace the generated file body with:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'password',
                'two_factor_secret',
                'two_factor_recovery_codes',
                'two_factor_confirmed_at',
            ]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('google_id')->unique()->nullable()->after('email');
            $table->string('avatar')->nullable()->after('google_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['google_id', 'avatar']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('password')->after('email');
            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->timestamp('two_factor_confirmed_at')->nullable();
        });
    }
};
```

- [ ] **Step 4: Run migration**

```bash
php artisan migrate
```

- [ ] **Step 5: Replace User model**

Replace `app/Models/User.php`:

```php
<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'google_id', 'avatar'])]
#[Hidden(['remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
        ];
    }

    public function offices(): BelongsToMany
    {
        return $this->belongsToMany(Office::class)
            ->withPivot('is_primary')
            ->withTimestamps();
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

- [ ] **Step 6: Replace UserFactory**

Replace `database/factories/UserFactory.php`:

```php
<?php

namespace Database\Factories;

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
}
```

- [ ] **Step 7: Run all tests to verify they pass**

```bash
php artisan test --compact --filter=UserModelTest
```

Expected: PASS (5 tests)

- [ ] **Step 8: Run pint**

```bash
vendor/bin/pint --dirty --format agent
```

---

## Task 3: Offices + office_user pivot

**Files:**
- Create: `database/migrations/xxxx_create_offices_table.php`
- Create: `database/migrations/xxxx_create_office_user_table.php`
- Create: `app/Models/Office.php`
- Create: `database/factories/OfficeFactory.php`
- Create: `database/seeders/OfficeSeeder.php`
- Test: `tests/Feature/OfficeModelTest.php`

- [ ] **Step 1: Write the failing test**

```bash
php artisan make:test --pest OfficeModelTest
```

Replace `tests/Feature/OfficeModelTest.php`:

```php
<?php

use App\Models\Office;
use App\Models\User;

test('office has many staff users', function () {
    $office = Office::factory()->create();
    $staff = User::factory()->count(3)->create();

    $office->staff()->attach($staff->pluck('id'), ['is_primary' => false]);

    expect($office->staff)->toHaveCount(3);
});

test('user can belong to multiple offices', function () {
    $user = User::factory()->create();
    $offices = Office::factory()->count(2)->create();

    $offices->each(fn ($o) => $o->staff()->attach($user->id, ['is_primary' => false]));

    expect($user->offices)->toHaveCount(2);
});

test('user has a primary office', function () {
    $user = User::factory()->create();
    $primary = Office::factory()->create();
    $secondary = Office::factory()->create();

    $primary->staff()->attach($user->id, ['is_primary' => true]);
    $secondary->staff()->attach($user->id, ['is_primary' => false]);

    expect($user->primaryOffice()->id)->toBe($primary->id);
});

test('office active scope filters inactive offices', function () {
    Office::factory()->count(2)->create(['is_active' => true]);
    Office::factory()->create(['is_active' => false]);

    expect(Office::active()->count())->toBe(2);
});

test('office slug is auto-generated from name', function () {
    $office = Office::factory()->create(['name' => 'Information Technology Office', 'slug' => null]);

    expect($office->slug)->toBe('information-technology-office');
});
```

- [ ] **Step 2: Run tests to confirm they fail**

```bash
php artisan test --compact --filter=OfficeModelTest
```

Expected: FAIL (Office class not found)

- [ ] **Step 3: Create Office model with migration and factory**

```bash
php artisan make:model Office --migration --factory --no-interaction
```

- [ ] **Step 4: Fill in offices migration**

Replace the generated `database/migrations/xxxx_create_offices_table.php` body:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offices', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('email')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offices');
    }
};
```

- [ ] **Step 5: Create office_user pivot migration**

```bash
php artisan make:migration create_office_user_table --no-interaction
```

Replace the generated body:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('office_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('office_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
            $table->unique(['office_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('office_user');
    }
};
```

- [ ] **Step 6: Run migration**

```bash
php artisan migrate
```

- [ ] **Step 7: Fill in Office model**

Replace `app/Models/Office.php`:

```php
<?php

namespace App\Models;

use Database\Factories\OfficeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable(['name', 'slug', 'description', 'email', 'is_active', 'sort_order'])]
class Office extends Model
{
    /** @use HasFactory<OfficeFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (Office $office) {
            $office->slug ??= Str::slug($office->name);
        });
    }

    public function staff(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    public function serviceCategories(): HasMany
    {
        return $this->hasMany(ServiceCategory::class);
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }
}
```

- [ ] **Step 8: Fill in OfficeFactory**

Replace `database/factories/OfficeFactory.php`:

```php
<?php

namespace Database\Factories;

use App\Models\Office;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Office>
 */
class OfficeFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->company() . ' Office';

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
            'email' => fake()->companyEmail(),
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => ['is_active' => false]);
    }
}
```

- [ ] **Step 9: Create OfficeSeeder**

```bash
php artisan make:seeder OfficeSeeder --no-interaction
```

Replace `database/seeders/OfficeSeeder.php`:

```php
<?php

namespace Database\Seeders;

use App\Models\Office;
use Illuminate\Database\Seeder;

class OfficeSeeder extends Seeder
{
    public function run(): void
    {
        $offices = [
            ['name' => 'Information Technology Office', 'email' => 'ito@bicol-u.edu.ph'],
            ['name' => 'Physical Plant Office', 'email' => 'ppo@bicol-u.edu.ph'],
            ['name' => 'Registrar Office', 'email' => 'registrar@bicol-u.edu.ph'],
            ['name' => 'Student Affairs Office', 'email' => 'sao@bicol-u.edu.ph'],
            ['name' => 'Finance Office', 'email' => 'finance@bicol-u.edu.ph'],
        ];

        foreach ($offices as $data) {
            Office::firstOrCreate(['name' => $data['name']], $data);
        }
    }
}
```

- [ ] **Step 10: Run tests to verify they pass**

```bash
php artisan test --compact --filter=OfficeModelTest
```

Expected: PASS (5 tests)

- [ ] **Step 11: Run pint**

```bash
vendor/bin/pint --dirty --format agent
```

---

## Task 4: Service catalog (FieldType enum + 3 tables)

**Files:**
- Create: `app/Enums/FieldType.php`
- Create: `app/Models/ServiceCategory.php`, `ServiceType.php`, `ServiceTypeField.php`
- Create: migrations and factories for all three
- Test: `tests/Feature/ServiceCatalogTest.php`

- [ ] **Step 1: Write the failing test**

```bash
php artisan make:test --pest ServiceCatalogTest
```

Replace `tests/Feature/ServiceCatalogTest.php`:

```php
<?php

use App\Enums\FieldType;
use App\Models\Office;
use App\Models\ServiceCategory;
use App\Models\ServiceType;
use App\Models\ServiceTypeField;

test('service category belongs to an office', function () {
    $office = Office::factory()->create();
    $category = ServiceCategory::factory()->for($office)->create();

    expect($category->office->id)->toBe($office->id);
});

test('service type belongs to a category', function () {
    $category = ServiceCategory::factory()->create();
    $type = ServiceType::factory()->for($category)->create();

    expect($type->serviceCategory->id)->toBe($category->id);
});

test('service type has dynamic fields ordered by sort_order', function () {
    $type = ServiceType::factory()->create();
    ServiceTypeField::factory()->count(3)->for($type)->create();

    expect($type->fields)->toHaveCount(3);
});

test('field type enum casts correctly', function () {
    $field = ServiceTypeField::factory()->create(['field_type' => FieldType::Text]);

    expect($field->field_type)->toBe(FieldType::Text);
    expect($field->field_type->value)->toBe('text');
});

test('select field stores options as array', function () {
    $field = ServiceTypeField::factory()->create([
        'field_type' => FieldType::Select,
        'options' => ['Option A', 'Option B', 'Option C'],
    ]);

    expect($field->options)->toHaveCount(3);
    expect($field->options[0])->toBe('Option A');
});
```

- [ ] **Step 2: Run to confirm failure**

```bash
php artisan test --compact --filter=ServiceCatalogTest
```

Expected: FAIL (classes not found)

- [ ] **Step 3: Create FieldType enum**

```bash
php artisan make:enum Enums/FieldType --no-interaction
```

Replace `app/Enums/FieldType.php`:

```php
<?php

namespace App\Enums;

enum FieldType: string
{
    case Text = 'text';
    case Textarea = 'textarea';
    case Select = 'select';
    case Checkbox = 'checkbox';
    case File = 'file';
    case Date = 'date';
}
```

- [ ] **Step 4: Create models with migrations and factories**

```bash
php artisan make:model ServiceCategory --migration --factory --no-interaction
php artisan make:model ServiceType --migration --factory --no-interaction
php artisan make:model ServiceTypeField --migration --factory --no-interaction
```

- [ ] **Step 5: Fill in service_categories migration**

Replace the body of `database/migrations/xxxx_create_service_categories_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('office_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_categories');
    }
};
```

- [ ] **Step 6: Fill in service_types migration**

Replace the body of `database/migrations/xxxx_create_service_types_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_category_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->unsignedInteger('sla_days')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_types');
    }
};
```

- [ ] **Step 7: Fill in service_type_fields migration**

Replace the body of `database/migrations/xxxx_create_service_type_fields_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_type_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_type_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->string('field_type');
            $table->json('options')->nullable();
            $table->boolean('is_required')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_type_fields');
    }
};
```

- [ ] **Step 8: Run migration**

```bash
php artisan migrate
```

- [ ] **Step 9: Fill in ServiceCategory model**

Replace `app/Models/ServiceCategory.php`:

```php
<?php

namespace App\Models;

use Database\Factories\ServiceCategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable(['office_id', 'name', 'slug', 'description', 'is_active', 'sort_order'])]
class ServiceCategory extends Model
{
    /** @use HasFactory<ServiceCategoryFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (ServiceCategory $category) {
            $category->slug ??= Str::slug($category->name);
        });
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    public function serviceTypes(): HasMany
    {
        return $this->hasMany(ServiceType::class)->orderBy('sort_order');
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }
}
```

- [ ] **Step 10: Fill in ServiceType model**

Replace `app/Models/ServiceType.php`:

```php
<?php

namespace App\Models;

use Database\Factories\ServiceTypeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable(['service_category_id', 'name', 'slug', 'description', 'sla_days', 'is_active', 'sort_order'])]
class ServiceType extends Model
{
    /** @use HasFactory<ServiceTypeFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (ServiceType $type) {
            $type->slug ??= Str::slug($type->name);
        });
    }

    public function serviceCategory(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class);
    }

    public function fields(): HasMany
    {
        return $this->hasMany(ServiceTypeField::class)->orderBy('sort_order');
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }
}
```

- [ ] **Step 11: Fill in ServiceTypeField model**

Replace `app/Models/ServiceTypeField.php`:

```php
<?php

namespace App\Models;

use App\Enums\FieldType;
use Database\Factories\ServiceTypeFieldFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['service_type_id', 'label', 'field_type', 'options', 'is_required', 'sort_order'])]
class ServiceTypeField extends Model
{
    /** @use HasFactory<ServiceTypeFieldFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'field_type' => FieldType::class,
            'options' => 'array',
            'is_required' => 'boolean',
        ];
    }

    public function serviceType(): BelongsTo
    {
        return $this->belongsTo(ServiceType::class);
    }
}
```

- [ ] **Step 12: Fill in ServiceCategoryFactory**

Replace `database/factories/ServiceCategoryFactory.php`:

```php
<?php

namespace Database\Factories;

use App\Models\Office;
use App\Models\ServiceCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ServiceCategory>
 */
class ServiceCategoryFactory extends Factory
{
    public function definition(): array
    {
        $name = ucwords(fake()->unique()->words(3, true));

        return [
            'office_id' => Office::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
```

- [ ] **Step 13: Fill in ServiceTypeFactory**

Replace `database/factories/ServiceTypeFactory.php`:

```php
<?php

namespace Database\Factories;

use App\Models\ServiceCategory;
use App\Models\ServiceType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ServiceType>
 */
class ServiceTypeFactory extends Factory
{
    public function definition(): array
    {
        $name = ucwords(fake()->unique()->words(4, true));

        return [
            'service_category_id' => ServiceCategory::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
            'sla_days' => fake()->optional()->numberBetween(1, 30),
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
```

- [ ] **Step 14: Fill in ServiceTypeFieldFactory**

Replace `database/factories/ServiceTypeFieldFactory.php`:

```php
<?php

namespace Database\Factories;

use App\Enums\FieldType;
use App\Models\ServiceType;
use App\Models\ServiceTypeField;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceTypeField>
 */
class ServiceTypeFieldFactory extends Factory
{
    public function definition(): array
    {
        return [
            'service_type_id' => ServiceType::factory(),
            'label' => ucwords(fake()->words(3, true)),
            'field_type' => fake()->randomElement(FieldType::cases()),
            'options' => null,
            'is_required' => fake()->boolean(),
            'sort_order' => fake()->numberBetween(0, 10),
        ];
    }
}
```

- [ ] **Step 15: Run tests to verify they pass**

```bash
php artisan test --compact --filter=ServiceCatalogTest
```

Expected: PASS (5 tests)

- [ ] **Step 16: Run pint**

```bash
vendor/bin/pint --dirty --format agent
```

---

## Task 5: Tickets (enums + tickets table)

**Files:**
- Create: `app/Enums/TicketStatus.php`, `app/Enums/TicketPriority.php`
- Create: `app/Models/Ticket.php`, migration, factory
- Test: `tests/Feature/TicketModelTest.php`

- [ ] **Step 1: Write the failing test**

```bash
php artisan make:test --pest TicketModelTest
```

Replace `tests/Feature/TicketModelTest.php`:

```php
<?php

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\Office;
use App\Models\ServiceType;
use App\Models\Ticket;
use App\Models\User;

test('ticket has a ulid generated on creation', function () {
    $ticket = Ticket::factory()->create();

    expect($ticket->ulid)->not->toBeNull();
    expect(strlen($ticket->ulid))->toBe(26);
});

test('ticket belongs to requester, office, and service type', function () {
    $user = User::factory()->create();
    $office = Office::factory()->create();
    $type = ServiceType::factory()->create();

    $ticket = Ticket::factory()->create([
        'requester_id' => $user->id,
        'office_id' => $office->id,
        'service_type_id' => $type->id,
    ]);

    expect($ticket->requester->id)->toBe($user->id);
    expect($ticket->office->id)->toBe($office->id);
    expect($ticket->serviceType->id)->toBe($type->id);
});

test('ticket status defaults to pending', function () {
    $ticket = Ticket::factory()->create();

    expect($ticket->status)->toBe(TicketStatus::Pending);
});

test('ticket priority defaults to normal', function () {
    $ticket = Ticket::factory()->create();

    expect($ticket->priority)->toBe(TicketPriority::Normal);
});

test('ticket stores custom fields as array', function () {
    $ticket = Ticket::factory()->create([
        'custom_fields' => ['1' => 'BU-2024-001', '2' => 'Second semester'],
    ]);

    expect($ticket->custom_fields['1'])->toBe('BU-2024-001');
});
```

- [ ] **Step 2: Run to confirm failure**

```bash
php artisan test --compact --filter=TicketModelTest
```

Expected: FAIL

- [ ] **Step 3: Create enums**

```bash
php artisan make:enum Enums/TicketStatus --no-interaction
php artisan make:enum Enums/TicketPriority --no-interaction
```

Replace `app/Enums/TicketStatus.php`:

```php
<?php

namespace App\Enums;

enum TicketStatus: string
{
    case Pending = 'pending';
    case Assigned = 'assigned';
    case InProgress = 'in_progress';
    case OnHold = 'on_hold';
    case Forwarded = 'forwarded';
    case Resolved = 'resolved';
    case Closed = 'closed';
    case Cancelled = 'cancelled';
}
```

Replace `app/Enums/TicketPriority.php`:

```php
<?php

namespace App\Enums;

enum TicketPriority: string
{
    case Low = 'low';
    case Normal = 'normal';
    case High = 'high';
    case Urgent = 'urgent';
}
```

- [ ] **Step 4: Create Ticket model with migration and factory**

```bash
php artisan make:model Ticket --migration --factory --no-interaction
```

- [ ] **Step 5: Fill in tickets migration**

Replace the body of `database/migrations/xxxx_create_tickets_table.php`:

```php
<?php

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ulid', 26)->unique();
            $table->foreignId('requester_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('office_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default(TicketStatus::Pending->value);
            $table->string('priority')->default(TicketPriority::Normal->value);
            $table->string('subject');
            $table->text('description');
            $table->json('custom_fields')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
```

- [ ] **Step 6: Run migration**

```bash
php artisan migrate
```

- [ ] **Step 7: Fill in Ticket model**

Replace `app/Models/Ticket.php`:

```php
<?php

namespace App\Models;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use Database\Factories\TicketFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable([
    'ulid', 'requester_id', 'office_id', 'service_type_id', 'assigned_to',
    'status', 'priority', 'subject', 'description', 'custom_fields',
    'resolved_at', 'closed_at',
])]
class Ticket extends Model
{
    /** @use HasFactory<TicketFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'status' => TicketStatus::class,
            'priority' => TicketPriority::class,
            'custom_fields' => 'array',
            'resolved_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (Ticket $ticket) {
            $ticket->ulid ??= (string) Str::ulid();
        });
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    public function serviceType(): BelongsTo
    {
        return $this->belongsTo(ServiceType::class);
    }

    public function history(): HasMany
    {
        return $this->hasMany(TicketHistory::class)->orderBy('created_at');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(TicketMessage::class)->orderBy('created_at');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TicketAttachment::class);
    }
}
```

- [ ] **Step 8: Fill in TicketFactory**

Replace `database/factories/TicketFactory.php`:

```php
<?php

namespace Database\Factories;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\Office;
use App\Models\ServiceType;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    public function definition(): array
    {
        return [
            'ulid' => (string) Str::ulid(),
            'requester_id' => User::factory(),
            'office_id' => Office::factory(),
            'service_type_id' => ServiceType::factory(),
            'assigned_to' => null,
            'status' => TicketStatus::Pending,
            'priority' => TicketPriority::Normal,
            'subject' => fake()->sentence(6),
            'description' => fake()->paragraph(),
            'custom_fields' => null,
        ];
    }

    public function assigned(User $assignee = null): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TicketStatus::Assigned,
            'assigned_to' => $assignee?->id ?? User::factory(),
        ]);
    }

    public function resolved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TicketStatus::Resolved,
            'resolved_at' => now(),
        ]);
    }
}
```

- [ ] **Step 9: Run tests to verify they pass**

```bash
php artisan test --compact --filter=TicketModelTest
```

Expected: PASS (5 tests)

- [ ] **Step 10: Run pint**

```bash
vendor/bin/pint --dirty --format agent
```

---

## Task 6: Ticket history, messages, and attachments

**Files:**
- Create: `app/Enums/EventType.php`
- Create: `app/Models/TicketHistory.php`, `TicketMessage.php`, `TicketAttachment.php`
- Create: migrations and factories for all three
- Test: `tests/Feature/TicketSupportTablesTest.php`

- [ ] **Step 1: Write the failing test**

```bash
php artisan make:test --pest TicketSupportTablesTest
```

Replace `tests/Feature/TicketSupportTablesTest.php`:

```php
<?php

use App\Enums\EventType;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\TicketHistory;
use App\Models\TicketMessage;
use App\Models\User;

test('ticket history records a status change event', function () {
    $ticket = Ticket::factory()->create();
    $actor = User::factory()->create();

    TicketHistory::factory()->create([
        'ticket_id' => $ticket->id,
        'actor_id' => $actor->id,
        'event_type' => EventType::StatusChanged,
        'from_status' => 'pending',
        'to_status' => 'assigned',
    ]);

    $history = $ticket->history->first();
    expect($history->event_type)->toBe(EventType::StatusChanged);
    expect($history->to_status)->toBe('assigned');
});

test('ticket message can be an internal note', function () {
    $ticket = Ticket::factory()->create();

    TicketMessage::factory()->create([
        'ticket_id' => $ticket->id,
        'is_internal_note' => true,
    ]);

    expect($ticket->messages->first()->is_internal_note)->toBeTrue();
});

test('ticket message tracks seen_at timestamp', function () {
    $message = TicketMessage::factory()->create(['seen_at' => null]);

    expect($message->seen_at)->toBeNull();

    $message->update(['seen_at' => now()]);

    expect($message->fresh()->seen_at)->not->toBeNull();
});

test('ticket attachment tracks original and compressed sizes', function () {
    $ticket = Ticket::factory()->create();
    $attachment = TicketAttachment::factory()->create([
        'ticket_id' => $ticket->id,
        'size_bytes' => 5_000_000,
        'compressed_size_bytes' => 1_200_000,
    ]);

    expect($attachment->size_bytes)->toBe(5_000_000);
    expect($attachment->compressed_size_bytes)->toBe(1_200_000);
    expect($ticket->attachments)->toHaveCount(1);
});

test('ticket attachment can belong to a message', function () {
    $message = TicketMessage::factory()->create();
    $attachment = TicketAttachment::factory()->create([
        'ticket_id' => $message->ticket_id,
        'ticket_message_id' => $message->id,
    ]);

    expect($attachment->message->id)->toBe($message->id);
});
```

- [ ] **Step 2: Run to confirm failure**

```bash
php artisan test --compact --filter=TicketSupportTablesTest
```

Expected: FAIL

- [ ] **Step 3: Create EventType enum**

```bash
php artisan make:enum Enums/EventType --no-interaction
```

Replace `app/Enums/EventType.php`:

```php
<?php

namespace App\Enums;

enum EventType: string
{
    case Created = 'created';
    case StatusChanged = 'status_changed';
    case Assigned = 'assigned';
    case Forwarded = 'forwarded';
    case NoteAdded = 'note_added';
    case Resolved = 'resolved';
    case Closed = 'closed';
}
```

- [ ] **Step 4: Create models with migrations and factories**

```bash
php artisan make:model TicketHistory --migration --factory --no-interaction
php artisan make:model TicketMessage --migration --factory --no-interaction
php artisan make:model TicketAttachment --migration --factory --no-interaction
```

- [ ] **Step 5: Fill in ticket_history migration**

Replace `database/migrations/xxxx_create_ticket_history_table.php` body:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('actor_id')->constrained('users')->cascadeOnDelete();
            $table->string('event_type');
            $table->string('from_status')->nullable();
            $table->string('to_status')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_history');
    }
};
```

- [ ] **Step 6: Fill in ticket_messages migration**

Replace `database/migrations/xxxx_create_ticket_messages_table.php` body:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->text('body');
            $table->boolean('is_internal_note')->default(false);
            $table->boolean('is_canned_response')->default(false);
            $table->timestamp('seen_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_messages');
    }
};
```

- [ ] **Step 7: Fill in ticket_attachments migration**

Replace `database/migrations/xxxx_create_ticket_attachments_table.php` body:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ticket_message_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('uploader_id')->constrained('users')->cascadeOnDelete();
            $table->string('disk')->default('local');
            $table->string('path');
            $table->string('original_filename');
            $table->string('mime_type');
            $table->unsignedBigInteger('size_bytes');
            $table->unsignedBigInteger('compressed_size_bytes');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_attachments');
    }
};
```

- [ ] **Step 8: Run migration**

```bash
php artisan migrate
```

- [ ] **Step 9: Fill in TicketHistory model**

Replace `app/Models/TicketHistory.php`:

```php
<?php

namespace App\Models;

use App\Enums\EventType;
use Database\Factories\TicketHistoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['ticket_id', 'actor_id', 'event_type', 'from_status', 'to_status', 'note'])]
class TicketHistory extends Model
{
    /** @use HasFactory<TicketHistoryFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return ['event_type' => EventType::class];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
```

- [ ] **Step 10: Fill in TicketMessage model**

Replace `app/Models/TicketMessage.php`:

```php
<?php

namespace App\Models;

use Database\Factories\TicketMessageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['ticket_id', 'sender_id', 'body', 'is_internal_note', 'is_canned_response', 'seen_at'])]
class TicketMessage extends Model
{
    /** @use HasFactory<TicketMessageFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'is_internal_note' => 'boolean',
            'is_canned_response' => 'boolean',
            'seen_at' => 'datetime',
        ];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TicketAttachment::class);
    }
}
```

- [ ] **Step 11: Fill in TicketAttachment model**

Replace `app/Models/TicketAttachment.php`:

```php
<?php

namespace App\Models;

use Database\Factories\TicketAttachmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'ticket_id', 'ticket_message_id', 'uploader_id',
    'disk', 'path', 'original_filename', 'mime_type',
    'size_bytes', 'compressed_size_bytes',
])]
class TicketAttachment extends Model
{
    /** @use HasFactory<TicketAttachmentFactory> */
    use HasFactory;

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function message(): BelongsTo
    {
        return $this->belongsTo(TicketMessage::class, 'ticket_message_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploader_id');
    }
}
```

- [ ] **Step 12: Fill in TicketHistoryFactory**

Replace `database/factories/TicketHistoryFactory.php`:

```php
<?php

namespace Database\Factories;

use App\Enums\EventType;
use App\Models\Ticket;
use App\Models\TicketHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TicketHistory>
 */
class TicketHistoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'ticket_id' => Ticket::factory(),
            'actor_id' => User::factory(),
            'event_type' => EventType::Created,
            'from_status' => null,
            'to_status' => null,
            'note' => null,
        ];
    }
}
```

- [ ] **Step 13: Fill in TicketMessageFactory**

Replace `database/factories/TicketMessageFactory.php`:

```php
<?php

namespace Database\Factories;

use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TicketMessage>
 */
class TicketMessageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'ticket_id' => Ticket::factory(),
            'sender_id' => User::factory(),
            'body' => fake()->paragraph(),
            'is_internal_note' => false,
            'is_canned_response' => false,
            'seen_at' => null,
        ];
    }

    public function internalNote(): static
    {
        return $this->state(fn (array $attributes) => ['is_internal_note' => true]);
    }
}
```

- [ ] **Step 14: Fill in TicketAttachmentFactory**

Replace `database/factories/TicketAttachmentFactory.php`:

```php
<?php

namespace Database\Factories;

use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TicketAttachment>
 */
class TicketAttachmentFactory extends Factory
{
    public function definition(): array
    {
        $size = fake()->numberBetween(100_000, 10_000_000);

        return [
            'ticket_id' => Ticket::factory(),
            'ticket_message_id' => null,
            'uploader_id' => User::factory(),
            'disk' => 'local',
            'path' => 'attachments/' . fake()->uuid() . '.pdf',
            'original_filename' => fake()->word() . '.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => $size,
            'compressed_size_bytes' => (int) ($size * 0.4),
        ];
    }
}
```

- [ ] **Step 15: Run tests to verify they pass**

```bash
php artisan test --compact --filter=TicketSupportTablesTest
```

Expected: PASS (5 tests)

- [ ] **Step 16: Run pint**

```bash
vendor/bin/pint --dirty --format agent
```

---

## Task 7: Forwarding logs + canned responses

**Files:**
- Create: `app/Enums/CreditType.php`
- Create: `app/Models/ForwardingLog.php`, `CannedResponse.php`
- Create: migrations and factories for both
- Test: `tests/Feature/ForwardingLogTest.php`

- [ ] **Step 1: Write the failing test**

```bash
php artisan make:test --pest ForwardingLogTest
```

Replace `tests/Feature/ForwardingLogTest.php`:

```php
<?php

use App\Enums\CreditType;
use App\Models\CannedResponse;
use App\Models\ForwardingLog;
use App\Models\Office;
use App\Models\Ticket;
use App\Models\User;

test('forwarding log is created with null credit type pending acceptance', function () {
    $log = ForwardingLog::factory()->create(['credit_type' => null]);

    expect($log->credit_type)->toBeNull();
    expect($log->responded_at)->toBeNull();
});

test('forwarding log can accept credit', function () {
    $acceptor = User::factory()->create();
    $log = ForwardingLog::factory()->create(['credit_type' => null]);

    $log->update([
        'credit_type' => CreditType::AcceptCredit,
        'accepted_by' => $acceptor->id,
        'responded_at' => now(),
    ]);

    expect($log->fresh()->credit_type)->toBe(CreditType::AcceptCredit);
});

test('forwarding log can be reference only', function () {
    $log = ForwardingLog::factory()->create([
        'credit_type' => CreditType::ReferenceOnly,
        'responded_at' => now(),
    ]);

    expect($log->credit_type)->toBe(CreditType::ReferenceOnly);
});

test('forwarding log belongs to from and to offices', function () {
    $from = Office::factory()->create();
    $to = Office::factory()->create();
    $ticket = Ticket::factory()->create(['office_id' => $from->id]);

    $log = ForwardingLog::factory()->create([
        'ticket_id' => $ticket->id,
        'from_office_id' => $from->id,
        'to_office_id' => $to->id,
    ]);

    expect($log->fromOffice->id)->toBe($from->id);
    expect($log->toOffice->id)->toBe($to->id);
});

test('office-scoped canned response is separate from system-wide', function () {
    $office = Office::factory()->create();
    $staff = User::factory()->create();

    CannedResponse::factory()->create(['office_id' => $office->id, 'created_by' => $staff->id]);
    CannedResponse::factory()->create(['office_id' => null, 'created_by' => $staff->id]);

    expect(CannedResponse::where('office_id', $office->id)->count())->toBe(1);
    expect(CannedResponse::whereNull('office_id')->count())->toBe(1);
});
```

- [ ] **Step 2: Run to confirm failure**

```bash
php artisan test --compact --filter=ForwardingLogTest
```

Expected: FAIL

- [ ] **Step 3: Create CreditType enum**

```bash
php artisan make:enum Enums/CreditType --no-interaction
```

Replace `app/Enums/CreditType.php`:

```php
<?php

namespace App\Enums;

enum CreditType: string
{
    case AcceptCredit = 'accept_credit';
    case ReferenceOnly = 'reference_only';
}
```

- [ ] **Step 4: Create models with migrations and factories**

```bash
php artisan make:model ForwardingLog --migration --factory --no-interaction
php artisan make:model CannedResponse --migration --factory --no-interaction
```

- [ ] **Step 5: Fill in forwarding_logs migration**

Replace `database/migrations/xxxx_create_forwarding_logs_table.php` body:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forwarding_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('from_office_id')->constrained('offices')->cascadeOnDelete();
            $table->foreignId('to_office_id')->constrained('offices')->cascadeOnDelete();
            $table->foreignId('forwarded_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('accepted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('credit_type')->nullable();
            $table->text('note')->nullable();
            $table->timestamp('forwarded_at')->useCurrent();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forwarding_logs');
    }
};
```

- [ ] **Step 6: Fill in canned_responses migration**

Replace `database/migrations/xxxx_create_canned_responses_table.php` body:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('canned_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('office_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('body');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('canned_responses');
    }
};
```

- [ ] **Step 7: Run migration**

```bash
php artisan migrate
```

- [ ] **Step 8: Fill in ForwardingLog model**

Replace `app/Models/ForwardingLog.php`:

```php
<?php

namespace App\Models;

use App\Enums\CreditType;
use Database\Factories\ForwardingLogFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'ticket_id', 'from_office_id', 'to_office_id',
    'forwarded_by', 'accepted_by', 'credit_type',
    'note', 'forwarded_at', 'responded_at',
])]
class ForwardingLog extends Model
{
    /** @use HasFactory<ForwardingLogFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'credit_type' => CreditType::class,
            'forwarded_at' => 'datetime',
            'responded_at' => 'datetime',
        ];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function fromOffice(): BelongsTo
    {
        return $this->belongsTo(Office::class, 'from_office_id');
    }

    public function toOffice(): BelongsTo
    {
        return $this->belongsTo(Office::class, 'to_office_id');
    }

    public function forwardedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'forwarded_by');
    }

    public function acceptedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'accepted_by');
    }
}
```

- [ ] **Step 9: Fill in CannedResponse model**

Replace `app/Models/CannedResponse.php`:

```php
<?php

namespace App\Models;

use Database\Factories\CannedResponseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['office_id', 'title', 'body', 'created_by', 'is_active'])]
class CannedResponse extends Model
{
    /** @use HasFactory<CannedResponseFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function scopeForOffice(Builder $query, int $officeId): void
    {
        $query->where(function (Builder $q) use ($officeId) {
            $q->where('office_id', $officeId)->orWhereNull('office_id');
        });
    }
}
```

- [ ] **Step 10: Fill in ForwardingLogFactory**

Replace `database/factories/ForwardingLogFactory.php`:

```php
<?php

namespace Database\Factories;

use App\Models\ForwardingLog;
use App\Models\Office;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ForwardingLog>
 */
class ForwardingLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'ticket_id' => Ticket::factory(),
            'from_office_id' => Office::factory(),
            'to_office_id' => Office::factory(),
            'forwarded_by' => User::factory(),
            'accepted_by' => null,
            'credit_type' => null,
            'note' => fake()->optional()->sentence(),
            'forwarded_at' => now(),
            'responded_at' => null,
        ];
    }
}
```

- [ ] **Step 11: Fill in CannedResponseFactory**

Replace `database/factories/CannedResponseFactory.php`:

```php
<?php

namespace Database\Factories;

use App\Models\CannedResponse;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CannedResponse>
 */
class CannedResponseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'office_id' => null,
            'title' => fake()->sentence(4),
            'body' => fake()->paragraph(),
            'created_by' => User::factory(),
            'is_active' => true,
        ];
    }
}
```

- [ ] **Step 12: Run tests to verify they pass**

```bash
php artisan test --compact --filter=ForwardingLogTest
```

Expected: PASS (5 tests)

- [ ] **Step 13: Run pint**

```bash
vendor/bin/pint --dirty --format agent
```

---

## Task 8: Wire DatabaseSeeder + final smoke test

**Files:**
- Modify: `database/seeders/DatabaseSeeder.php`

- [ ] **Step 1: Update DatabaseSeeder to include OfficeSeeder**

Replace `database/seeders/DatabaseSeeder.php`:

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            OfficeSeeder::class,
        ]);
    }
}
```

- [ ] **Step 2: Run migrate:fresh --seed to verify clean-state boot**

```bash
php artisan migrate:fresh --seed
```

Expected: All migrations run, seeders complete without errors. 4 roles and 5 offices created.

- [ ] **Step 3: Run the full test suite**

```bash
php artisan test --compact
```

Expected: All tests pass (welcome page tests + all 6 model test files)

- [ ] **Step 4: Run pint**

```bash
vendor/bin/pint --dirty --format agent
```
