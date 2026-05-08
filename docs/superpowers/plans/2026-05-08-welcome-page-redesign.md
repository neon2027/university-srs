# Welcome Page Redesign Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Redesign `resources/views/welcome.blade.php` into a structured, typography-driven layout with a blue (#0089CB) and orange (#FE8926) palette — no icons, fully responsive, 7 sections.

**Architecture:** Single Blade file edit. The page is served at `/` via `Route::view('/', 'welcome')->name('home')`. No backend changes. All styling is Tailwind CSS v4 utility classes; new arbitrary color classes (`bg-[#0089CB]` etc.) are picked up automatically by Vite through the `@source '../views'` directive in `app.css`.

**Tech Stack:** Laravel 13, Blade, Tailwind CSS v4, Pest 4

---

### Task 1: Write the failing feature test

**Files:**
- Create: `tests/Feature/WelcomePageTest.php`

- [ ] **Step 1: Create the test file**

```bash
php artisan make:test --pest WelcomePageTest
```

- [ ] **Step 2: Replace the generated content**

Open `tests/Feature/WelcomePageTest.php` and replace its entire content with:

```php
<?php

test('welcome page loads successfully', function () {
    $this->get(route('home'))->assertOk();
});

test('welcome page has navbar with university name', function () {
    $this->get(route('home'))
        ->assertSeeText('BICOL UNIVERSITY');
});

test('welcome page has hero headline', function () {
    $this->get(route('home'))
        ->assertSeeText('Get the help you need');
});

test('welcome page has new request and track columns', function () {
    $response = $this->get(route('home'));
    $response->assertSeeText('New Request');
    $response->assertSeeText('Track a Ticket');
    $response->assertSeeText('Submit Now');
    $response->assertSeeText('Track Now');
});

test('welcome page has how it works section', function () {
    $this->get(route('home'))
        ->assertSeeText('How It Works');
});

test('welcome page has departments section', function () {
    $response = $this->get(route('home'));
    $response->assertSeeText('Departments');
    $response->assertSeeText('Information Technology Office');
    $response->assertSeeText('Physical Plant Office');
});

test('welcome page has footer with technical support', function () {
    $response = $this->get(route('home'));
    $response->assertSeeText('Technical Support');
    $response->assertSee('itsupport@bicol-u.edu.ph');
});
```

- [ ] **Step 3: Run the tests to confirm they fail**

```bash
php artisan test --compact --filter=WelcomePageTest
```

Expected: most tests FAIL because the current view does not contain the new content. The first test (`welcome page loads successfully`) should PASS since the route already exists.

---

### Task 2: Implement the HTML shell, navbar, and hero headline

**Files:**
- Modify: `resources/views/welcome.blade.php`

- [ ] **Step 1: Replace the `<body>` content**

Keep the entire `<head>` section intact (do not touch `@fonts`, `<style>`, or `@vite`). Replace only the `<body>` tag and everything inside it with:

```html
<body class="bg-white">

    {{-- 1. NAVBAR --}}
    <nav class="bg-[#0089CB] px-6 lg:px-10 py-3 flex items-center justify-between">
        <span class="text-white font-bold text-xs uppercase tracking-widest">BICOL UNIVERSITY — SERVICE REQUEST SYSTEM</span>
        <div class="flex items-center gap-6">
            <a href="{{ route('login') }}" class="text-white/80 text-xs hover:text-white transition-colors">Log in</a>
            <a href="{{ route('register') }}" class="text-white/80 text-xs hover:text-white transition-colors border-l border-white/30 pl-6">Register</a>
        </div>
    </nav>

    <main class="max-w-5xl mx-auto">

        {{-- 2. HERO HEADLINE --}}
        <div class="px-6 lg:px-10 py-12 border-b-2 border-black">
            <h1 class="text-5xl lg:text-6xl font-black text-[#111111] leading-tight tracking-tight mb-4">
                Get the help<br>you need — fast.
            </h1>
            <p class="text-sm text-[#555555] leading-relaxed max-w-lg">
                Submit service requests to any Bicol University department online. No queues. No paperwork. Just results.
            </p>
        </div>

        {{-- SECTIONS 3–6 WILL BE ADDED IN THE NEXT TASKS --}}

    </main>

    {{-- FOOTER WILL BE ADDED IN TASK 6 --}}

</body>
```

- [ ] **Step 2: Run the two navbar/hero tests**

```bash
php artisan test --compact --filter="welcome page has navbar|welcome page has hero"
```

Expected: both PASS.

---

### Task 3: Add action columns and stat line

**Files:**
- Modify: `resources/views/welcome.blade.php`

- [ ] **Step 1: Replace the `{{-- SECTIONS 3–6 --}}` placeholder with the action columns and stat line**

