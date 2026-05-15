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
