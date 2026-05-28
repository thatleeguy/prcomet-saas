{{--
    Sidebar navigation.
    Desktop: fixed 256px rail. Mobile: hidden by default, slides in via Alpine.
--}}
<div x-data="{ open: false }" class="lg:contents">

    {{-- Mobile bar (visible only < lg) --}}
    <div class="lg:hidden fixed top-0 inset-x-0 z-40 h-14 bg-white border-b border-slate-200 flex items-center justify-between px-4">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
            <span class="h-6 w-6 rounded-full bg-slate-900"></span>
            <span class="font-semibold text-slate-900">PrComet</span>
        </a>
        <button @click="open = ! open" class="p-2 -mr-2 rounded-md text-slate-600 hover:bg-slate-100">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
    </div>

    {{-- Mobile backdrop --}}
    <div x-show="open" @click="open = false" x-cloak
         x-transition:enter="transition-opacity ease-out duration-200"
         x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-in duration-150"
         x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="lg:hidden fixed inset-0 z-40 bg-slate-900/40"></div>

    {{-- Sidebar --}}
    <aside :class="open ? 'translate-x-0' : '-translate-x-full'"
           class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-slate-200 flex flex-col
                  transition-transform duration-200 lg:translate-x-0">

        {{-- Brand --}}
        <div class="h-16 px-5 flex items-center border-b border-slate-200">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5">
                <span class="h-7 w-7 rounded-full bg-slate-900"></span>
                <span class="font-semibold text-slate-900 tracking-tight">PrComet</span>
            </a>
        </div>

        {{-- Team switcher --}}
        @if (Laravel\Jetstream\Jetstream::hasTeamFeatures())
            <div class="px-3 py-3 border-b border-slate-200">
                <x-dropdown align="left" width="56">
                    <x-slot name="trigger">
                        <button type="button" class="w-full flex items-center gap-2.5 px-3 py-2 rounded-md hover:bg-slate-50 transition-colors text-left">
                            <span class="grid place-items-center h-7 w-7 rounded-md bg-slate-100 text-slate-700 font-medium text-xs">
                                {{ strtoupper(substr(Auth::user()->currentTeam->name, 0, 2)) }}
                            </span>
                            <span class="flex-1 min-w-0">
                                <span class="block text-sm font-medium text-slate-900 truncate">{{ Auth::user()->currentTeam->name }}</span>
                                <span class="block text-xs text-slate-500">Workspace</span>
                            </span>
                            <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4" /></svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="px-4 py-2 text-xs font-medium text-slate-500 uppercase tracking-wider">Team</div>
                        <x-dropdown-link href="{{ route('teams.show', Auth::user()->currentTeam->id) }}">{{ __('Team settings') }}</x-dropdown-link>
                        @can('create', Laravel\Jetstream\Jetstream::newTeamModel())
                            <x-dropdown-link href="{{ route('teams.create') }}">{{ __('Create new team') }}</x-dropdown-link>
                        @endcan
                        @if (Auth::user()->allTeams()->count() > 1)
                            <div class="border-t border-slate-100 my-1"></div>
                            <div class="px-4 py-2 text-xs font-medium text-slate-500 uppercase tracking-wider">Switch</div>
                            @foreach (Auth::user()->allTeams() as $team)
                                <x-switchable-team :team="$team" />
                            @endforeach
                        @endif
                    </x-slot>
                </x-dropdown>
            </div>
        @endif

        {{-- Current company switcher.
             Resolved here once and reused below to render the company-scoped
             nav group. Shows even with a single company so the user always
             knows which company the workspace is scoped to. --}}
        @php
            $currentCompany = auth()->user()->resolveCurrentCompany();
            $teamCompanies = \App\Models\Company::where('team_id', Auth::user()->currentTeam->id)
                ->orderBy('name')->get();
        @endphp

        @if ($currentCompany)
            <div class="px-3 py-3 border-b border-slate-200">
                <div class="px-3 mb-1.5 text-[10px] font-semibold text-slate-400 uppercase tracking-wider">In focus</div>
                <div x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false" class="relative">
                    <button @click="open = !open" type="button"
                            class="w-full flex items-center gap-2.5 px-3 py-2 rounded-md bg-brand-50/60 border border-brand-200 hover:border-brand-300 transition-colors text-left">
                        <span class="grid place-items-center h-7 w-7 rounded-md bg-brand-600 text-white font-medium text-xs">
                            {{ strtoupper(substr($currentCompany->name, 0, 2)) }}
                        </span>
                        <span class="flex-1 min-w-0">
                            <span class="block text-sm font-medium text-slate-900 truncate">{{ $currentCompany->name }}</span>
                            <span class="block text-xs text-brand-700/80">Company</span>
                        </span>
                        <svg class="h-4 w-4 text-slate-400 transition-transform" :class="open && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4" /></svg>
                    </button>

                    <div x-show="open" x-cloak
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="absolute left-0 right-0 mt-2 rounded-lg bg-white shadow-lg ring-1 ring-slate-200 z-50 py-1.5"
                         @click="open = false">
                        <div class="px-3 py-1.5 text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Shortcuts</div>
                        <a href="{{ route('companies.show', $currentCompany) }}" wire:navigate
                           class="block px-3 py-2 text-sm text-slate-700 hover:bg-slate-50">Company page</a>
                        <a href="{{ route('companies.matches', $currentCompany) }}" wire:navigate
                           class="block px-3 py-2 text-sm text-slate-700 hover:bg-slate-50">Matches</a>

                        @if ($teamCompanies->count() > 1)
                            <div class="border-t border-slate-100 my-1"></div>
                            <div class="px-3 py-1.5 text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Switch focus</div>
                            @foreach ($teamCompanies as $c)
                                @if ($c->id !== $currentCompany->id)
                                    <form method="POST" action="{{ route('current-company.update') }}" class="block">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="company_id" value="{{ $c->id }}">
                                        <button type="submit"
                                                class="w-full text-left px-3 py-2 text-sm text-slate-700 hover:bg-slate-50 truncate">
                                            {{ $c->name }}
                                        </button>
                                    </form>
                                @endif
                            @endforeach
                        @endif

                        <div class="border-t border-slate-100 my-1"></div>
                        <a href="{{ route('companies.index') }}" wire:navigate
                           class="block px-3 py-2 text-sm text-slate-500 hover:bg-slate-50">All companies →</a>
                    </div>
                </div>
            </div>
        @endif

        {{-- Nav --}}
        @php
            $nav = [
                ['route' => 'dashboard',        'label' => 'Overview',  'pattern' => 'dashboard',         'icon' => 'home'],
                ['route' => 'companies.index',  'label' => 'Companies', 'pattern' => 'companies.index',   'icon' => 'building'],
                ['route' => 'matches.index',    'label' => 'Matches',   'pattern' => 'matches.*',         'icon' => 'sparkles'],
                ['route' => 'stream.index',     'label' => 'Stream',    'pattern' => 'stream.*',          'icon' => 'stream'],
                ['route' => 'wins.index',       'label' => 'Wins',      'pattern' => 'wins.*',            'icon' => 'trophy'],
            ];
            $icons = [
                'home'     => 'M3 12l9-9 9 9M5 10v10a1 1 0 001 1h3v-6h6v6h3a1 1 0 001-1V10',
                'building' => 'M4 21V7a2 2 0 012-2h12a2 2 0 012 2v14M4 21h16M9 7h6M9 11h6M9 15h6M9 21v-4h6v4',
                'sparkles' => 'M5 3v4M3 5h4M6 17v4M4 19h4M13 3l3 7 7 3-7 3-3 7-3-7-7-3 7-3 3-7z',
                'stream'   => 'M3 19a8 8 0 018-8M3 13a14 14 0 0114-14M5 19h.01',
                'trophy'   => 'M8 21h8M12 17v4M7 4h10v5a5 5 0 01-10 0V4zM7 4H4v2a3 3 0 003 3M17 4h3v2a3 3 0 01-3 3',
                'images'   => 'M4 7a2 2 0 012-2h12a2 2 0 012 2v10a2 2 0 01-2 2H6a2 2 0 01-2-2V7zM4 15l4-4 4 4 4-4 4 4M9 9a1 1 0 100-2 1 1 0 000 2z',
                'palette'  => 'M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10c1.1 0 2-.9 2-2 0-.51-.2-.97-.51-1.32-.3-.35-.49-.81-.49-1.31 0-1.1.9-2 2-2h2c2.76 0 5-2.24 5-5 0-4.96-4.48-9-10-9zM6.5 12a1.5 1.5 0 100-3 1.5 1.5 0 000 3zm3-4a1.5 1.5 0 100-3 1.5 1.5 0 000 3zm5 0a1.5 1.5 0 100-3 1.5 1.5 0 000 3zm3 4a1.5 1.5 0 100-3 1.5 1.5 0 000 3z',
            ];
        @endphp

        <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto">
            <div class="px-3 mb-2 text-xs font-medium text-slate-400 uppercase tracking-wider">Workspace</div>
            @foreach ($nav as $item)
                @php $active = request()->routeIs($item['pattern']); @endphp
                <a href="{{ route($item['route']) }}" wire:navigate
                   class="nav-link {{ $active ? 'nav-link-active' : '' }}">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <path d="{{ $icons[$item['icon']] }}" />
                    </svg>
                    <span class="flex-1">{{ $item['label'] }}</span>
                </a>
            @endforeach

            {{-- Company-scoped nav group: Library + Branding. Only shown when
                 a company is in focus; routes are pre-bound to that company
                 so a click never surprises you with the wrong context. --}}
            @if ($currentCompany)
                <div class="px-3 mb-2 mt-6 text-xs font-medium text-slate-400 uppercase tracking-wider">{{ \Illuminate\Support\Str::limit($currentCompany->name, 22) }}</div>
                <a href="{{ route('companies.library', $currentCompany) }}" wire:navigate
                   class="nav-link {{ request()->routeIs('companies.library') ? 'nav-link-active' : '' }}">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <path d="{{ $icons['images'] }}" />
                    </svg>
                    <span class="flex-1">Media library</span>
                </a>
                <a href="{{ route('companies.branding', $currentCompany) }}" wire:navigate
                   class="nav-link {{ request()->routeIs('companies.branding') ? 'nav-link-active' : '' }}">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <path d="{{ $icons['palette'] }}" />
                    </svg>
                    <span class="flex-1">Branding</span>
                </a>
            @endif

            <div class="px-3 mb-2 mt-6 text-xs font-medium text-slate-400 uppercase tracking-wider">Account</div>
            <a href="{{ route('settings.digest') }}" wire:navigate class="nav-link {{ request()->routeIs('settings.*') ? 'nav-link-active' : '' }}">
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <path d="M4 4h16v4l-6 6v6l-4-2v-4L4 8V4z" />
                </svg>
                <span>Notifications</span>
            </a>
            @if (Auth::user()->is_admin)
                <a href="{{ url('/admin') }}" class="nav-link">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <path d="M12 15a3 3 0 100-6 3 3 0 000 6zM19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 11-2.83 2.83l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 11-4 0v-.09a1.65 1.65 0 00-1-1.51 1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 11-2.83-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H3a2 2 0 110-4h.09a1.65 1.65 0 001.51-1 1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 112.83-2.83l.06.06a1.65 1.65 0 001.82.33h0a1.65 1.65 0 001-1.51V3a2 2 0 114 0v.09a1.65 1.65 0 001 1.51h0a1.65 1.65 0 001.82-.33l.06-.06a2 2 0 112.83 2.83l-.06.06a1.65 1.65 0 00-.33 1.82v0a1.65 1.65 0 001.51 1H21a2 2 0 110 4h-.09a1.65 1.65 0 00-1.51 1z" />
                </svg>
                <span>Admin</span>
            </a>
            @endif
        </nav>

        {{-- User menu (bottom) — opens upward because Jetstream's <x-dropdown>
             only opens downward, which would clip off-screen here. --}}
        <div class="p-3 border-t border-slate-200">
            <div x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false" class="relative">
                <button @click="open = !open" type="button" :class="open ? 'bg-slate-100' : 'hover:bg-slate-50'"
                        class="w-full flex items-center gap-2.5 px-2 py-2 rounded-md transition-colors text-left">
                    @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                        <img class="h-8 w-8 rounded-full object-cover" src="{{ Auth::user()->profile_photo_url }}" alt="">
                    @else
                        <span class="grid place-items-center h-8 w-8 rounded-full bg-slate-100 text-slate-700 font-medium text-xs">
                            {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                        </span>
                    @endif
                    <span class="flex-1 min-w-0">
                        <span class="block text-sm font-medium text-slate-900 truncate">{{ Auth::user()->name }}</span>
                        <span class="block text-xs text-slate-500 truncate">{{ Auth::user()->email }}</span>
                    </span>
                    <svg class="h-4 w-4 text-slate-400 transition-transform" :class="open && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4" /></svg>
                </button>

                <div x-show="open" x-cloak
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-100"
                     x-transition:leave-start="opacity-100 translate-y-0"
                     x-transition:leave-end="opacity-0 translate-y-1"
                     class="absolute bottom-full left-0 right-0 mb-2 origin-bottom rounded-lg bg-white shadow-lg ring-1 ring-slate-200 z-50 py-1.5"
                     @click="open = false">

                    <div class="px-3 py-1.5 text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Account</div>
                    <a href="{{ route('profile.show') }}" wire:navigate
                       class="flex items-center gap-2 px-3 py-2 text-sm text-slate-700 hover:bg-slate-50 hover:text-slate-900">
                        <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2M12 11a4 4 0 100-8 4 4 0 000 8z" /></svg>
                        Profile
                    </a>
                    <a href="{{ route('settings.digest') }}" wire:navigate
                       class="flex items-center gap-2 px-3 py-2 text-sm text-slate-700 hover:bg-slate-50 hover:text-slate-900">
                        <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M18 8a6 6 0 00-12 0c0 7-3 9-3 9h18s-3-2-3-9M13.7 21a2 2 0 01-3.4 0" /></svg>
                        Notifications
                    </a>

                    @if (Auth::user()->is_admin)
                        <div class="border-t border-slate-100 my-1"></div>
                        <a href="{{ url('/admin') }}"
                           class="flex items-center gap-2 px-3 py-2 text-sm text-slate-700 hover:bg-slate-50 hover:text-slate-900">
                            <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M3 7l9 6 9-6M3 7v10a2 2 0 002 2h14a2 2 0 002-2V7M3 7l9-4 9 4" /></svg>
                            Admin panel
                        </a>
                    @endif

                    <div class="border-t border-slate-100 my-1"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="w-full flex items-center gap-2 px-3 py-2 text-sm text-slate-700 hover:bg-rose-50 hover:text-rose-700">
                            <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9" /></svg>
                            Sign out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </aside>
</div>