```html
        {{-- 3. TWO ACTION COLUMNS --}}
        <div class="flex flex-col md:flex-row border-b border-gray-200">
            <div class="flex-1 px-6 lg:px-10 py-6 md:border-r border-gray-200">
                <p class="text-[#0089CB] text-xs font-extrabold uppercase tracking-widest mb-2">New Request</p>
                <p class="text-sm text-[#555555] leading-relaxed mb-5">
                    Submit a ticket for IT support, maintenance, administrative concerns, or any university service.
                </p>
                <a href="{{ route('login') }}" class="inline-block bg-[#0089CB] text-white px-5 py-2 text-xs font-bold hover:bg-[#007ab5] transition-colors">
                    Submit Now
                </a>
            </div>
            <div class="flex-1 px-6 lg:px-10 py-6">
                <p class="text-[#555555] text-xs font-extrabold uppercase tracking-widest mb-2">Track a Ticket</p>
                <p class="text-sm text-[#555555] leading-relaxed mb-5">
                    Already submitted? Enter your reference number to view real-time status and updates.
                </p>
                <a href="{{ route('login') }}" class="inline-block border-2 border-[#0089CB] text-[#0089CB] px-5 py-2 text-xs font-semibold hover:bg-[#0089CB] hover:text-white transition-colors">
                    Track Now
                </a>
            </div>
        </div>

        {{-- 4. STAT LINE --}}
        <div class="bg-[#F5FBFF] border-b border-[#E0F0FA] px-6 lg:px-10 py-3 flex flex-wrap items-center gap-3">
            <span class="text-xs text-[#555555]">Serving <strong class="text-[#111111] font-bold">10+ departments</strong> across Bicol University</span>
            <span class="w-1 h-1 rounded-full bg-gray-300 inline-block flex-shrink-0"></span>
            <span class="text-xs text-[#555555]"><strong class="text-[#111111] font-bold">500+</strong> requests resolved</span>
            <span class="w-1 h-1 rounded-full bg-gray-300 inline-block flex-shrink-0"></span>
            <span class="text-xs text-[#555555]">Avg. response: <strong class="text-[#111111] font-bold">24 hrs</strong></span>
        </div>

        {{-- SECTIONS 5–6 WILL BE ADDED IN THE NEXT TASKS --}}
```

- [ ] **Step 2: Run the action columns test**

```bash
php artisan test --compact --filter="welcome page has new request"
```

Expected: PASS.

---

### Task 4: Add the How It Works section

**Files:**
- Modify: `resources/views/welcome.blade.php`

- [ ] **Step 1: Replace `{{-- SECTIONS 5–6 --}}` with the How It Works section**

```html
        {{-- 5. HOW IT WORKS --}}
        <div class="px-6 lg:px-10 py-10 border-b border-gray-200">
            <h2 class="text-xs font-extrabold uppercase tracking-widest text-[#111111] border-l-4 border-[#0089CB] pl-3 mb-8">
                How It Works
            </h2>
            <div>
                <div class="flex gap-6 items-start pb-6">
                    <span class="text-[#0089CB] font-black text-xl w-8 flex-shrink-0 leading-none">01</span>
                    <div>
                        <p class="font-bold text-sm text-[#111111] mb-1">Submit your request online</p>
                        <p class="text-xs text-[#666666] leading-relaxed">
                            Fill out a short form describing your concern. Select the department and service type. No sign-up required for basic requests.
                        </p>
                    </div>
                </div>
                <div class="border-t border-gray-100 ml-14 mb-6"></div>
                <div class="flex gap-6 items-start pb-6">
                    <span class="text-[#0089CB] font-black text-xl w-8 flex-shrink-0 leading-none">02</span>
                    <div>
                        <p class="font-bold text-sm text-[#111111] mb-1">Your ticket is routed automatically</p>
                        <p class="text-xs text-[#666666] leading-relaxed">
                            The system forwards your request to the right department immediately. You receive a reference number to track progress.
                        </p>
                    </div>
                </div>
                <div class="border-t border-gray-100 ml-14 mb-6"></div>
                <div class="flex gap-6 items-start">
                    <span class="text-[#FE8926] font-black text-xl w-8 flex-shrink-0 leading-none">03</span>
                    <div>
                        <p class="font-bold text-sm text-[#111111] mb-1">Get resolved and notified</p>
                        <p class="text-xs text-[#666666] leading-relaxed">
                            The assigned staff handles your request and updates the status. You are notified once your concern is resolved.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- SECTION 6 WILL BE ADDED IN THE NEXT TASK --}}
```

- [ ] **Step 2: Run the how it works test**

```bash
php artisan test --compact --filter="welcome page has how it works"
```

Expected: PASS.

---

### Task 5: Add the Departments & Services section

**Files:**
- Modify: `resources/views/welcome.blade.php`

- [ ] **Step 1: Replace `{{-- SECTION 6 --}}` with the departments grid**

