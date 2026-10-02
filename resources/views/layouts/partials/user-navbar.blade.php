<header x-data="navbar()" x-init="init()" x-cloak
    class="fixed top-0 left-0 right-0 z-50 transition-colors duration-300"
    :class="scrolled ? 'bg-white/95 backdrop-blur-md shadow-sm border-b border-gray-100' : 'bg-transparent'">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 lg:h-[72px]">

            {{-- ── Left: Logo --}}
            <a href="{{ route('welcome') }}" class="flex items-center gap-2.5 flex-shrink-0 group">
                <div
                    class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0 overflow-hidden bg-gray-900">

                    @if (setting('app_logo'))
                        <img src="{{ Storage::url(setting('app_logo')) }}" alt="{{ setting('app_name', 'TicketIn') }}"
                            class="w-full h-full object-cover">
                    @else
                        <svg class="w-5 h-5 fill-white" viewBox="0 0 24 24">
                            <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" />
                        </svg>
                    @endif

                </div>
                <span
                    class="text-base font-bold text-gray-900 tracking-tight group-hover:text-gray-700 transition-colors duration-200">
                    {{ setting('app_name', config('app.name')) }}
                </span>
            </a>

            {{-- ── Center: Nav Links --}}
            <div class="hidden lg:flex items-center justify-center flex-1 min-w-0 overflow-hidden transition-[max-width] duration-300"
                :class="searchExpanded ? 'max-w-xs xl:max-w-md' : 'max-w-2xl'">
                <nav class="flex items-center gap-1 w-full justify-center">
                    @foreach ([['route' => 'welcome', 'pattern' => 'welcome', 'label' => 'Home'], ['route' => 'events.index', 'pattern' => 'events*', 'label' => 'Event'], ['route' => 'blogs.index', 'pattern' => 'blogs*', 'label' => 'Blog'], ['route' => 'faqs.index', 'pattern' => 'faqs*', 'label' => 'FAQ'], ['route' => 'about.index', 'pattern' => 'about*', 'label' => 'About']] as $nav)
                        @php

                            $isActive = request()->routeIs($nav['pattern']);
                        @endphp

                        <a href="{{ route($nav['route']) }}"
                            class="relative px-4 py-2 text-sm font-medium rounded-xl whitespace-nowrap transition-colors duration-20 {{ $isActive ? 'text-gray-900 bg-gray-100' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50' }}">
                            {{ $nav['label'] }}

                            @if ($isActive)
                                <span
                                    class="absolute bottom-0.5 left-1/2 -translate-x-1/2 w-1 h-1 bg-gray-900 rounded-full"></span>
                            @endif
                        </a>
                    @endforeach
                    <a href="{{ route('cinema.index') }}"
                        class="flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-50 transition-all duration-150 {{ request()->routeIs('cinema.*') ? 'bg-gray-100 text-gray-900 font-semibold' : '' }}">
                        <i class="ti ti-movie text-base"></i>
                        Bioskop
                    </a>
                </nav>
            </div>

            {{-- ── Right Side: Tools & Auth --}}
            <div class="flex items-center gap-2 flex-shrink-0 justify-end">

                {{-- Desktop Search --}}
                <div class="hidden lg:flex items-center flex-shrink-0" @click.outside="collapseSearch()">
                    {{-- Search Button --}}
                    <template x-if="!searchExpanded">
                        <div>
                            <button @click="expandSearch()" type="button"
                                class="w-9 h-9 border border-gray-200 rounded-xl flex items-center justify-center
                                text-gray-400 hover:bg-gray-50 hover:text-gray-700 hover:border-gray-300 transition-colors duration-200">
                                <i class="ti ti-search text-base"></i>
                            </button>
                        </div>
                    </template>

                    {{-- Expanded Search --}}
                    <template x-if="searchExpanded">
                        <div class="relative">
                            <form action="{{ route('events.index') }}" method="GET" autocomplete="off">
                                <div
                                    class="flex items-center gap-2 bg-gray-50 border border-gray-200 focus-within:border-gray-400 focus-within:bg-white rounded-xl px-3 h-10 w-72">
                                    <i class="ti ti-search text-gray-400 text-sm flex-shrink-0"></i>
                                    <input x-ref="desktopSearch" type="text" name="search" autocomplete="off"
                                        autocorrect="off" autocapitalize="off" spellcheck="false"
                                        placeholder="Cari event, konser, seminar..."
                                        class="bg-transparent border-none outline-none focus:outline-none focus:ring-0 text-sm text-gray-700 placeholder-gray-400 w-full min-w-0">
                                    <button type="button" @click="collapseSearch()"
                                        class="text-gray-300 hover:text-gray-500 transition">
                                        <i class="ti ti-x text-sm"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </template>
                </div>

                {{-- Divider --}}
                <div class="hidden lg:block w-px h-5 bg-gray-200"></div>

                {{-- Guest State --}}
                @guest
                    <div class="hidden lg:flex items-center gap-2">
                        <a href="{{ route('login') }}"
                            class="h-9 px-4 text-sm font-medium text-gray-600 rounded-xl flex items-center justify-center hover:bg-gray-50 hover:text-gray-900 transition-colors duration-200">
                            Masuk
                        </a>
                        <a href="{{ route('register') }}"
                            class="h-9 px-4 bg-gray-900 text-white text-sm font-medium rounded-xl flex items-center justify-center hover:bg-gray-700 transition-colors duration-200">
                            Daftar
                        </a>
                    </div>
                @endguest

                {{-- Auth State --}}
                @auth
                    {{-- Profile Dropdown (Flat Modern - No Shadow) --}}
                    <div class="hidden lg:block relative" x-data="{ open: false }" @click.outside="open = false">
                        <button @click="open = !open" type="button"
                            class="flex items-center gap-2.5 h-10 pl-2 pr-3.5 border border-gray-200 rounded-xl bg-white hover:bg-gray-50 hover:border-gray-400 transition-all duration-200 group">
                            @if (auth()->user()->avatar)
                                <img src="{{ Storage::url(auth()->user()->avatar) }}"
                                    class="w-6 h-6 rounded-full object-cover flex-shrink-0 ring-1 ring-gray-100"
                                    alt="">
                            @else
                                <div
                                    class="w-6 h-6 rounded-full bg-gray-900 flex items-center justify-center text-white text-[10px] font-bold flex-shrink-0 tracking-tight">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                                </div>
                            @endif

                            <span
                                class="text-sm font-semibold text-gray-700 max-w-[100px] truncate group-hover:text-gray-900 transition-colors duration-150">
                                {{ auth()->user()->name }}
                            </span>

                            <i class="ti ti-chevron-down text-gray-400 text-sm transition-transform duration-200 group-hover:text-gray-600"
                                :class="open ? 'rotate-180 text-gray-900' : ''"></i>
                        </button>

                        {{-- Dropdown Menu (Flat Design) --}}
                        <div x-show="open" x-transition x-cloak
                            class="absolute right-0 top-[calc(100%+6px)] w-56 bg-white border border-gray-200 rounded-xl overflow-hidden z-50">

                            <div class="px-4 py-3.5 bg-gray-50/70 border-b border-gray-100">
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-0.5">Akun Saya</p>
                                <p class="text-sm font-bold text-gray-900 truncate">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-gray-500 truncate mt-0.5">{{ auth()->user()->email }}</p>
                            </div>

                            <div class="p-1.5 space-y-0.5">
                                @foreach ([['route' => 'user.dashboard', 'icon' => 'ti-layout-dashboard', 'label' => 'Dashboard'], ['route' => 'user.tickets.index', 'icon' => 'ti-ticket', 'label' => 'Tiket Saya'], ['route' => 'user.orders.index', 'icon' => 'ti-shopping-cart', 'label' => 'Pesanan Saya'], ['route' => 'user.refunds.index', 'icon' => 'ti-refresh', 'label' => 'Refund Saya'], ['route' => 'user.profile.edit', 'icon' => 'ti-user', 'label' => 'Profile']] as $item)
                                    <a href="{{ route($item['route']) }}"
                                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-150 group/item
                                        {{ request()->routeIs($item['route']) ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                                        <i
                                            class="ti {{ $item['icon'] }} text-base transition-colors duration-150
                                            {{ request()->routeIs($item['route']) ? 'text-white' : 'text-gray-400 group-hover/item:text-gray-900' }}"></i>
                                        <span>{{ $item['label'] }}</span>
                                    </a>
                                @endforeach
                            </div>

                            <div class="p-1.5 border-t border-gray-100 bg-gray-50/30">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit"
                                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-red-500 hover:bg-red-50 hover:text-red-600 transition-all duration-150 group/btn">
                                        <i class="ti ti-logout text-base text-red-400 group-hover/btn:text-red-600"></i>
                                        <span>Logout</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endauth

                {{-- Mobile: Search Trigger --}}
                <button @click="mobileSearch = !mobileSearch; if(mobileSearch) mobileMenu = false"
                    class="lg:hidden w-9 h-9 border border-gray-200 rounded-xl flex items-center justify-center text-gray-500 hover:bg-gray-50 transition-colors duration-200">
                    <i class="ti ti-search text-base"></i>
                </button>

                {{-- Mobile: Hamburger Menu --}}
                <button @click="mobileMenu = !mobileMenu; if(mobileMenu) mobileSearch = false"
                    class="lg:hidden w-9 h-9 border border-gray-200 rounded-xl flex items-center justify-center text-gray-500 hover:bg-gray-50 transition-colors duration-200">
                    <i class="ti text-base transition-transform duration-200"
                        :class="mobileMenu ? 'ti-x' : 'ti-menu-2'"></i>
                </button>

            </div>
        </div>

        {{-- Mobile Search Dropdown --}}
        <div x-show="mobileSearch" x-transition x-cloak class="lg:hidden pb-3">
            <form action="{{ route('events.index') }}" method="GET" autocomplete="off">
                <div
                    class="flex items-center gap-2 bg-gray-50 border border-gray-200 focus-within:border-gray-400 focus-within:bg-white rounded-xl px-3 h-11">
                    <i class="ti ti-search text-gray-400 text-sm flex-shrink-0"></i>
                    <input type="text" name="search" autocomplete="off"
                        placeholder="Cari event, konser, seminar..."
                        class="bg-transparent border-none outline-none text-sm text-gray-700 placeholder-gray-400 w-full">
                </div>
            </form>
        </div>
    </div>

    {{-- Mobile Menu Dropdown --}}
    <div x-show="mobileMenu" x-transition x-cloak
        class="lg:hidden border-t border-gray-100 bg-white/95 backdrop-blur-md">
        <div class="max-w-7xl mx-auto px-4 py-3 space-y-0.5">
            @foreach ([['route' => 'welcome', 'icon' => 'ti-home', 'label' => 'Home'], ['route' => 'events.index', 'icon' => 'ti-ticket', 'label' => 'Jelajahi Tiket'], ['route' => 'blogs.index', 'icon' => 'ti-news', 'label' => 'Blog'], ['route' => 'faqs.index', 'icon' => 'ti-help-circle', 'label' => 'FAQ']] as $nav)
                <a href="{{ route($nav['route']) }}" @click="mobileMenu = false"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-colors duration-150
                    {{ request()->routeIs($nav['route']) ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <i class="ti {{ $nav['icon'] }} text-base"></i>
                    {{ $nav['label'] }}
                </a>
            @endforeach
        </div>
    </div>

</header>

<div class="h-16 lg:h-[72px]"></div>

@push('scripts')
    <script>
        function navbar() {
            return {
                scrolled: false,
                mobileMenu: false,
                mobileSearch: false,
                searchExpanded: false,

                init() {
                    this.handleScroll()
                    window.addEventListener('scroll', () => {
                        this.handleScroll()
                    })
                },

                handleScroll() {
                    this.scrolled = window.scrollY > 10
                },

                expandSearch() {
                    this.searchExpanded = true
                    this.$nextTick(() => {
                        this.$refs.desktopSearch?.focus()
                    })
                },

                collapseSearch() {
                    this.searchExpanded = false
                }
            }
        }
    </script>
@endpush
