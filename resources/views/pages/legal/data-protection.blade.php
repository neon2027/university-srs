<x-layouts.public title="Data Protection — iBUConnect">
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
                    Bicol University processes personal data in compliance with the <em>Data Privacy Act of 2012</em> (Republic Act No. 10173), its Implementing Rules and Regulations, and the issuances of the National Privacy Commission (NPC). iBUConnect is registered with the NPC as a personal information controller.
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
                <p class="mt-3 leading-relaxed">All personal data processed through iBUConnect adheres to the following principles under RA 10173:</p>
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
                <p class="mt-3 leading-relaxed">iBUConnect implements the following technical measures to protect personal data:</p>
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
                    iBUConnect uses Google OAuth for authentication, which involves transmission of limited identity data to Google's servers (which may be located outside the Philippines). This transfer is governed by Google's standard contractual clauses and their compliance with applicable data protection law.
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
