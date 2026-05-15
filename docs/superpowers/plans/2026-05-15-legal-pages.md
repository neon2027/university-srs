# Legal Pages Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add five public legal pages (Terms of Use, Privacy Policy, Cookie Policy, Data Protection, Transparency Report) to BUSRS with BUSRS-specific content, a shared public layout matching the welcome page style, and wired footer links.

**Architecture:** A `LegalController` serves five static views under `/legal/*` routes (no auth). A new `resources/views/components/layouts/public.blade.php` provides the shared nav + footer shell. The welcome page footer's existing `#` placeholder links are updated to named routes.

**Tech Stack:** Laravel 13, Blade component layouts, Tailwind CSS v4, Alpine.js (for nav mobile menu), Pest v4

---

## File Map

| Action | File | Purpose |
|--------|------|---------|
| Create | `app/Http/Controllers/LegalController.php` | Returns the 5 legal views |
| Modify | `routes/web.php` | `/legal/*` route group |
| Create | `resources/views/components/layouts/public.blade.php` | Shared nav + footer for public pages |
| Create | `resources/views/pages/legal/terms.blade.php` | Terms of Use content |
| Create | `resources/views/pages/legal/privacy.blade.php` | Privacy Policy content |
| Create | `resources/views/pages/legal/cookies.blade.php` | Cookie Policy content |
| Create | `resources/views/pages/legal/data-protection.blade.php` | Data Protection content |
| Create | `resources/views/pages/legal/transparency.blade.php` | Transparency Report content |
| Modify | `resources/views/welcome.blade.php` | Wire footer legal links |
| Create | `tests/Feature/LegalPagesTest.php` | Verifies all 5 routes return 200 as guest |

---

## Task 1: Write Failing Tests + Controller + Routes

**Files:**
- Create: `tests/Feature/LegalPagesTest.php`
- Create: `app/Http/Controllers/LegalController.php`
- Modify: `routes/web.php`

- [ ] **Step 1: Create the test file**

```bash
php artisan make:test --pest LegalPagesTest
```

- [ ] **Step 2: Write failing tests**

Replace the contents of `tests/Feature/LegalPagesTest.php`:

```php
<?php

test('terms of use page is accessible to guests', function () {
    $this->get(route('legal.terms'))->assertOk();
});

test('privacy policy page is accessible to guests', function () {
    $this->get(route('legal.privacy'))->assertOk();
});

test('cookie policy page is accessible to guests', function () {
    $this->get(route('legal.cookies'))->assertOk();
});

test('data protection page is accessible to guests', function () {
    $this->get(route('legal.data-protection'))->assertOk();
});

test('transparency report page is accessible to guests', function () {
    $this->get(route('legal.transparency'))->assertOk();
});
```

- [ ] **Step 3: Run tests to confirm they fail**

```bash
php artisan test --compact --filter=LegalPages
```

Expected: 5 failures — route not defined.

- [ ] **Step 4: Create the controller**

```bash
php artisan make:controller LegalController --no-interaction
```

Replace `app/Http/Controllers/LegalController.php` with:

```php
<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class LegalController extends Controller
{
    public function terms(): View
    {
        return view('pages.legal.terms');
    }

    public function privacy(): View
    {
        return view('pages.legal.privacy');
    }

    public function cookies(): View
    {
        return view('pages.legal.cookies');
    }

    public function dataProtection(): View
    {
        return view('pages.legal.data-protection');
    }

    public function transparency(): View
    {
        return view('pages.legal.transparency');
    }
}
```

- [ ] **Step 5: Add routes to `routes/web.php`**

Add after the `auth.google.callback` route, before the `home` route:

```php
use App\Http\Controllers\LegalController;

Route::prefix('legal')->name('legal.')->group(function (): void {
    Route::get('/terms', [LegalController::class, 'terms'])->name('terms');
    Route::get('/privacy', [LegalController::class, 'privacy'])->name('privacy');
    Route::get('/cookies', [LegalController::class, 'cookies'])->name('cookies');
    Route::get('/data-protection', [LegalController::class, 'dataProtection'])->name('data-protection');
    Route::get('/transparency', [LegalController::class, 'transparency'])->name('transparency');
});
```

- [ ] **Step 6: Run tests again — now they should fail with "View not found"**

```bash
php artisan test --compact --filter=LegalPages
```

Expected: 5 failures — `View [pages.legal.terms] not found`.

---

## Task 2: Public Layout Component

**Files:**
- Create: `resources/views/components/layouts/public.blade.php`

This layout provides the same nav and footer as `welcome.blade.php` (light theme, Tailwind, Alpine.js). It does not include the offices dropdown since legal pages don't need it — just the logo and Sign In button.

- [ ] **Step 1: Create the layout file**

Create `resources/views/components/layouts/public.blade.php`:

