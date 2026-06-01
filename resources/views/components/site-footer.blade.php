{{-- Shared marketing footer: link columns + legal row. Used across the
     homepage, articles, industry pages, and standalone marketing pages. --}}
<footer class="border-t border-slate-200 bg-white">
    <div class="max-w-7xl mx-auto px-6 lg:px-8 py-14">
        <div class="grid grid-cols-2 gap-8 md:grid-cols-4 lg:grid-cols-5">
            {{-- Brand --}}
            <div class="col-span-2 lg:col-span-2">
                <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                    <span class="h-7 w-7 rounded-full bg-slate-900"></span>
                    <span class="font-semibold text-slate-900 tracking-tight">PrComet</span>
                </a>
                <p class="mt-4 max-w-xs text-sm text-slate-500 leading-relaxed">
                    Find the journalists who'll publish your story. Discovery, not automation.
                </p>
            </div>

            {{-- Product --}}
            <div>
                <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-400">Product</h3>
                <ul class="mt-4 space-y-3 text-sm">
                    <li><a href="{{ route('home') }}#how" class="text-slate-600 hover:text-slate-900">How it works</a></li>
                    <li><a href="{{ route('home') }}#brief" class="text-slate-600 hover:text-slate-900">The brief</a></li>
                    <li><a href="{{ route('home') }}#for-whom" class="text-slate-600 hover:text-slate-900">Who it's for</a></li>
                    <li><a href="{{ route('articles.index') }}" class="text-slate-600 hover:text-slate-900">The Angle</a></li>
                    <li><a href="{{ route('home') }}#request-demo" class="text-slate-600 hover:text-slate-900">Request a demo</a></li>
                </ul>
            </div>

            {{-- Industries --}}
            <div>
                <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-400">Industries</h3>
                <ul class="mt-4 space-y-3 text-sm">
                    <li><a href="{{ route('industries.junior-mining') }}" class="text-slate-600 hover:text-slate-900">Junior Mining</a></li>
                    <li><a href="{{ route('industries.manufacturing') }}" class="text-slate-600 hover:text-slate-900">Manufacturing</a></li>
                    <li><a href="{{ route('industries.tourism-councils') }}" class="text-slate-600 hover:text-slate-900">Tourism Councils</a></li>
                    <li><a href="{{ route('industries.municipalities') }}" class="text-slate-600 hover:text-slate-900">Municipalities</a></li>
                </ul>
            </div>

            {{-- Company --}}
            <div>
                <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-400">Company</h3>
                <ul class="mt-4 space-y-3 text-sm">
                    <li><a href="{{ route('about') }}" class="text-slate-600 hover:text-slate-900">About</a></li>
                    <li><a href="{{ route('contact') }}" class="text-slate-600 hover:text-slate-900">Contact</a></li>
                    <li><a href="{{ route('privacy') }}" class="text-slate-600 hover:text-slate-900">Privacy</a></li>
                </ul>
            </div>
        </div>

        <div class="mt-12 border-t border-slate-100 pt-6 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-400">
            <span>© {{ date('Y') }} PrComet. All rights reserved.</span>
            <span>Discovery, not automation.</span>
        </div>
    </div>
</footer>
