<x-filament-panels::page>
    @php
        $statusStyle = [
            'ok'      => ['dot' => 'bg-emerald-500', 'chip' => 'bg-emerald-50 text-emerald-700 ring-emerald-200', 'label' => 'OK'],
            'warn'    => ['dot' => 'bg-amber-500',   'chip' => 'bg-amber-50 text-amber-700 ring-amber-200',     'label' => 'Warning'],
            'fail'    => ['dot' => 'bg-rose-500',    'chip' => 'bg-rose-50 text-rose-700 ring-rose-200',         'label' => 'Failing'],
            'unknown' => ['dot' => 'bg-slate-400',   'chip' => 'bg-slate-100 text-slate-600 ring-slate-200',     'label' => 'Unknown'],
        ];
        $checkMeta = [
            'scheduler' => ['title' => 'Scheduler',    'subtitle' => 'php artisan schedule:run (every minute)'],
            'queue'     => ['title' => 'Queue worker', 'subtitle' => 'php artisan queue:work'],
            'database'  => ['title' => 'Database',     'subtitle' => 'Primary DB connection'],
            'storage'   => ['title' => 'Storage',      'subtitle' => 'Default filesystem disk'],
            'anthropic' => ['title' => 'Anthropic',    'subtitle' => 'Claude API key'],
        ];
    @endphp

    <div class="space-y-6">

        {{-- Top-line check cards.
             Each card renders the status, the freshness message, and a
             "Setup commands" disclosure with the env vars + shell
             commands needed to configure that subsystem. The disclosure
             is open by default when the check is not green so the fix
             is one glance away from the failure. --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach ($checks as $key => $check)
                @php $style = $statusStyle[$check['status']] ?? $statusStyle['unknown']; @endphp
                <div class="rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-900 p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $checkMeta[$key]['title'] ?? $key }}</div>
                            <div class="text-[11px] text-gray-500 mt-0.5">{{ $checkMeta[$key]['subtitle'] ?? '' }}</div>
                        </div>
                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full ring-1 text-[11px] font-medium uppercase tracking-wider {{ $style['chip'] }}">
                            <span class="h-1.5 w-1.5 rounded-full {{ $style['dot'] }}"></span>
                            {{ $style['label'] }}
                        </span>
                    </div>

                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-3 leading-relaxed">{{ $check['message'] }}</p>

                    @if (! empty($check['last_at']))
                        <div class="text-[11px] text-gray-500 mt-2 font-mono">
                            Last beat: {{ \Carbon\Carbon::parse($check['last_at'])->toIso8601String() }}
                        </div>
                    @endif

                    @if ($key === 'queue')
                        <div class="grid grid-cols-2 gap-2 mt-3 pt-3 border-t border-gray-100 dark:border-white/5">
                            <div>
                                <div class="text-lg font-semibold tabular text-gray-900 dark:text-gray-100">{{ $check['pending'] ?? 0 }}</div>
                                <div class="text-[10px] text-gray-500 uppercase tracking-wider">Pending</div>
                            </div>
                            <div>
                                <div class="text-lg font-semibold tabular text-rose-600">{{ $check['failed'] ?? 0 }}</div>
                                <div class="text-[10px] text-gray-500 uppercase tracking-wider">Failed</div>
                            </div>
                        </div>
                    @endif

                    {{-- Setup recipe. <details> gives us native disclosure
                         with no JS; the `open` attribute is conditional on
                         status so the operator sees the fix immediately
                         when something's wrong. --}}
                    @if (! empty($check['setup']))
                        <details class="mt-4 group" @if ($check['status'] !== 'ok') open @endif>
                            <summary class="cursor-pointer text-[11px] font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider flex items-center gap-1.5 select-none">
                                <svg class="h-3 w-3 text-gray-400 transition-transform group-open:rotate-90" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                                Setup commands
                            </summary>

                            <div class="mt-3 space-y-3">
                                @if (! empty($check['setup']['env']))
                                    <div>
                                        <div class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Environment variables</div>
                                        <pre class="text-[11px] leading-snug font-mono bg-gray-900 text-gray-100 dark:bg-gray-950 rounded-md p-3 overflow-x-auto whitespace-pre">{{ implode("\n", $check['setup']['env']) }}</pre>
                                    </div>
                                @endif

                                @if (! empty($check['setup']['commands']))
                                    <div>
                                        <div class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Commands</div>
                                        <pre class="text-[11px] leading-snug font-mono bg-gray-900 text-gray-100 dark:bg-gray-950 rounded-md p-3 overflow-x-auto whitespace-pre">{{ implode("\n", $check['setup']['commands']) }}</pre>
                                    </div>
                                @endif

                                @if (! empty($check['setup']['forge_notes']))
                                    <p class="text-[11px] text-gray-500 leading-relaxed">
                                        <span class="inline-flex items-center gap-1 font-semibold text-gray-700 dark:text-gray-300">
                                            <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                                            Forge
                                        </span>
                                        {{ $check['setup']['forge_notes'] }}
                                    </p>
                                @endif
                            </div>
                        </details>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Recent failures --}}
        <div class="rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-900 overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100 dark:border-white/5 flex items-center justify-between">
                <div>
                    <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">Recent failed jobs</div>
                    <div class="text-[11px] text-gray-500">Last {{ count($recentFailures) }} of {{ $failedJobs }}</div>
                </div>
            </div>

            @if (empty($recentFailures))
                <div class="px-5 py-8 text-center text-sm text-gray-500">
                    No failed jobs. 🎉
                </div>
            @else
                <ul class="divide-y divide-gray-100 dark:divide-white/5">
                    @foreach ($recentFailures as $f)
                        <li class="px-5 py-3">
                            <div class="flex items-start justify-between gap-3 text-[11px] text-gray-500">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-gray-100 dark:bg-white/5 text-gray-700 dark:text-gray-300 font-medium uppercase tracking-wider">{{ $f['queue'] ?? 'default' }}</span>
                                    @if ($f['failed_at'])
                                        <span>{{ \Carbon\Carbon::parse($f['failed_at'])->diffForHumans() }}</span>
                                    @endif
                                </div>
                                <span class="font-mono text-[10px] text-gray-400 truncate max-w-[180px]" title="{{ $f['id'] }}">{{ $f['id'] }}</span>
                            </div>
                            <div class="text-sm text-gray-900 dark:text-gray-100 mt-1 font-mono truncate">{{ $f['exception'] }}</div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</x-filament-panels::page>