```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name') }}</title>
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-white text-gray-900 antialiased">

    {{-- Nav --}}
    <header
        x-data="{ scrolled: false, mobileOpen: false, init() { this.scrolled = window.scrollY > 10; window.addEventListener('scroll', () => { this.scrolled = window.scrollY > 10; }, { passive: true }); } }"
        x-effect="mobileOpen ? document.body.style.overflow = 'hidden' : document.body.style.overflow = ''"
        :class="scrolled ? 'border-gray-200 bg-white/95 shadow-sm backdrop-blur-lg' : 'border-transparent'"
        class="sticky top-0 z-50 w-full border-b transition-all duration-300"
    >
        <nav class="mx-auto flex h-14 w-full max-w-5xl items-center justify-between px-4">
            <a href="{{ route('home') }}" class="flex flex-col rounded-md px-2 py-1 leading-none transition-colors hover:bg-black/5">
                <span class="text-[10px] font-extrabold tracking-widest text-[#0089CB]">BICOL UNIVERSITY</span>
                <span class="text-[9px] font-medium tracking-widest text-gray-500">SERVICE REQUEST SYSTEM</span>
            </a>
            <a href="{{ route('auth.google') }}"
               class="inline-flex items-center gap-2 rounded-md bg-[#0089CB] px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-[#0077b3]">
                Sign In
            </a>
        </nav>
    </header>

    {{-- Page content --}}
    <main>
        {{ $slot }}
    </main>

    {{-- Footer --}}
    <footer class="border-t bg-gray-50/60">
        <div class="mx-auto max-w-6xl px-4 lg:px-6">
            <div class="grid grid-cols-2 gap-8 py-8 md:grid-cols-4">
                <div>
                    <h3 class="mb-4 text-xs font-semibold text-gray-900">University</h3>
                    <ul class="space-y-2 text-sm text-gray-500">
                        <li><a href="#" class="transition-colors hover:text-gray-900">About Bicol University</a></li>
                        <li><a href="#" class="transition-colors hover:text-gray-900">University Website</a></li>
                        <li><a href="#" class="transition-colors hover:text-gray-900">BU Portal</a></li>
                        <li><a href="#" class="transition-colors hover:text-gray-900">Announcements</a></li>
                        <li><a href="#" class="transition-colors hover:text-gray-900">Academic Calendar</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="mb-4 text-xs font-semibold text-gray-900">Support</h3>
                    <ul class="space-y-2 text-sm text-gray-500">
                        <li><a href="mailto:itsupport@bicol-u.edu.ph" class="transition-colors hover:text-gray-900">Technical Support</a></li>
                        <li><a href="#" class="transition-colors hover:text-gray-900">Help Center</a></li>
                        <li><a href="#" class="transition-colors hover:text-gray-900">FAQs</a></li>
                    </ul>
                    <div class="mt-3 space-y-0.5">
                        <p class="text-[11px] text-gray-400">itsupport@bicol-u.edu.ph</p>
                        <p class="text-[11px] text-gray-400">Mon–Fri, 8:00 AM – 5:00 PM</p>
                    </div>
                </div>
                <div>
                    <h3 class="mb-4 text-xs font-semibold text-gray-900">Legal</h3>
                    <ul class="space-y-2 text-sm text-gray-500">
                        <li><a href="{{ route('legal.terms') }}" class="transition-colors hover:text-gray-900">Terms of Use</a></li>
                        <li><a href="{{ route('legal.privacy') }}" class="transition-colors hover:text-gray-900">Privacy Policy</a></li>
                        <li><a href="{{ route('legal.cookies') }}" class="transition-colors hover:text-gray-900">Cookie Policy</a></li>
                        <li><a href="{{ route('legal.data-protection') }}" class="transition-colors hover:text-gray-900">Data Protection</a></li>
                        <li><a href="{{ route('legal.transparency') }}" class="transition-colors hover:text-gray-900">Transparency Report</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="mb-4 text-xs font-semibold text-gray-900">Portal</h3>
                    <ul class="space-y-2 text-sm text-gray-500">
                        <li><a href="{{ route('auth.google') }}" class="transition-colors hover:text-gray-900">Sign In</a></li>
                        <li><a href="{{ route('home') }}" class="transition-colors hover:text-gray-900">Home</a></li>
                    </ul>
                </div>
            </div>

            <div class="h-px bg-gray-200"></div>

            <div class="py-4 text-center text-xs text-gray-400">
                <p>© {{ date('Y') }} Bicol University — Service Request System. All rights reserved.</p>
            </div>
        </div>
    </footer>

</body>
</html>
```

---

## Task 3: Terms of Use Page

**Files:**
- Create: `resources/views/pages/legal/terms.blade.php`

- [ ] **Step 1: Create the directory**

```bash
mkdir -p resources/views/pages/legal
```

- [ ] **Step 2: Create the view**

Create `resources/views/pages/legal/terms.blade.php`:

