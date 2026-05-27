<nav x-data="{ open: false }" class="bg-paper/80 backdrop-blur-md border-b border-hairline sticky top-0 z-30">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <!-- Wordmark + Primary nav -->
            <div class="flex items-center gap-10">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 group">
                    <!-- Comet glyph: a tiny ascending vector with a tail -->
                    <span class="relative inline-block h-7 w-7">
                        <span class="absolute inset-0 rounded-full bg-gradient-to-br from-accent to-accent-deeper"></span>
                        <span class="absolute -right-0.5 -top-0.5 h-1.5 w-1.5 rounded-full bg-paper"></span>
                        <span class="absolute inset-x-0 -bottom-1 h-px bg-gradient-to-r from-transparent via-accent-soft to-transparent"></span>
                    </span>
                    <span class="font-display text-xl tracking-tight leading-none">
                        <span class="font-semibold text-ink">Pr</span><span class="italic font-light text-accent">Comet</span>
                    </span>
                </a>

                <div class="hidden md:flex items-center gap-1">
                    @php
                        $nav = [
                            ['href' => route('dashboard'),         'label' => 'Brief',     'pattern' => 'dashboard'],
                            ['href' => route('companies.index'),   'label' => 'Companies', 'pattern' => 'companies.*'],
                            ['href' => route('matches.index'),     'label' => 'Matches',   'pattern' => 'matches.*'],
                            ['href' => route('wins.index'),        'label' => 'Wins',      'pattern' => 'wins.*'],
                        ];
                    @endphp
                    @foreach ($nav as $item)
                        @php $active = request()->routeIs($item['pattern']); @endphp
                        <a href="{{ $item['href'] }}"
                            class="relative px-3 py-2 font-mono text-[11px] uppercase tracking-caps transition-colors
                                   {{ $active ? 'text-ink' : 'text-ink-soft hover:text-ink' }}">
                            {{ $item['label'] }}
                            @if ($active)
                                <span class="absolute left-3 right-3 -bottom-px h-px bg-accent"></span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Right cluster: team chip + user menu -->
            <div class="hidden md:flex items-center gap-3">
                @if (Laravel\Jetstream\Jetstream::hasTeamFeatures())
                    <x-dropdown align="right" width="60">
                        <x-slot name="trigger">
                            <button type="button" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full border border-hairline bg-paper hover:border-ink-soft transition-colors">
                                <span class="h-1.5 w-1.5 rounded-full bg-signal-go"></span>
                                <span class="font-mono text-[11px] uppercase tracking-caps text-ink-soft">
                                    {{ Auth::user()->currentTeam->name }}
                                </span>
                                <svg class="size-3 text-ink-muted" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                </svg>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <div class="w-60">
                                <div class="px-4 py-2 eyebrow">Team</div>
                                <x-dropdown-link href="{{ route('teams.show', Auth::user()->currentTeam->id) }}">
                                    {{ __('Team Settings') }}
                                </x-dropdown-link>
                                @can('create', Laravel\Jetstream\Jetstream::newTeamModel())
                                    <x-dropdown-link href="{{ route('teams.create') }}">
                                        {{ __('Create New Team') }}
                                    </x-dropdown-link>
                                @endcan
                                @if (Auth::user()->allTeams()->count() > 1)
                                    <div class="border-t border-hairline my-1"></div>
                                    <div class="px-4 py-2 eyebrow">Switch</div>
                                    @foreach (Auth::user()->allTeams() as $team)
                                        <x-switchable-team :team="$team" />
                                    @endforeach
                                @endif
                            </div>
                        </x-slot>
                    </x-dropdown>
                @endif

                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                            <button class="flex border border-hairline rounded-full overflow-hidden hover:border-ink-soft transition-colors">
                                <img class="size-8 object-cover" src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" />
                            </button>
                        @else
                            <button type="button" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full border border-hairline hover:border-ink-soft transition-colors">
                                <span class="size-6 rounded-full bg-ink text-paper grid place-items-center font-mono text-[11px]">
                                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                </span>
                                <span class="font-mono text-[11px] uppercase tracking-caps text-ink-soft">
                                    {{ Str::limit(Auth::user()->name, 16, '') }}
                                </span>
                            </button>
                        @endif
                    </x-slot>

                    <x-slot name="content">
                        <div class="px-4 py-2 eyebrow">Account</div>
                        <x-dropdown-link href="{{ route('profile.show') }}">{{ __('Profile') }}</x-dropdown-link>
                        <x-dropdown-link href="{{ route('settings.digest') }}">{{ __('Digest preferences') }}</x-dropdown-link>
                        @if (Auth::user()->is_admin)
                            <div class="border-t border-hairline my-1"></div>
                            <x-dropdown-link href="{{ url('/admin') }}">{{ __('Admin panel') }}</x-dropdown-link>
                        @endif
                        <div class="border-t border-hairline my-1"></div>
                        <form method="POST" action="{{ route('logout') }}" x-data>
                            @csrf
                            <x-dropdown-link href="{{ route('logout') }}" @click.prevent="$root.submit();">
                                {{ __('Sign out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center md:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-ink-soft hover:text-ink hover:bg-paper-deep focus:outline-none transition">
                    <svg class="size-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden md:hidden border-t border-hairline">
        <div class="px-4 py-3 space-y-1">
            @foreach ($nav as $item)
                <a href="{{ $item['href'] }}" class="block px-3 py-2 font-mono text-[11px] uppercase tracking-caps {{ request()->routeIs($item['pattern']) ? 'text-accent' : 'text-ink-soft' }}">
                    {{ $item['label'] }}
                </a>
            @endforeach
        </div>
        <div class="px-4 py-3 border-t border-hairline space-y-1">
            <a href="{{ route('profile.show') }}" class="block px-3 py-2 text-sm">Profile</a>
            <a href="{{ route('settings.digest') }}" class="block px-3 py-2 text-sm">Digest preferences</a>
            @if (Auth::user()->is_admin)
                <a href="{{ url('/admin') }}" class="block px-3 py-2 text-sm">Admin panel</a>
            @endif
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="block w-full text-left px-3 py-2 text-sm">Sign out</button>
            </form>
        </div>
    </div>
</nav>
