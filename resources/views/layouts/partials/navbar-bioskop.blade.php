<nav class="bg-gray-950 border-b border-white/5 sticky top-0 z-50" x-data="cinemaNavbar()" x-init="init()">

    {{-- Top bar: info cinema --}}
    <div class="border-b border-white/5 bg-black/30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-8">

                {{-- Kiri: info --}}
                <div class="flex items-center gap-4 text-[11px] text-gray-500">
                    <span class="flex items-center gap-1.5">
                        <i class="ti ti-clock text-gray-600 text-xs"></i>
                        Buka 09.00 — 24.00 WIB
                    </span>
                    <span class="hidden sm:flex items-center gap-1.5">
                        <i class="ti ti-phone text-gray-600 text-xs"></i>
                        {{ setting('app_phone', '021-XXXXXXX') }}
                    </span>
                </div>

                {{-- Kanan: sosial media --}}
                <div class="flex items-center gap-3">
                    @foreach ([['key' => 'social_instagram', 'icon' => 'ti-brand-instagram', 'href' => 'https://instagram.com/'], ['key' => 'social_twitter', 'icon' => 'ti-brand-x', 'href' => 'https://twitter.com/'], ['key' => 'social_facebook', 'icon' => 'ti-brand-facebook', 'href' => 'https://facebook.com/'], ['key' => 'social_youtube', 'icon' => 'ti-brand-youtube', 'href' => 'https://youtube.com/'], ['key' => 'social_tiktok', 'icon' => 'ti-brand-tiktok', 'href' => 'https://tiktok.com/@']] as $social)
                        @if (setting($social['key']))
                            <a href="{{ $social['href'] . setting($social['key']) }}" target="_blank"
                                rel="noopener noreferrer"
                                class="text-gray-600 hover:text-white transition-colors duration-200">
                                <i class="ti {{ $social['icon'] }} text-sm"></i>
                            </a>
                        @endif
                    @endforeach
                </div>

            </div>
        </div>
    </div>

    {{-- Main Navbar --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 gap-6">

            {{-- Logo + Nama  --}}
            <a href="{{ route('cinema.index') }}" class="flex items-center gap-3 flex-shrink-0 group">
                <div
                    class="w-9 h-9 bg-red-600 rounded-xl flex items-center justify-center
                    group-hover:bg-red-500 transition-colors duration-200 flex-shrink-0">
                    @if (setting('app_logo'))
                        <img src="{{ Storage::url(setting('app_logo')) }}" class="w-5 h-5 object-contain"
                            alt="Logo">
                    @else
                        <i class="ti ti-movie text-white text-lg"></i>
                    @endif
                </div>
                <div class="leading-tight">
                    <p class="text-sm font-bold text-white tracking-tight leading-none">
                        {{ setting('app_name', config('app.name')) }}
                    </p>
                    <p
                        class="text-[10px] text-red-500 font-semibold tracking-widest
                        uppercase leading-none mt-0.5">
                        Cinema
                    </p>
                </div>
            </a>

            {{-- Navigasi Desktop --}}
            <nav class="hidden lg:flex items-center gap-1 flex-1 justify-center">
                @foreach ([['route' => 'cinema.index', 'label' => 'Beranda', 'icon' => 'ti-home'], ['route' => 'cinema.movies', 'label' => 'Sedang Tayang', 'icon' => 'ti-movie'], ['route' => 'cinema.coming', 'label' => 'Segera Hadir', 'icon' => 'ti-calendar-event'], ['route' => 'cinema.cinemas', 'label' => 'Bioskop', 'icon' => 'ti-building']] as $nav)
                    <a href="{{ route($nav['route']) }}"
                        class="flex items-center gap-2 px-4 h-9 rounded-xl text-sm font-medium
                        transition-all duration-200
                        {{ request()->routeIs($nav['route'])
                            ? 'bg-red-600/20 text-red-400'
                            : 'text-gray-400 hover:text-white hover:bg-white/5' }}">
                        <i class="ti {{ $nav['icon'] }} text-base"></i>
                        {{ $nav['label'] }}
                    </a>
                @endforeach
            </nav>

            {{-- Actions --}}
            <div class="flex items-center gap-2 flex-shrink-0">

                {{-- Search --}}
                <div class="relative hidden md:block" x-data="cinemaSearch()">
                    <div class="flex items-center gap-2 bg-white/5 border border-white/10
                        rounded-xl px-3 h-9 w-44 transition-all duration-200"
                        :class="focused ? 'border-white/30 bg-white/10 w-56' : ''">
                        <i class="ti ti-search text-gray-500 text-sm flex-shrink-0"></i>
                        <input type="text" x-model="query" x-ref="searchInput" @focus="focused = true"
                            @blur="setTimeout(() => focused = false, 200)" @input.debounce.300ms="search()"
                            @keydown.escape="close()" placeholder="Cari film..."
                            class="bg-transparent border-none outline-none text-sm
                                text-white placeholder-gray-500 w-full">
                        <button x-show="query.length > 0" @click="clearSearch()"
                            class="text-gray-500 hover:text-white transition flex-shrink-0">
                            <i class="ti ti-x text-xs"></i>
                        </button>
                    </div>

                    {{-- Search Results Dropdown --}}
                    <div x-show="open && results.length > 0" x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 -translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        class="absolute top-full right-0 mt-2 w-72 bg-gray-900 border
                            border-white/10 rounded-2xl shadow-2xl z-50 overflow-hidden">
                        <div class="p-2">
                            <p
                                class="text-[10px] font-semibold text-gray-500 uppercase
                                tracking-wider px-3 py-2">
                                Hasil Pencarian
                            </p>
                            <template x-for="movie in results" :key="movie.id">
                                <a :href="movie.url"
                                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl
                                        hover:bg-white/5 transition-colors duration-150 group">
                                    <div
                                        class="w-10 h-14 bg-gray-800 rounded-lg overflow-hidden
                                        flex-shrink-0">
                                        <img x-show="movie.poster" :src="movie.poster"
                                            class="w-full h-full object-cover" alt="">
                                        <div x-show="!movie.poster"
                                            class="w-full h-full flex items-center justify-center">
                                            <i class="ti ti-movie text-gray-600 text-lg"></i>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold text-white truncate
                                            leading-snug"
                                            x-text="movie.title"></p>
                                        <p class="text-xs text-gray-500 mt-0.5" x-text="movie.genre"></p>
                                        <span
                                            class="text-[10px] px-2 py-0.5 rounded-full
                                            font-semibold inline-block mt-1"
                                            :class="movie.status === 'now_showing' ?
                                                'bg-emerald-500/20 text-emerald-400' :
                                                'bg-amber-500/20 text-amber-400'"
                                            x-text="movie.status === 'now_showing'
                                                ? 'Sedang Tayang'
                                                : 'Segera Hadir'">
                                        </span>
                                    </div>
                                </a>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Tiket saya --}}
                @auth
                    <a href="{{ route('cinema.my-tickets') }}"
                        class="hidden sm:flex items-center gap-2 h-9 px-3 border border-white/10
                        text-gray-400 text-sm font-medium rounded-xl hover:bg-white/5
                        hover:text-white hover:border-white/20 transition-all duration-200">
                        <i class="ti ti-ticket text-base"></i>
                        <span class="hidden md:block">Tiket Saya</span>
                    </a>

                    {{-- User avatar dropdown --}}
                    <div class="relative" x-data="{ open: false }" @click.away="open = false">
                        <button @click="open = !open"
                            class="w-9 h-9 rounded-full bg-red-600/20 border border-red-500/30
                            flex items-center justify-center text-red-400 text-xs font-bold
                            hover:bg-red-600/30 transition-all duration-200">
                            {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                        </button>

                        <div x-show="open" x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 scale-95 translate-y-1"
                            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                            class="absolute right-0 top-full mt-2 w-52 bg-gray-900 border
                            border-white/10 rounded-2xl shadow-2xl z-50 overflow-hidden py-1">

                            {{-- User info --}}
                            <div class="px-4 py-3 border-b border-white/5">
                                <p class="text-sm font-semibold text-white truncate">
                                    {{ auth()->user()->name }}
                                </p>
                                <p class="text-xs text-gray-500 truncate">
                                    {{ auth()->user()->email }}
                                </p>
                            </div>

                            <div class="py-1">
                                @foreach ([['href' => route('cinema.my-tickets'), 'icon' => 'ti-ticket', 'label' => 'Tiket Saya'], ['href' => route('cinema.my-orders'), 'icon' => 'ti-shopping-cart', 'label' => 'Riwayat Order'], ['href' => route('user.profile.edit'), 'icon' => 'ti-user', 'label' => 'Profil'], ['href' => route('user.dashboard'), 'icon' => 'ti-layout-dashboard', 'label' => 'Dashboard']] as $item)
                                    <a href="{{ $item['href'] }}"
                                        class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-400
                                    hover:text-white hover:bg-white/5 transition-all duration-150">
                                        <i class="ti {{ $item['icon'] }} text-base w-4 text-center"></i>
                                        {{ $item['label'] }}
                                    </a>
                                @endforeach

                                <div class="border-t border-white/5 mt-1 pt-1">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit"
                                            class="w-full flex items-center gap-3 px-4 py-2.5 text-sm
                                            text-red-400 hover:text-red-300 hover:bg-red-500/10
                                            transition-all duration-150">
                                            <i class="ti ti-logout text-base w-4 text-center"></i>
                                            Keluar
                                        </button>
                                    </form>
                                </div>
                            </div>

                        </div>
                    </div>
                @else
                    {{-- Login & Register button --}}
                    <a href="{{ route('login') }}"
                        class="h-9 px-4 text-sm font-medium text-gray-400 hover:text-white
                        transition-colors duration-200">
                        Masuk
                    </a>
                    <a href="{{ route('register') }}"
                        class="h-9 px-4 bg-red-600 hover:bg-red-500 text-white text-sm
                        font-semibold rounded-xl transition-all duration-200
                        flex items-center gap-1.5">
                        <i class="ti ti-user-plus text-sm"></i>
                        Daftar
                    </a>
                @endauth

                {{-- Mobile menu button --}}
                <button @click="mobileOpen = !mobileOpen"
                    class="lg:hidden w-9 h-9 border border-white/10 rounded-xl
                        flex items-center justify-center text-gray-400
                        hover:text-white hover:bg-white/5 transition">
                    <i class="ti text-base" :class="mobileOpen ? 'ti-x' : 'ti-menu-2'"></i>
                </button>

            </div>

        </div>
    </div>

    {{-- Mobile Menu --}}
    <div x-show="mobileOpen" x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
        class="lg:hidden border-t border-white/5 bg-black/30">

        <div class="max-w-7xl mx-auto px-4 py-3 space-y-1">

            {{-- Mobile search --}}
            <div class="relative mb-3">
                <div
                    class="flex items-center gap-2 bg-white/5 border border-white/10
                    rounded-xl px-3 h-10">
                    <i class="ti ti-search text-gray-500 text-sm"></i>
                    <input type="text" placeholder="Cari film..."
                        class="bg-transparent border-none outline-none text-sm text-white
                            placeholder-gray-500 w-full">
                </div>
            </div>

            @foreach ([['route' => 'cinema.index', 'label' => 'Beranda', 'icon' => 'ti-home'], ['route' => 'cinema.movies', 'label' => 'Sedang Tayang', 'icon' => 'ti-movie'], ['route' => 'cinema.coming', 'label' => 'Segera Hadir', 'icon' => 'ti-calendar-event'], ['route' => 'cinema.cinemas', 'label' => 'Bioskop', 'icon' => 'ti-building']] as $nav)
                <a href="{{ route($nav['route']) }}"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium
                    transition-all duration-150
                    {{ request()->routeIs($nav['route'])
                        ? 'bg-red-600/20 text-red-400'
                        : 'text-gray-400 hover:text-white hover:bg-white/5' }}">
                    <i class="ti {{ $nav['icon'] }} text-base"></i>
                    {{ $nav['label'] }}
                </a>
            @endforeach

            @auth
                <div class="border-t border-white/5 pt-3 mt-3 space-y-1">
                    <a href="{{ route('cinema.my-tickets') }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm text-gray-400
                        hover:text-white hover:bg-white/5 transition-all duration-150">
                        <i class="ti ti-ticket text-base"></i>
                        Tiket Saya
                    </a>
                </div>
            @endauth

        </div>
    </div>

    {{-- Now Playing Ticker --}}
    @if ($nowPlayingMovies ?? false)
        <div class="bg-red-600/10 border-t border-red-500/20 overflow-hidden">
            <div class="flex items-center h-8">
                <div class="flex-shrink-0 bg-red-600 px-4 h-full flex items-center">
                    <span
                        class="text-white text-[10px] font-bold uppercase tracking-widest
                    whitespace-nowrap flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 bg-white rounded-full animate-pulse"></span>
                        Now Playing
                    </span>
                </div>
                <div class="overflow-hidden flex-1 mx-4">
                    <div class="flex gap-8 animate-marquee whitespace-nowrap">
                        @foreach ($nowPlayingMovies as $movie)
                            <a href="{{ route('cinema.movies.show', $movie->slug) }}"
                                class="text-[11px] text-gray-400 hover:text-white transition
                            flex items-center gap-2">
                                <i class="ti ti-movie text-red-500 text-xs"></i>
                                {{ $movie->title }}
                                <span class="text-gray-600">·</span>
                                <span class="text-gray-500">{{ $movie->duration_formatted }}</span>
                            </a>
                        @endforeach
                        {{-- Duplikat untuk loop seamless --}}
                        @foreach ($nowPlayingMovies as $movie)
                            <a href="{{ route('cinema.movies.show', $movie->slug) }}"
                                class="text-[11px] text-gray-400 hover:text-white transition
                            flex items-center gap-2">
                                <i class="ti ti-movie text-red-500 text-xs"></i>
                                {{ $movie->title }}
                                <span class="text-gray-600">·</span>
                                <span class="text-gray-500">{{ $movie->duration_formatted }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif

</nav>

@push('scripts')
    <script>
        function cinemaNavbar() {
            return {
                mobileOpen: false,

                init() {
                    // Tutup mobile menu saat resize ke desktop
                    window.addEventListener('resize', () => {
                        if (window.innerWidth >= 1024) {
                            this.mobileOpen = false;
                        }
                    });
                }
            }
        }

        function cinemaSearch() {
            return {
                query: '',
                results: [],
                open: false,
                focused: false,

                async search() {
                    if (this.query.length < 2) {
                        this.results = [];
                        this.open = false;
                        return;
                    }

                    try {
                        const res = await fetch(
                            `{{ route('cinema.search') }}?q=${encodeURIComponent(this.query)}`, {
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            }
                        );
                        const data = await res.json();
                        this.results = data.movies ?? [];
                        this.open = this.results.length > 0;
                    } catch (e) {
                        console.error('Cinema search error:', e);
                    }
                },

                clearSearch() {
                    this.query = '';
                    this.results = [];
                    this.open = false;
                    this.$refs.searchInput?.focus();
                },

                close() {
                    this.open = false;
                }
            }
        }
    </script>
@endpush

@push('styles')
    <style>
        @keyframes marquee {
            0% {
                transform: translateX(0);
            }

            100% {
                transform: translateX(-50%);
            }
        }

        .animate-marquee {
            animation: marquee 30s linear infinite;
        }

        .animate-marquee:hover {
            animation-play-state: paused;
        }
    </style>
@endpush