```blade
<x-layouts.public title="Terms of Use — BUSRS">
    <div class="mx-auto max-w-3xl px-4 py-16 lg:px-6">

        {{-- Breadcrumb --}}
        <nav class="mb-8 flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('home') }}" class="transition-colors hover:text-gray-900">Home</a>
            <span>/</span>
            <span class="text-gray-900">Terms of Use</span>
        </nav>

        {{-- Header --}}
        <div class="mb-10 border-b border-gray-200 pb-8">
            <p class="mb-2 text-xs font-semibold uppercase tracking-widest text-[#0089CB]">Legal</p>
            <h1 class="text-3xl font-bold tracking-tight text-gray-900">Terms of Use</h1>
            <p class="mt-3 text-sm text-gray-500">Effective date: January 1, 2025 &nbsp;·&nbsp; Last updated: May 1, 2025</p>
        </div>

        {{-- Content --}}
        <div class="prose prose-gray max-w-none space-y-8 text-gray-700">

            <section>
                <h2 class="text-lg font-semibold text-gray-900">1. Acceptance of Terms</h2>
                <p class="mt-3 leading-relaxed">
                    By accessing or using the Bicol University Service Request System (<strong>"BUSRS"</strong> or the <strong>"System"</strong>), you agree to be bound by these Terms of Use. If you do not agree, you must not use the System. These terms apply to all students, faculty, staff, and other users of BUSRS.
                </p>
            </section>

            <section>
                <h2 class="text-lg font-semibold text-gray-900">2. Authorized Use</h2>
                <p class="mt-3 leading-relaxed">BUSRS is provided exclusively for the purpose of submitting, tracking, and managing service requests within Bicol University. Authorized uses include:</p>
                <ul class="mt-3 list-disc space-y-1 pl-6 leading-relaxed">
                    <li>Submitting requests to university offices and departments.</li>
                    <li>Tracking the status of your submitted requests.</li>
                    <li>Communicating with office staff regarding your requests.</li>
                    <li>Uploading supporting documents relevant to your requests.</li>
                </ul>
            </section>

            <section>
                <h2 class="text-lg font-semibold text-gray-900">3. Account and Authentication</h2>
                <p class="mt-3 leading-relaxed">
                    BUSRS uses Google OAuth for authentication. By signing in, you authorize BUSRS to receive your Google account's name, email address, and profile photo. You are responsible for all activity that occurs under your account. You must not share your Google credentials or allow others to access BUSRS on your behalf.
                </p>
            </section>

            <section>
                <h2 class="text-lg font-semibold text-gray-900">4. Prohibited Conduct</h2>
                <p class="mt-3 leading-relaxed">You must not:</p>
                <ul class="mt-3 list-disc space-y-1 pl-6 leading-relaxed">
                    <li>Submit false, misleading, or fraudulent service requests.</li>
                    <li>Attempt to access accounts, data, or systems you are not authorized to use.</li>
                    <li>Upload malicious files, scripts, or content designed to harm the System or other users.</li>
                    <li>Use the System for any commercial purpose or for activities unrelated to Bicol University business.</li>
                    <li>Interfere with or disrupt the integrity or performance of the System.</li>
                    <li>Attempt to reverse-engineer, decompile, or extract source code from the System.</li>
                </ul>
            </section>

            <section>
                <h2 class="text-lg font-semibold text-gray-900">5. Intellectual Property</h2>
                <p class="mt-3 leading-relaxed">
                    All content, trademarks, and materials within BUSRS are the property of Bicol University or its licensors. Nothing in these Terms grants you any right to use Bicol University's name, logo, or trademarks without prior written consent.
                </p>
            </section>

            <section>
                <h2 class="text-lg font-semibold text-gray-900">6. Termination of Access</h2>
                <p class="mt-3 leading-relaxed">
                    Bicol University reserves the right to suspend or terminate your access to BUSRS at any time, with or without notice, for conduct that violates these Terms or is otherwise harmful to the System, other users, or the university.
                </p>
            </section>

            <section>
                <h2 class="text-lg font-semibold text-gray-900">7. Limitation of Liability</h2>
                <p class="mt-3 leading-relaxed">
                    BUSRS is provided on an "as-is" basis. Bicol University makes no warranties, express or implied, regarding the availability, accuracy, or fitness of the System for any particular purpose. The university shall not be liable for any loss or damage arising from your use of or inability to use BUSRS.
                </p>
            </section>

            <section>
                <h2 class="text-lg font-semibold text-gray-900">8. Changes to These Terms</h2>
                <p class="mt-3 leading-relaxed">
                    Bicol University may update these Terms at any time. Continued use of BUSRS after changes are posted constitutes your acceptance of the updated Terms. Significant changes will be communicated via the System or your registered email address.
                </p>
            </section>

            <section>
                <h2 class="text-lg font-semibold text-gray-900">9. Governing Law</h2>
                <p class="mt-3 leading-relaxed">
                    These Terms are governed by the laws of the Republic of the Philippines. Any disputes arising from the use of BUSRS shall be subject to the jurisdiction of the courts of Legazpi City, Albay.
                </p>
            </section>

            <section>
                <h2 class="text-lg font-semibold text-gray-900">10. Contact</h2>
                <p class="mt-3 leading-relaxed">
                    For questions about these Terms, contact the Bicol University Information Technology Office at <a href="mailto:itsupport@bicol-u.edu.ph" class="text-[#0089CB] underline underline-offset-2 hover:text-[#0077b3]">itsupport@bicol-u.edu.ph</a>.
                </p>
            </section>

        </div>

        {{-- Related links --}}
        <div class="mt-12 flex flex-wrap gap-3 border-t border-gray-100 pt-8">
            <a href="{{ route('legal.privacy') }}" class="rounded-md border border-gray-200 px-4 py-2 text-sm text-gray-600 transition-colors hover:border-gray-300 hover:text-gray-900">Privacy Policy</a>
            <a href="{{ route('legal.cookies') }}" class="rounded-md border border-gray-200 px-4 py-2 text-sm text-gray-600 transition-colors hover:border-gray-300 hover:text-gray-900">Cookie Policy</a>
            <a href="{{ route('legal.data-protection') }}" class="rounded-md border border-gray-200 px-4 py-2 text-sm text-gray-600 transition-colors hover:border-gray-300 hover:text-gray-900">Data Protection</a>
            <a href="{{ route('legal.transparency') }}" class="rounded-md border border-gray-200 px-4 py-2 text-sm text-gray-600 transition-colors hover:border-gray-300 hover:text-gray-900">Transparency Report</a>
        </div>
    </div>
</x-layouts.public>
```

---

## Task 4: Privacy Policy Page

**Files:**
- Create: `resources/views/pages/legal/privacy.blade.php`

- [ ] **Step 1: Create the view**

Create `resources/views/pages/legal/privacy.blade.php`:

