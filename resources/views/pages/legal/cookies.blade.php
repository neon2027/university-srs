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