```html
        {{-- 6. DEPARTMENTS & SERVICES --}}
        <div class="bg-[#FAFAFA] px-6 lg:px-10 py-10 border-b border-gray-200">
            <h2 class="text-xs font-extrabold uppercase tracking-widest text-[#111111] border-l-4 border-[#FE8926] pl-3 mb-6">
                Departments & Services
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div class="bg-white border border-gray-200 overflow-hidden">
                    <div class="h-0.5 bg-[#0089CB]"></div>
                    <div class="px-4 py-3">
                        <p class="font-bold text-sm text-[#111111] mb-1">Information Technology Office</p>
                        <p class="text-xs text-[#888888]">Systems, networks, hardware, software</p>
                    </div>
                </div>
                <div class="bg-white border border-gray-200 overflow-hidden">
                    <div class="h-0.5 bg-[#0089CB]"></div>
                    <div class="px-4 py-3">
                        <p class="font-bold text-sm text-[#111111] mb-1">Physical Plant Office</p>
                        <p class="text-xs text-[#888888]">Maintenance, repairs, facilities</p>
                    </div>
                </div>
                <div class="bg-white border border-gray-200 overflow-hidden">
                    <div class="h-0.5 bg-[#0089CB]"></div>
                    <div class="px-4 py-3">
                        <p class="font-bold text-sm text-[#111111] mb-1">Registrar's Office</p>
                        <p class="text-xs text-[#888888]">Documents, certifications, records</p>
                    </div>
                </div>
                <div class="bg-white border border-gray-200 overflow-hidden">
                    <div class="h-0.5 bg-[#0089CB]"></div>
                    <div class="px-4 py-3">
                        <p class="font-bold text-sm text-[#111111] mb-1">Library Services</p>
                        <p class="text-xs text-[#888888]">Resources, access, research support</p>
                    </div>
                </div>
                <div class="bg-white border border-gray-200 overflow-hidden">
                    <div class="h-0.5 bg-[#0089CB]"></div>
                    <div class="px-4 py-3">
                        <p class="font-bold text-sm text-[#111111] mb-1">Finance Office</p>
                        <p class="text-xs text-[#888888]">Payments, billing, scholarships</p>
                    </div>
                </div>
                <div class="bg-white border border-gray-200 overflow-hidden">
                    <div class="h-0.5 bg-[#FE8926]"></div>
                    <div class="px-4 py-3 flex items-center">
                        <a href="#" class="text-xs font-bold text-[#0089CB] hover:underline">+ View all departments →</a>
                    </div>
                </div>
            </div>
        </div>

    </main>{{-- close main --}}

    {{-- FOOTER WILL BE ADDED IN TASK 6 --}}
```

- [ ] **Step 2: Run the departments test**

```bash
php artisan test --compact --filter="welcome page has departments"
```

Expected: PASS.

---

### Task 6: Add the footer and finalize

**Files:**
- Modify: `resources/views/welcome.blade.php`

- [ ] **Step 1: Replace `{{-- FOOTER WILL BE ADDED IN TASK 6 --}}` with the footer**

```html
    {{-- 7. FOOTER --}}
    <footer class="bg-[#111111] px-6 lg:px-10 py-8">
        <div class="max-w-5xl mx-auto">
            <div class="flex flex-col md:flex-row justify-between gap-6 mb-6">
                <div>
                    <p class="font-bold text-sm text-white mb-1">Bicol University</p>
                    <p class="text-xs text-[#888888] leading-relaxed">
                        Service Request System<br>
                        Legazpi City, Albay, Philippines
                    </p>
                </div>
                <div class="md:text-right">
                    <p class="font-bold text-sm text-[#FE8926] mb-2">Technical Support</p>
                    <p class="text-xs text-[#AAAAAA] leading-relaxed">
                        itsupport@bicol-u.edu.ph<br>
                        (052) 820-0000 loc. 101<br>
                        Mon – Fri, 8:00 AM – 5:00 PM
                    </p>
                </div>
            </div>
            <div class="border-t border-[#333333] pt-4 flex flex-col md:flex-row justify-between items-center gap-2">
                <p class="text-xs text-[#555555]">© 2025 Bicol University. All rights reserved.</p>
                <div class="flex gap-4">
                    <a href="#" class="text-xs text-[#555555] hover:text-[#888888] transition-colors">Privacy Policy</a>
                    <a href="#" class="text-xs text-[#555555] hover:text-[#888888] transition-colors">Terms of Use</a>
                </div>
            </div>
        </div>
    </footer>

</body>
```

- [ ] **Step 2: Run all welcome page tests**

```bash
php artisan test --compact --filter=WelcomePageTest
```

Expected: all 7 tests PASS.

---

### Task 7: Build assets, run Pint, full test suite, commit

**Files:**
- No new files

- [ ] **Step 1: Build Vite assets so new Tailwind classes are compiled**

```bash
npm run build
```

Expected: build succeeds with no errors. New arbitrary color classes (`bg-[#0089CB]`, `text-[#FE8926]`, etc.) will be included in the compiled CSS.

- [ ] **Step 2: Run Pint on modified PHP files**

```bash
vendor/bin/pint --dirty --format agent
```

Expected: formats `tests/Feature/WelcomePageTest.php`. The Blade file is not formatted by Pint.

- [ ] **Step 3: Run the full test suite**

```bash
php artisan test --compact
```

Expected: all existing tests still pass, all 7 new WelcomePageTest tests pass.

- [ ] **Step 4: Commit**

```bash
git add resources/views/welcome.blade.php tests/Feature/WelcomePageTest.php
git commit -m "redesign welcome page with blue/orange palette and 7-section layout"
```