```blade
<x-layouts.public title="Privacy Policy — BUSRS">
    <div class="mx-auto max-w-3xl px-4 py-16 lg:px-6">

        <nav class="mb-8 flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('home') }}" class="transition-colors hover:text-gray-900">Home</a>
            <span>/</span>
            <span class="text-gray-900">Privacy Policy</span>
        </nav>

        <div class="mb-10 border-b border-gray-200 pb-8">
            <p class="mb-2 text-xs font-semibold uppercase tracking-widest text-[#0089CB]">Legal</p>
            <h1 class="text-3xl font-bold tracking-tight text-gray-900">Privacy Policy</h1>
            <p class="mt-3 text-sm text-gray-500">Effective date: January 1, 2025 &nbsp;·&nbsp; Last updated: May 1, 2025</p>
        </div>

        <div class="prose prose-gray max-w-none space-y-8 text-gray-700">

            <section>
                <h2 class="text-lg font-semibold text-gray-900">1. Overview</h2>
                <p class="mt-3 leading-relaxed">
                    Bicol University (<strong>"we"</strong>, <strong>"us"</strong>, or <strong>"the University"</strong>) operates the Bicol University Service Request System (<strong>"BUSRS"</strong>). This Privacy Policy describes how we collect, use, store, and protect your personal information in accordance with the <em>Data Privacy Act of 2012</em> (Republic Act No. 10173) and its Implementing Rules and Regulations.
                </p>
            </section>

            <section>
                <h2 class="text-lg font-semibold text-gray-900">2. Information We Collect</h2>
                <p class="mt-3 leading-relaxed">We collect the following categories of personal information:</p>
                <ul class="mt-3 list-disc space-y-1 pl-6 leading-relaxed">
                    <li><strong>Identity data</strong> — your full name and email address, obtained from Google OAuth when you sign in.</li>
                    <li><strong>Profile data</strong> — your Google profile photo URL (avatar), used to personalize your portal experience.</li>
                    <li><strong>Request data</strong> — the content, subject, description, and files you submit as part of service requests.</li>
                    <li><strong>Usage data</strong> — session identifiers, timestamps of actions, and IP addresses, collected automatically for security and audit purposes.</li>
                    <li><strong>Onboarding data</strong> — your department/office affiliation, collected during the onboarding process for role assignment.</li>
                </ul>
            </section>

            <section>
                <h2 class="text-lg font-semibold text-gray-900">3. How We Use Your Information</h2>
                <p class="mt-3 leading-relaxed">Your information is used solely for the following purposes:</p>
                <ul class="mt-3 list-disc space-y-1 pl-6 leading-relaxed">
                    <li>Authenticating your identity and managing your BUSRS account.</li>
                    <li>Processing and routing your service requests to the appropriate university office.</li>
                    <li>Enabling office staff to respond to and resolve your requests.</li>
                    <li>Sending system-generated notifications related to your request status.</li>
                    <li>Maintaining records for administrative audit and compliance purposes.</li>
                    <li>Improving the reliability and performance of the System.</li>
                </ul>
            </section>

            <section>
                <h2 class="text-lg font-semibold text-gray-900">4. Legal Basis for Processing</h2>
                <p class="mt-3 leading-relaxed">
                    We process your personal data on the basis of: (a) your consent given at sign-in; (b) the performance of a public function — the delivery of university services; and (c) compliance with legal obligations under applicable Philippine law.
                </p>
            </section>

            <section>
                <h2 class="text-lg font-semibold text-gray-900">5. Data Sharing</h2>
                <p class="mt-3 leading-relaxed">
                    We do not sell, rent, or trade your personal data. Your information may be shared only with:
                </p>
                <ul class="mt-3 list-disc space-y-1 pl-6 leading-relaxed">
                    <li><strong>Bicol University offices and staff</strong> — only to the extent needed to fulfil your request.</li>
                    <li><strong>Google LLC</strong> — as the OAuth provider; subject to Google's own Privacy Policy.</li>
                    <li><strong>Authorized system administrators</strong> — for maintenance and security purposes.</li>
                    <li><strong>Government authorities</strong> — when required by law or lawful order.</li>
                </ul>
            </section>

            <section>
                <h2 class="text-lg font-semibold text-gray-900">6. Data Retention</h2>
                <p class="mt-3 leading-relaxed">
                    Account and request data is retained for a period of five (5) years from the date of last activity, in accordance with the University's records management policy, unless a longer retention period is required by law. After this period, data is securely deleted or anonymized.
                </p>
            </section>

            <section>
                <h2 class="text-lg font-semibold text-gray-900">7. Security</h2>
                <p class="mt-3 leading-relaxed">
                    We implement appropriate technical and organizational measures to protect your personal data against unauthorized access, disclosure, alteration, or destruction. These include encrypted transmission (HTTPS), access controls, and regular security reviews. However, no system is completely secure, and we cannot guarantee absolute security.
                </p>
            </section>

            <section>
                <h2 class="text-lg font-semibold text-gray-900">8. Your Rights</h2>
                <p class="mt-3 leading-relaxed">Under the Data Privacy Act of 2012, you have the right to:</p>
                <ul class="mt-3 list-disc space-y-1 pl-6 leading-relaxed">
                    <li><strong>Access</strong> — request a copy of the personal data we hold about you.</li>
                    <li><strong>Rectification</strong> — request correction of inaccurate personal data.</li>
                    <li><strong>Erasure</strong> — request deletion of your data, subject to lawful retention requirements.</li>
                    <li><strong>Object</strong> — object to the processing of your data in certain circumstances.</li>
                    <li><strong>Portability</strong> — receive a copy of your data in a structured, machine-readable format.</li>
                    <li><strong>Damages</strong> — file a complaint with the National Privacy Commission if your rights are violated.</li>
                </ul>
                <p class="mt-3 leading-relaxed">To exercise any of these rights, contact the University's Data Protection Officer at <a href="mailto:dpo@bicol-u.edu.ph" class="text-[#0089CB] underline underline-offset-2 hover:text-[#0077b3]">dpo@bicol-u.edu.ph</a>.</p>
            </section>

            <section>
                <h2 class="text-lg font-semibold text-gray-900">9. Changes to This Policy</h2>
                <p class="mt-3 leading-relaxed">
                    We may update this Privacy Policy periodically. We will notify you of material changes via the System or your registered email address. Your continued use of BUSRS after the effective date of changes constitutes acceptance of the updated Policy.
                </p>
            </section>

            <section>
                <h2 class="text-lg font-semibold text-gray-900">10. Contact</h2>
                <p class="mt-3 leading-relaxed">
                    For privacy-related inquiries, contact the Data Protection Officer at <a href="mailto:dpo@bicol-u.edu.ph" class="text-[#0089CB] underline underline-offset-2 hover:text-[#0077b3]">dpo@bicol-u.edu.ph</a>, or the IT Office at <a href="mailto:itsupport@bicol-u.edu.ph" class="text-[#0089CB] underline underline-offset-2 hover:text-[#0077b3]">itsupport@bicol-u.edu.ph</a>.
                </p>
            </section>

        </div>

        <div class="mt-12 flex flex-wrap gap-3 border-t border-gray-100 pt-8">
            <a href="{{ route('legal.terms') }}" class="rounded-md border border-gray-200 px-4 py-2 text-sm text-gray-600 transition-colors hover:border-gray-300 hover:text-gray-900">Terms of Use</a>
            <a href="{{ route('legal.cookies') }}" class="rounded-md border border-gray-200 px-4 py-2 text-sm text-gray-600 transition-colors hover:border-gray-300 hover:text-gray-900">Cookie Policy</a>
            <a href="{{ route('legal.data-protection') }}" class="rounded-md border border-gray-200 px-4 py-2 text-sm text-gray-600 transition-colors hover:border-gray-300 hover:text-gray-900">Data Protection</a>
            <a href="{{ route('legal.transparency') }}" class="rounded-md border border-gray-200 px-4 py-2 text-sm text-gray-600 transition-colors hover:border-gray-300 hover:text-gray-900">Transparency Report</a>
        </div>
    </div>
</x-layouts.public>
```

