<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<x-articles.head
    title="Privacy Policy · PrComet"
    description="How PrComet collects, uses, and protects your information."
    :canonical="route('privacy')"
/>
<body class="font-sans antialiased text-slate-900 bg-white">

    <x-site-nav />

    <section class="relative overflow-hidden bg-slate-900 text-white">
        <div class="absolute inset-0 pointer-events-none" aria-hidden="true">
            <div class="absolute -top-24 right-0 h-[360px] w-[360px] rounded-full bg-gradient-to-br from-indigo-500/25 to-fuchsia-500/15 blur-3xl"></div>
        </div>
        <div class="relative max-w-5xl mx-auto px-6 py-16 lg:py-20">
            <p class="text-xs font-semibold uppercase tracking-[0.25em] text-indigo-300">Legal</p>
            <h1 class="mt-4 text-4xl lg:text-5xl font-semibold tracking-tight">Privacy Policy</h1>
            <p class="mt-3 text-sm text-slate-400">Last updated {{ date('F Y') }}</p>
        </div>
        <div class="relative h-px bg-gradient-to-r from-transparent via-violet-500/50 to-transparent"></div>
    </section>

    <main class="max-w-3xl mx-auto px-6 py-16 lg:py-20 prose prose-slate">
        <p>
            This policy explains what information PrComet collects, how we use it, and the choices you
            have. We keep it short and plain. If anything is unclear, email
            <a href="mailto:hello@prcomet.com">hello@prcomet.com</a>.
        </p>

        <h2>Information we collect</h2>
        <ul>
            <li><strong>Information you give us.</strong> When you request a demo or contact us, we collect details such as your name, work email, company, role, and anything you write in the message.</li>
            <li><strong>Account information.</strong> If you use the product, we store the data needed to provide it, including your account and the companies and content you choose to monitor.</li>
            <li><strong>Usage data.</strong> We collect basic, privacy-respecting analytics about how our public pages are used so we can improve them.</li>
        </ul>

        <h2>How we use information</h2>
        <ul>
            <li>To respond to your requests and provide and improve the service.</li>
            <li>To communicate with you about your account, demos, and relevant updates.</li>
            <li>To keep the service secure and to meet legal obligations.</li>
        </ul>

        <h2>How we share information</h2>
        <p>
            We do not sell your personal information. We share it only with service providers who help
            us operate (for example, hosting and email delivery), and only as needed to provide the
            service, or where required by law.
        </p>

        <h2>Data retention</h2>
        <p>
            We keep information for as long as needed to provide the service and for legitimate business
            or legal purposes. You can ask us to delete your information at any time.
        </p>

        <h2>Your choices</h2>
        <p>
            You can request access to, correction of, or deletion of your personal information by
            emailing <a href="mailto:hello@prcomet.com">hello@prcomet.com</a>. You can also unsubscribe
            from any non-essential email using the link in that email.
        </p>

        <h2>Contact</h2>
        <p>
            Questions about this policy? Reach us at
            <a href="mailto:hello@prcomet.com">hello@prcomet.com</a>.
        </p>

        <p class="text-sm text-slate-500">
            This page is provided for general information and is not legal advice. We may update it from
            time to time; the date above reflects the latest revision.
        </p>
    </main>

    <x-site-footer />
</body>
</html>
