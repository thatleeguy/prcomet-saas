<x-filament-panels::page>
    @php
        $statusMeta = [
            'ok'      => ['color' => 'success', 'label' => 'OK'],
            'warn'    => ['color' => 'warning', 'label' => 'Warning'],
            'fail'    => ['color' => 'danger',  'label' => 'Failing'],
            'unknown' => ['color' => 'gray',    'label' => 'Unknown'],
        ];
        $checkMeta = [
            'scheduler' => ['title' => 'Scheduler',    'subtitle' => 'php artisan schedule:run (every minute)'],
            'queue'     => ['title' => 'Queue worker', 'subtitle' => 'php artisan queue:work'],
            'database'  => ['title' => 'Database',     'subtitle' => 'Primary DB connection'],
            'storage'   => ['title' => 'Storage',      'subtitle' => 'Default filesystem disk'],
            'anthropic' => ['title' => 'Anthropic',    'subtitle' => 'Claude API key'],
        ];
    @endphp

    {{-- Scoped CSS so layout doesn't depend on the workspace Tailwind
         build — Filament's own CSS bundle doesn't see this file's
         classes. Everything visual on this page works without any
         compile-time dependency on Tailwind. --}}
    <style>
        .sys-health-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        @media (min-width: 768px) {
            .sys-health-grid { grid-template-columns: 1fr 1fr; }
        }
        .sys-health-card {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }
        .sys-health-row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 0.75rem;
        }
        .sys-health-title { font-size: 0.875rem; font-weight: 600; }
        .sys-health-subtitle { font-size: 0.75rem; color: rgb(107 114 128); margin-top: 0.125rem; }
        .sys-health-message { font-size: 0.8125rem; line-height: 1.45; color: rgb(75 85 99); margin: 0; }
        .dark .sys-health-message { color: rgb(156 163 175); }
        .sys-health-meta { font-size: 0.75rem; color: rgb(107 114 128); font-family: ui-monospace, SFMono-Regular, monospace; }
        .sys-health-queue-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.5rem;
            padding-top: 0.75rem;
            border-top: 1px solid rgb(229 231 235);
        }
        .dark .sys-health-queue-stats { border-top-color: rgb(255 255 255 / 0.05); }
        .sys-health-stat-num { font-size: 1.125rem; font-weight: 600; font-variant-numeric: tabular-nums; }
        .sys-health-stat-num.danger { color: rgb(225 29 72); }
        .sys-health-stat-label { font-size: 0.625rem; color: rgb(107 114 128); text-transform: uppercase; letter-spacing: 0.05em; }
        .sys-health-disclosure summary {
            cursor: pointer;
            font-size: 0.6875rem;
            font-weight: 600;
            color: rgb(55 65 81);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            list-style: none;
            display: flex;
            align-items: center;
            gap: 0.375rem;
            user-select: none;
        }
        .sys-health-disclosure summary::-webkit-details-marker { display: none; }
        .sys-health-disclosure summary svg { transition: transform 0.15s ease; flex-shrink: 0; }
        .sys-health-disclosure[open] summary svg { transform: rotate(90deg); }
        .sys-health-setup-block { margin-top: 0.5rem; }
        .sys-health-setup-label {
            font-size: 0.625rem;
            font-weight: 600;
            color: rgb(107 114 128);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.375rem;
        }
        .sys-health-code {
            font-family: ui-monospace, SFMono-Regular, monospace;
            font-size: 0.6875rem;
            line-height: 1.4;
            background: rgb(17 24 39);
            color: rgb(243 244 246);
            border-radius: 0.375rem;
            padding: 0.75rem;
            overflow-x: auto;
            white-space: pre;
            margin: 0;
        }
        .sys-health-forge-note {
            font-size: 0.6875rem;
            line-height: 1.5;
            color: rgb(107 114 128);
            margin-top: 0.5rem;
        }
        .sys-health-forge-note strong { color: rgb(55 65 81); }
        .dark .sys-health-forge-note strong { color: rgb(209 213 219); }
        .sys-health-failure-row { padding: 0.75rem 0; }
        .sys-health-failure-row + .sys-health-failure-row { border-top: 1px solid rgb(229 231 235); }
        .dark .sys-health-failure-row + .sys-health-failure-row { border-top-color: rgb(255 255 255 / 0.05); }
        .sys-health-failure-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            font-size: 0.6875rem;
            color: rgb(107 114 128);
            margin-bottom: 0.25rem;
        }
        .sys-health-failure-id {
            font-family: ui-monospace, monospace;
            font-size: 0.625rem;
            color: rgb(156 163 175);
            max-width: 180px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .sys-health-failure-exception {
            font-size: 0.8125rem;
            font-family: ui-monospace, monospace;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
    </style>

    <div class="sys-health-grid">
        @foreach ($checks as $key => $check)
            @php $meta = $statusMeta[$check['status']] ?? $statusMeta['unknown']; @endphp

            <x-filament::section>
                <div class="sys-health-card">

                    <div class="sys-health-row">
                        <div>
                            <div class="sys-health-title">{{ $checkMeta[$key]['title'] ?? $key }}</div>
                            <div class="sys-health-subtitle">{{ $checkMeta[$key]['subtitle'] ?? '' }}</div>
                        </div>
                        <x-filament::badge :color="$meta['color']">
                            {{ $meta['label'] }}
                        </x-filament::badge>
                    </div>

                    <p class="sys-health-message">{{ $check['message'] }}</p>

                    @if (! empty($check['last_at']))
                        <div class="sys-health-meta">
                            Last beat: {{ \Carbon\Carbon::parse($check['last_at'])->toIso8601String() }}
                        </div>
                    @endif

                    @if ($key === 'queue')
                        <div class="sys-health-queue-stats">
                            <div>
                                <div class="sys-health-stat-num">{{ $check['pending'] ?? 0 }}</div>
                                <div class="sys-health-stat-label">Pending</div>
                            </div>
                            <div>
                                <div class="sys-health-stat-num danger">{{ $check['failed'] ?? 0 }}</div>
                                <div class="sys-health-stat-label">Failed</div>
                            </div>
                        </div>
                    @endif

                    @if (! empty($check['setup']))
                        <details class="sys-health-disclosure" @if ($check['status'] !== 'ok') open @endif>
                            <summary>
                                {{-- Hard-sized inline so the SVG never balloons. --}}
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 5l7 7-7 7"/></svg>
                                Setup commands
                            </summary>

                            @if (! empty($check['setup']['env']))
                                <div class="sys-health-setup-block">
                                    <div class="sys-health-setup-label">Environment variables</div>
                                    <pre class="sys-health-code">{{ implode("\n", $check['setup']['env']) }}</pre>
                                </div>
                            @endif

                            @if (! empty($check['setup']['commands']))
                                <div class="sys-health-setup-block">
                                    <div class="sys-health-setup-label">Commands</div>
                                    <pre class="sys-health-code">{{ implode("\n", $check['setup']['commands']) }}</pre>
                                </div>
                            @endif

                            @if (! empty($check['setup']['forge_notes']))
                                <p class="sys-health-forge-note">
                                    <strong>Forge.</strong> {{ $check['setup']['forge_notes'] }}
                                </p>
                            @endif
                        </details>
                    @endif
                </div>
            </x-filament::section>
        @endforeach
    </div>

    {{-- Recent failures panel --}}
    <div style="margin-top: 1.5rem;">
        <x-filament::section>
            <x-slot name="heading">Recent failed jobs</x-slot>
            <x-slot name="description">Last {{ count($recentFailures) }} of {{ $failedJobs }}</x-slot>

            @if (empty($recentFailures))
                <p style="font-size: 0.875rem; color: rgb(107 114 128); text-align: center; padding: 1rem 0; margin: 0;">No failed jobs.</p>
            @else
                <ul style="list-style: none; padding: 0; margin: 0;">
                    @foreach ($recentFailures as $f)
                        <li class="sys-health-failure-row">
                            <div class="sys-health-failure-meta">
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <x-filament::badge color="gray">{{ $f['queue'] ?? 'default' }}</x-filament::badge>
                                    @if ($f['failed_at'])
                                        <span>{{ \Carbon\Carbon::parse($f['failed_at'])->diffForHumans() }}</span>
                                    @endif
                                </div>
                                <span class="sys-health-failure-id" title="{{ $f['id'] }}">{{ $f['id'] }}</span>
                            </div>
                            <div class="sys-health-failure-exception">{{ $f['exception'] }}</div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-filament::section>
    </div>
</x-filament-panels::page>