---

## Task 5: Cookie Policy Page

**Files:**
- Create: `resources/views/pages/legal/cookies.blade.php`

- [ ] **Step 1: Create the view**

Create `resources/views/pages/legal/cookies.blade.php`:

```blade
<x-layouts.public title="Cookie Policy — BUSRS">
    <div class="mx-auto max-w-3xl px-4 py-16 lg:px-6">

        <nav class="mb-8 flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('home') }}" class="transition-colors hover:text-gray-900">Home</a>
            <span>/</span>
            <span class="text-gray-900">Cookie Policy</span>
        </nav>

        <div class="mb-10 border-b border-gray-200 pb-8">
            <p class="mb-2 text-xs font-semibold uppercase tracking-widest text-[#0089CB]">Legal</p>
            <h1 class="text-3xl font-bold tracking-tight text-gray-900">Cookie Policy</h1>
            <p class="mt-3 text-sm text-gray-500">Effective date: January 1, 2025 &nbsp;·&nbsp; Last updated: May 1, 2025</p>
        </div>

        <div class="prose prose-gray max-w-none space-y-8 text-gray-700">

            <section>
                <h2 class="text-lg font-semibold text-gray-900">1. What Are Cookies?</h2>
                <p class="mt-3 leading-relaxed">
                    Cookies are small text files placed on your device by a web server when you visit a website. They allow the site to remember your actions and preferences over a period of time, so you don't have to re-enter them each time you visit.
                </p>
            </section>

            <section>
                <h2 class="text-lg font-semibold text-gray-900">2. How BUSRS Uses Cookies</h2>
                <p class="mt-3 leading-relaxed">BUSRS uses a minimal set of cookies strictly necessary for the System to function. We do <strong>not</strong> use advertising cookies, cross-site tracking cookies, or third-party analytics cookies.</p>
            </section>

            <section>
                <h2 class="text-lg font-semibold text-gray-900">3. Cookies We Set</h2>
                <div class="mt-4 overflow-x-auto rounded-lg border border-gray-200">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                            <tr>
                                <th class="px-4 py-3">Cookie Name</th>
                                <th class="px-4 py-3">Purpose</th>
                                <th class="px-4 py-3">Type</th>
                                <th class="px-4 py-3">Duration</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr>
                                <td class="px-4 py-3 font-mono text-xs">busrs_session</td>
                                <td class="px-4 py-3">Maintains your authenticated session after sign-in</td>
                                <td class="px-4 py-3">Strictly Necessary</td>
                                <td class="px-4 py-3">Session (cleared when browser closes)</td>
                            </tr>
                            <tr class="bg-gray-50/50">
                                <td class="px-4 py-3 font-mono text-xs">XSRF-TOKEN</td>
                                <td class="px-4 py-3">Protects against Cross-Site Request Forgery (CSRF) attacks</td>
                                <td class="px-4 py-3">Strictly Necessary</td>
                                <td class="px-4 py-3">Session</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 font-mono text-xs">remember_web_*</td>
                                <td class="px-4 py-3">Keeps you signed in across browser sessions if "Remember me" is active</td>
                                <td class="px-4 py-3">Strictly Necessary</td>
                                <td class="px-4 py-3">400 days</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section>
                <h2 class="text-lg font-semibold text-gray-900">4. Third-Party Cookies</h2>
                <p class="mt-3 leading-relaxed">
                    When you sign in using Google OAuth, Google may set its own cookies on your device. These cookies are governed by <a href="https://policies.google.com/privacy" target="_blank" rel="noopener noreferrer" class="text-[#0089CB] underline underline-offset-2 hover:text-[#0077b3]">Google's Privacy Policy</a>. BUSRS does not control and is not responsible for Google's cookies.
                </p>
            </section>

            <section>
                <h2 class="text-lg font-semibold text-gray-900">5. Managing Cookies</h2>
                <p class="mt-3 leading-relaxed">
                    You can control cookies through your browser settings. Please be aware that disabling the strictly necessary cookies listed above will prevent BUSRS from functioning correctly — you will not be able to sign in or submit requests.
                </p>
                <p class="mt-3 leading-relaxed">For instructions on managing cookies in your browser, refer to your browser's help documentation.</p>
            </section>

            <section>
                <h2 class="text-lg font-semibold text-gray-900">6. Contact</h2>
                <p class="mt-3 leading-relaxed">
                    For questions about our use of cookies, contact the IT Office at <a href="mailto:itsupport@bicol-u.edu.ph" class="text-[#0089CB] underline underline-offset-2 hover:text-[#0077b3]">itsupport@bicol-u.edu.ph</a>.
                </p>
            </section>

        </div>

        <div class="mt-12 flex flex-wrap gap-3 border-t border-gray-100 pt-8">
            <a href="{{ route('legal.terms') }}" class="rounded-md border border-gray-200 px-4 py-2 text-sm text-gray-600 transition-colors hover:border-gray-300 hover:text-gray-900">Terms of Use</a>
            <a href="{{ route('legal.privacy') }}" class="rounded-md border border-gray-200 px-4 py-2 text-sm text-gray-600 transition-colors hover:border-gray-300 hover:text-gray-900">Privacy Policy</a>
            <a href="{{ route('legal.data-protection') }}" class="rounded-md border border-gray-200 px-4 py-2 text-sm text-gray-600 transition-colors hover:border-gray-300 hover:text-gray-900">Data Protection</a>
            <a href="{{ route('legal.transparency') }}" class="rounded-md border border-gray-200 px-4 py-2 text-sm text-gray-600 transition-colors hover:border-gray-300 hover:text-gray-900">Transparency Report</a>
        </div>
    </div>
</x-layouts.public>
```

