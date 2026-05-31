<footer class="border-t border-slate-200 mt-16">
    <div class="max-w-5xl mx-auto px-6 py-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-sm text-slate-500">
        <div class="flex items-center gap-2">
            <span class="h-5 w-5 rounded-full bg-slate-900"></span>
            <span class="font-medium text-slate-700">PrComet</span>
        </div>
        <div class="flex items-center gap-6">
            <a href="{{ route('articles.index') }}" class="hover:text-slate-900">The Angle</a>
            <a href="{{ route('home') }}" class="hover:text-slate-900">Home</a>
        </div>
        <div class="text-xs text-slate-400">© {{ date('Y') }} PrComet · Discovery, not automation.</div>
    </div>
</footer>