---

## Task 6: Data Protection Page

**Files:**
- Create: `resources/views/pages/legal/data-protection.blade.php`

- [ ] **Step 1: Create the view**

Create `resources/views/pages/legal/data-protection.blade.php`:

```blade
<x-layouts.public title="Data Protection — BUSRS">
    <div class="mx-auto max-w-3xl px-4 py-16 lg:px-6">

        <nav class="mb-8 flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('home') }}" class="transition-colors hover:text-gray-900">Home</a>
            <span>/</span>
            <span class="text-gray-900">Data Protection</span>
        </nav>

        <div class="mb-10 border-b border-gray-200 pb-8">
            <p class="mb-2 text-xs font-semibold uppercase tracking-widest text-[#0089CB]">Legal</p>
            <h1 class="text-3xl font-bold tracking-tight text-gray-900">Data Protection</h1>
            <p class="mt-3 text-sm text-gray-500">Effective date: January 1, 2025 &nbsp;·&nbsp; Last updated: May 1, 2025</p>
        </div>

        <div class="prose prose-gray max-w-none space-y-8 text-gray-700">

            <section>
                <h2 class="text-lg font-semibold text-gray-900">1. Legal Framework</h2>
                <p class="mt-3 leading-relaxed">
                    Bicol University processes personal data in compliance with the <em>Data Privacy Act of 2012</em> (Republic Act No. 10173), its Implementing Rules and Regulations, and the issuances of the National Privacy Commission (NPC). BUSRS is registered with the NPC as a personal information controller.
                </p>
            </section>

            <section>
                <h2 class="text-lg font-semibold text-gray-900">2. Data Protection Officer</h2>
                <p class="mt-3 leading-relaxed">Bicol University has designated a Data Protection Officer (DPO) responsible for overseeing compliance with data protection laws and handling data subject requests.</p>
                <div class="mt-4 rounded-lg border border-gray-200 bg-gray-50 p-5">
                    <p class="font-semibold text-gray-900">Data Protection Officer</p>
                    <p class="mt-1 text-sm text-gray-600">Bicol University, Legazpi City, Albay 4500, Philippines</p>
                    <p class="mt-1 text-sm text-gray-600">Email: <a href="mailto:dpo@bicol-u.edu.ph" class="text-[#0089CB] underline underline-offset-2 hover:text-[#0077b3]">dpo@bicol-u.edu.ph</a></p>
                </div>
            </section>

            <section>
                <h2 class="text-lg font-semibold text-gray-900">3. Principles of Data Processing</h2>
                <p class="mt-3 leading-relaxed">All personal data processed through BUSRS adheres to the following principles under RA 10173:</p>
                <ul class="mt-3 list-disc space-y-1 pl-6 leading-relaxed">
                    <li><strong>Transparency</strong> — you are informed of the purpose for which your data is collected.</li>
                    <li><strong>Legitimate Purpose</strong> — data is collected only for university service delivery.</li>
                    <li><strong>Proportionality</strong> — only the minimum data necessary is collected.</li>
                    <li><strong>Data Quality</strong> — we take steps to keep your data accurate and up to date.</li>
                    <li><strong>Security</strong> — appropriate safeguards are in place to protect your data.</li>
                    <li><strong>Accountability</strong> — we are responsible for how your data is used.</li>
                </ul>
            </section>

            <section>
                <h2 class="text-lg font-semibold text-gray-900">4. Technical Safeguards</h2>
                <p class="mt-3 leading-relaxed">BUSRS implements the following technical measures to protect personal data:</p>
                <ul class="mt-3 list-disc space-y-1 pl-6 leading-relaxed">
                    <li>All data is transmitted over HTTPS using TLS encryption.</li>
                    <li>Passwords are never stored — authentication is handled entirely by Google OAuth.</li>
                    <li>Role-based access control ensures staff can only access data relevant to their office.</li>
                    <li>Session tokens are rotated on login and invalidated on logout.</li>
                    <li>File uploads are scanned and stored in an access-controlled environment.</li>
                    <li>Database backups are encrypted at rest.</li>
                </ul>
            </section>

            <section>
                <h2 class="text-lg font-semibold text-gray-900">5. Organizational Safeguards</h2>
                <ul class="mt-3 list-disc space-y-1 pl-6 leading-relaxed">
                    <li>Access to production data is limited to authorized personnel only.</li>
                    <li>University staff handling data are trained on data privacy obligations.</li>
                    <li>Third-party integrations (e.g., Google OAuth) are subject to data processing agreements.</li>
                    <li>Regular security reviews and vulnerability assessments are conducted.</li>
                </ul>
            </section>

            <section>
                <h2 class="text-lg font-semibold text-gray-900">6. Data Breach Response</h2>
                <p class="mt-3 leading-relaxed">
                    In the event of a personal data breach, Bicol University will notify the National Privacy Commission within 72 hours of becoming aware of the breach, as required by NPC Circular No. 16-03. Affected data subjects will be notified promptly if the breach is likely to result in high risk to their rights and freedoms.
                </p>
            </section>

            <section>
                <h2 class="text-lg font-semibold text-gray-900">7. Cross-Border Data Transfers</h2>
                <p class="mt-3 leading-relaxed">
                    BUSRS uses Google OAuth for authentication, which involves transmission of limited identity data to Google's servers (which may be located outside the Philippines). This transfer is governed by Google's standard contractual clauses and their compliance with applicable data protection law.
                </p>
            </section>

            <section>
                <h2 class="text-lg font-semibold text-gray-900">8. Data Subject Rights Procedure</h2>
                <p class="mt-3 leading-relaxed">To exercise your rights under RA 10173:</p>
                <ol class="mt-3 list-decimal space-y-1 pl-6 leading-relaxed">
                    <li>Submit your request in writing to the DPO at <a href="mailto:dpo@bicol-u.edu.ph" class="text-[#0089CB] underline underline-offset-2 hover:text-[#0077b3]">dpo@bicol-u.edu.ph</a>.</li>
                    <li>Include your full name, student or employee ID, and a description of your request.</li>
                    <li>We will acknowledge your request within five (5) business days.</li>
                    <li>We will respond to your request within thirty (30) calendar days.</li>
                </ol>
            </section>

        </div>

        <div class="mt-12 flex flex-wrap gap-3 border-t border-gray-100 pt-8">
            <a href="{{ route('legal.terms') }}" class="rounded-md border border-gray-200 px-4 py-2 text-sm text-gray-600 transition-colors hover:border-gray-300 hover:text-gray-900">Terms of Use</a>
            <a href="{{ route('legal.privacy') }}" class="rounded-md border border-gray-200 px-4 py-2 text-sm text-gray-600 transition-colors hover:border-gray-300 hover:text-gray-900">Privacy Policy</a>
            <a href="{{ route('legal.cookies') }}" class="rounded-md border border-gray-200 px-4 py-2 text-sm text-gray-600 transition-colors hover:border-gray-300 hover:text-gray-900">Cookie Policy</a>
            <a href="{{ route('legal.transparency') }}" class="rounded-md border border-gray-200 px-4 py-2 text-sm text-gray-600 transition-colors hover:border-gray-300 hover:text-gray-900">Transparency Report</a>
        </div>
    </div>
</x-layouts.public>
```

---

## Task 7: Transparency Report Page

**Files:**
- Create: `resources/views/pages/legal/transparency.blade.php`

- [ ] **Step 1: Create the view**

Create `resources/views/pages/legal/transparency.blade.php`:

```blade
<x-layouts.public title="Transparency Report — BUSRS">
    <div class="mx-auto max-w-3xl px-4 py-16 lg:px-6">

        <nav class="mb-8 flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('home') }}" class="transition-colors hover:text-gray-900">Home</a>
            <span>/</span>
            <span class="text-gray-900">Transparency Report</span>
        </nav>

        <div class="mb-10 border-b border-gray-200 pb-8">
            <p class="mb-2 text-xs font-semibold uppercase tracking-widest text-[#0089CB]">Legal</p>
            <h1 class="text-3xl font-bold tracking-tight text-gray-900">Transparency Report</h1>
            <p class="mt-3 text-sm text-gray-500">Reporting period: Academic Year 2024–2025 &nbsp;·&nbsp; Published: May 1, 2025</p>
        </div>

        <div class="prose prose-gray max-w-none space-y-8 text-gray-700">

            <section>
                <h2 class="text-lg font-semibold text-gray-900">About This Report</h2>
                <p class="mt-3 leading-relaxed">
                    Bicol University publishes this Transparency Report to provide the university community with information about how BUSRS operates, how data is handled, and any significant incidents or changes to the System. This report covers the academic year 2024–2025.
                </p>
            </section>

            <section>
                <h2 class="text-lg font-semibold text-gray-900">Service Requests</h2>
                <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-4">
                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 text-center">
                        <p class="text-2xl font-bold text-[#0089CB]">—</p>
                        <p class="mt-1 text-xs text-gray-500">Total Requests Submitted</p>
                    </div>
                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 text-center">
                        <p class="text-2xl font-bold text-green-600">—</p>
                        <p class="mt-1 text-xs text-gray-500">Requests Resolved</p>
                    </div>
                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 text-center">
                        <p class="text-2xl font-bold text-gray-700">—</p>
                        <p class="mt-1 text-xs text-gray-500">Avg. Resolution Time</p>
                    </div>
                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 text-center">
                        <p class="text-2xl font-bold text-gray-700">—</p>
                        <p class="mt-1 text-xs text-gray-500">Active Users</p>
                    </div>
                </div>
                <p class="mt-4 text-xs text-gray-400 italic">Statistics will be published at the end of each academic year.</p>
            </section>

            <section>
                <h2 class="text-lg font-semibold text-gray-900">System Availability</h2>
                <p class="mt-3 leading-relaxed">
                    BUSRS targets 99.5% monthly uptime during the academic year. Planned maintenance windows are scheduled outside of regular university hours (typically Friday evenings or weekends) and announced at least 24 hours in advance via the System.
                </p>
                <div class="mt-4 overflow-x-auto rounded-lg border border-gray-200">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                            <tr>
                                <th class="px-4 py-3">Period</th>
                                <th class="px-4 py-3">Uptime</th>
                                <th class="px-4 py-3">Incidents</th>
                                <th class="px-4 py-3">Planned Maintenance</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr>
                                <td class="px-4 py-3">AY 2024–2025 (Full Year)</td>
                                <td class="px-4 py-3">—</td>
                                <td class="px-4 py-3">—</td>
                                <td class="px-4 py-3">—</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p class="mt-3 text-xs text-gray-400 italic">Data will be populated at the end of the reporting period.</p>
            </section>

            <section>
                <h2 class="text-lg font-semibold text-gray-900">Data Requests and Disclosures</h2>
                <p class="mt-3 leading-relaxed">
                    The following table summarizes requests for data from external parties (e.g., law enforcement, government agencies) received during the reporting period:
                </p>
                <div class="mt-4 overflow-x-auto rounded-lg border border-gray-200">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                            <tr>
                                <th class="px-4 py-3">Type</th>
                                <th class="px-4 py-3">Requests Received</th>
                                <th class="px-4 py-3">Requests Complied With</th>
                                <th class="px-4 py-3">Requests Rejected</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr>
                                <td class="px-4 py-3">Government / Law Enforcement</td>
                                <td class="px-4 py-3">0</td>
                                <td class="px-4 py-3">0</td>
                                <td class="px-4 py-3">0</td>
                            </tr>
                            <tr class="bg-gray-50/50">
                                <td class="px-4 py-3">Data Subject Access Requests</td>
                                <td class="px-4 py-3">—</td>
                                <td class="px-4 py-3">—</td>
                                <td class="px-4 py-3">—</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section>
                <h2 class="text-lg font-semibold text-gray-900">Security Incidents</h2>
                <p class="mt-3 leading-relaxed">
                    No personal data breaches or security incidents requiring NPC notification have occurred during the reporting period.
                </p>
            </section>

            <section>
                <h2 class="text-lg font-semibold text-gray-900">Policy Changes</h2>
                <p class="mt-3 leading-relaxed">The following changes to BUSRS policies were made during the reporting period:</p>
                <ul class="mt-3 list-disc space-y-1 pl-6 leading-relaxed">
                    <li><strong>May 2025</strong> — Terms of Use, Privacy Policy, Cookie Policy, Data Protection notice, and Transparency Report published for the first time.</li>
                </ul>
            </section>

            <section>
                <h2 class="text-lg font-semibold text-gray-900">Contact</h2>
                <p class="mt-3 leading-relaxed">
                    Questions about this report can be directed to the Data Protection Officer at <a href="mailto:dpo@bicol-u.edu.ph" class="text-[#0089CB] underline underline-offset-2 hover:text-[#0077b3]">dpo@bicol-u.edu.ph</a>.
                </p>
            </section>

        </div>

        <div class="mt-12 flex flex-wrap gap-3 border-t border-gray-100 pt-8">
            <a href="{{ route('legal.terms') }}" class="rounded-md border border-gray-200 px-4 py-2 text-sm text-gray-600 transition-colors hover:border-gray-300 hover:text-gray-900">Terms of Use</a>
            <a href="{{ route('legal.privacy') }}" class="rounded-md border border-gray-200 px-4 py-2 text-sm text-gray-600 transition-colors hover:border-gray-300 hover:text-gray-900">Privacy Policy</a>
            <a href="{{ route('legal.cookies') }}" class="rounded-md border border-gray-200 px-4 py-2 text-sm text-gray-600 transition-colors hover:border-gray-300 hover:text-gray-900">Cookie Policy</a>
            <a href="{{ route('legal.data-protection') }}" class="rounded-md border border-gray-200 px-4 py-2 text-sm text-gray-600 transition-colors hover:border-gray-300 hover:text-gray-900">Data Protection</a>
        </div>
    </div>
</x-layouts.public>
```

---

## Task 8: Wire Welcome Page Footer Links

**Files:**
- Modify: `resources/views/welcome.blade.php` (lines 651–657)

The footer already has a Legal column. Replace the `href="#"` placeholders with actual named routes.

- [ ] **Step 1: Update the 5 Legal footer links in `welcome.blade.php`**

Find this block (around line 650–657):

```blade
<li><a href="#" class="transition-colors hover:text-gray-900">Privacy Policy</a></li>
<li><a href="#" class="transition-colors hover:text-gray-900">Terms of Use</a></li>
<li><a href="#" class="transition-colors hover:text-gray-900">Cookie Policy</a></li>
<li><a href="#" class="transition-colors hover:text-gray-900">Data Protection</a></li>
<li><a href="#" class="transition-colors hover:text-gray-900">Transparency Report</a></li>
```

Replace with:

```blade
<li><a href="{{ route('legal.privacy') }}" class="transition-colors hover:text-gray-900">Privacy Policy</a></li>
<li><a href="{{ route('legal.terms') }}" class="transition-colors hover:text-gray-900">Terms of Use</a></li>
<li><a href="{{ route('legal.cookies') }}" class="transition-colors hover:text-gray-900">Cookie Policy</a></li>
<li><a href="{{ route('legal.data-protection') }}" class="transition-colors hover:text-gray-900">Data Protection</a></li>
<li><a href="{{ route('legal.transparency') }}" class="transition-colors hover:text-gray-900">Transparency Report</a></li>
```

---

## Task 9: Run Pint, Verify All Tests Pass, Commit

**Files:** All modified/created PHP files

- [ ] **Step 1: Run Pint on all changed PHP files**

```bash
vendor/bin/pint --dirty --format agent
```

Expected: `{"tool":"pint","result":"passed"}`

- [ ] **Step 2: Run the legal page tests**

```bash
php artisan test --compact --filter=LegalPages
```

Expected: 5 passed.

- [ ] **Step 3: Run the full test suite**

```bash
php artisan test --compact
```

Expected: all tests pass (no regressions).

- [ ] **Step 4: Commit**

```bash
git add \
  app/Http/Controllers/LegalController.php \
  routes/web.php \
  resources/views/components/layouts/public.blade.php \
  resources/views/pages/legal/terms.blade.php \
  resources/views/pages/legal/privacy.blade.php \
  resources/views/pages/legal/cookies.blade.php \
  resources/views/pages/legal/data-protection.blade.php \
  resources/views/pages/legal/transparency.blade.php \
  resources/views/welcome.blade.php \
  tests/Feature/LegalPagesTest.php

git commit -m "$(cat <<'EOF'
feat: add public legal pages with shared layout and wired footer links

Adds Terms of Use, Privacy Policy, Cookie Policy, Data Protection, and
Transparency Report pages under /legal/* with BUSRS-specific content.
Creates a reusable public layout component and wires the welcome page
footer legal links to their named routes.

Co-Authored-By: Claude Sonnet 4.6 <noreply@anthropic.com>
EOF
)"
```
