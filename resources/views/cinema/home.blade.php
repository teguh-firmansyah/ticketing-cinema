<x-cinema-layout>
    <x-slot name="title">{{ setting('app_name', config('app.name')) }} Cinema — Beli Tiket Bioskop Online</x-slot>

    {{-- Hero Section --}}
    <section class="relative min-h-[85vh] flex items-center overflow-hidden bg-gray-950" x-data="heroSlider({{ $featured->count() }})"
        x-init="init()">

        {{-- Backdrop slides --}}
        @foreach ($featured as $i => $movie)
            <div class="absolute inset-0 transition-all duration-1000"
                :class="{{ $i }} === active ? 'opacity-100' : 'opacity-0'">
                <img src="{{ $movie->backdrop_url }}" class="w-full h-full object-cover scale-105"
                    :class="{{ $i }} === active ? 'scale-100 transition-transform duration-[8000ms]' : ''"
                    alt="{{ $movie->title }}">
                <div class="absolute inset-0 bg-gradient-to-r from-gray-950 via-gray-950/75 to-transparent"></div>
                <div class="absolute inset-0 bg-gradient-to-t from-gray-950 via-transparent to-gray-950/30"></div>
            </div>
        @endforeach

        {{-- Grid overlay --}}
        <div class="absolute inset-0 opacity-[0.03]"
            style="background-image: linear-gradient(rgba(255,255,255,.5) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255,255,255,.5) 1px, transparent 1px);
            background-size: 60px 60px;">
        </div>

        {{-- Content --}}
        <div class="relative w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">

                {{-- Left: Text --}}
                <div>
                    @foreach ($featured as $i => $movie)
                        <div class="transition-all duration-700"
                            :class="{{ $i }} === active ?
                                'opacity-100 translate-y-0' :
                                'opacity-0 translate-y-6 absolute pointer-events-none'">

                            {{-- Badges --}}
                            <div class="flex flex-wrap items-center gap-2 mb-5">
                                <span
                                    class="inline-flex items-center gap-1.5 bg-red-600 text-white
                            text-xs font-bold px-3 py-1.5 rounded-full">
                                    <span class="w-1.5 h-1.5 bg-white rounded-full animate-pulse"></span>
                                    Sedang Tayang
                                </span>
                                @foreach (array_slice($movie->genres ?? [], 0, 2) as $genre)
                                    <span
                                        class="bg-white/10 text-white/70 text-xs font-medium
                            px-3 py-1.5 rounded-full border border-white/10">
                                        {{ $genre }}
                                    </span>
                                @endforeach
                                <span
                                    class="border text-xs font-bold px-2.5 py-1 rounded-lg
                            {{ $movie->age_rating === 'SU' ? 'border-green-500/50 text-green-400 bg-green-900/20' : '' }}
                            {{ $movie->age_rating === '13+' ? 'border-blue-500/50 text-blue-400 bg-blue-900/20' : '' }}
                            {{ $movie->age_rating === '17+' ? 'border-amber-500/50 text-amber-400 bg-amber-900/20' : '' }}
                            {{ $movie->age_rating === '21+' ? 'border-red-500/50 text-red-400 bg-red-900/20' : '' }}">
                                    {{ $movie->age_rating }}
                                </span>
                            </div>

                            {{-- Title --}}
                            <h1
                                class="text-4xl sm:text-5xl lg:text-6xl font-black text-white
                        leading-[1.1] tracking-tight mb-4">
                                {{ $movie->title }}
                            </h1>

                            {{-- Meta --}}
                            <div class="flex flex-wrap items-center gap-3 mb-5 text-sm text-gray-400">
                                @if ($movie->vote_average)
                                    <span class="flex items-center gap-1.5 text-amber-400 font-semibold">
                                        <i class="ti ti-star-filled text-sm"></i>
                                        {{ $movie->vote_average }}/10
                                    </span>
                                    <span class="text-gray-700">·</span>
                                @endif
                                <span>{{ $movie->duration_formatted }}</span>
                                @if ($movie->director)
                                    <span class="text-gray-700">·</span>
                                    <span>{{ $movie->director }}</span>
                                @endif
                            </div>

                            {{-- Synopsis --}}
                            <p
                                class="text-gray-400 text-base leading-relaxed mb-8
                        line-clamp-3 max-w-xl">
                                {{ $movie->synopsis }}
                            </p>

                            {{-- CTAs --}}
                            <div class="flex flex-wrap items-center gap-3">
                                <a href="{{ route('cinema.movies.show', $movie->slug) }}"
                                    class="inline-flex items-center gap-2.5 h-13 px-8 bg-red-600
                                hover:bg-red-500 text-white font-bold text-sm rounded-2xl
                                transition-all duration-200 shadow-lg shadow-red-600/30
                                hover:shadow-red-500/40 hover:-translate-y-0.5">
                                    <i class="ti ti-ticket text-lg"></i>
                                    Pesan Tiket Sekarang
                                </a>
                                @if ($movie->hasTrailer())
                                    <a href="{{ $movie->trailer_url }}" target="_blank"
                                        class="inline-flex items-center gap-2.5 h-13 px-6
                                bg-white/10 hover:bg-white/20 border border-white/10
                                hover:border-white/20 text-white font-medium text-sm
                                rounded-2xl transition-all duration-200 backdrop-blur-sm">
                                        <div
                                            class="w-7 h-7 bg-white rounded-full flex items-center
                                justify-center flex-shrink-0">
                                            <i
                                                class="ti ti-player-play text-gray-900 text-xs
                                    ml-0.5"></i>
                                        </div>
                                        Tonton Trailer
                                    </a>
                                @endif
                            </div>

                            {{-- Cast (preview) --}}
                            @if ($movie->cast && count($movie->cast) > 0)
                                <div class="mt-6 flex items-center gap-3">
                                    <div class="flex -space-x-2">
                                        @foreach (array_slice($movie->cast, 0, 4) as $actor)
                                            <div
                                                class="w-8 h-8 rounded-full bg-gradient-to-br
                                from-gray-700 to-gray-800 border-2 border-gray-900
                                flex items-center justify-center text-[10px]
                                font-bold text-white flex-shrink-0">
                                                {{ strtoupper(substr($actor, 0, 1)) }}
                                            </div>
                                        @endforeach
                                    </div>
                                    <p class="text-xs text-gray-500">
                                        {{ implode(', ', array_slice($movie->cast, 0, 3)) }}
                                    </p>
                                </div>
                            @endif

                        </div>
                    @endforeach
                </div>

                {{-- Right: Poster + floating cards --}}
                <div class="hidden lg:flex justify-center items-center relative">

                    @foreach ($featured as $i => $movie)
                        <div class="absolute inset-0 flex justify-center items-center
                    transition-all duration-700"
                            :class="{{ $i }} === active ?
                                'opacity-100 scale-100' :
                                'opacity-0 scale-95 pointer-events-none'">

                            {{-- Poster --}}
                            <div class="relative">
                                <div
                                    class="w-64 xl:w-72 rounded-3xl overflow-hidden
                            shadow-2xl shadow-black/60 border border-white/10">
                                    <img src="{{ $movie->poster_url }}" class="w-full aspect-[2/3] object-cover"
                                        alt="{{ $movie->title }}">
                                </div>

                                {{-- Floating: rating card --}}
                                @if ($movie->vote_average)
                                    <div
                                        class="absolute -left-12 top-8 bg-gray-900/95 backdrop-blur-xl
                            border border-white/10 rounded-2xl px-4 py-3 shadow-xl
                            min-w-[110px]">
                                        <p class="text-[10px] text-gray-500 mb-1">Rating IMDb</p>
                                        <div class="flex items-center gap-1.5">
                                            <i class="ti ti-star-filled text-amber-400 text-base"></i>
                                            <span class="text-white font-black text-lg leading-none">
                                                {{ $movie->vote_average }}
                                            </span>
                                            <span class="text-gray-600 text-xs">/10</span>
                                        </div>
                                    </div>
                                @endif

                                {{-- Floating: duration --}}
                                <div
                                    class="absolute -right-10 top-1/2 -translate-y-1/2
                            bg-gray-900/95 backdrop-blur-xl border border-white/10
                            rounded-2xl px-4 py-3 shadow-xl">
                                    <p class="text-[10px] text-gray-500 mb-1">Durasi</p>
                                    <div class="flex items-center gap-1.5">
                                        <i class="ti ti-clock text-blue-400 text-sm"></i>
                                        <span class="text-white font-bold text-sm">
                                            {{ $movie->duration_formatted }}
                                        </span>
                                    </div>
                                </div>

                                {{-- Floating: book now --}}
                                <div
                                    class="absolute -bottom-5 left-1/2 -translate-x-1/2
                            bg-red-600 rounded-2xl px-5 py-3 shadow-xl
                            shadow-red-600/40 w-52 text-center">
                                    <p class="text-white font-bold text-sm">Beli Tiket</p>
                                    <p class="text-red-200 text-[10px] mt-0.5">
                                        Tersedia di {{ $stats['cinemas'] }} bioskop
                                    </p>
                                </div>

                                {{-- Glow --}}
                                <div
                                    class="absolute inset-0 bg-red-600/20 blur-3xl rounded-full
                            -z-10 scale-150">
                                </div>
                            </div>
                        </div>
                    @endforeach

                </div>
            </div>

            {{-- Slide controls --}}
            @if ($featured->count() > 1)
                <div class="absolute bottom-8 left-1/2 -translate-x-1/2 flex items-center gap-3">
                    <button @click="prev()"
                        class="w-8 h-8 border border-white/20 rounded-full flex items-center
                    justify-center text-white/50 hover:text-white hover:border-white/40
                    transition-all duration-200">
                        <i class="ti ti-chevron-left text-sm"></i>
                    </button>
                    <div class="flex items-center gap-1.5">
                        @foreach ($featured as $i => $movie)
                            <button @click="goTo({{ $i }})" class="rounded-full transition-all duration-300"
                                :class="{{ $i }} === active ?
                                    'bg-red-500 w-6 h-2' :
                                    'bg-white/30 w-2 h-2 hover:bg-white/50'">
                            </button>
                        @endforeach
                    </div>
                    <button @click="next()"
                        class="w-8 h-8 border border-white/20 rounded-full flex items-center
                    justify-center text-white/50 hover:text-white hover:border-white/40
                    transition-all duration-200">
                        <i class="ti ti-chevron-right text-sm"></i>
                    </button>
                </div>
            @endif

        </div>
    </section>

    {{-- Stats Bar --}}
    <div class="bg-gray-900 border-y border-white/5">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-3 divide-x divide-white/5">
                @foreach ([['icon' => 'ti-movie', 'value' => $stats['movies'], 'label' => 'Film Tersedia', 'color' => 'text-red-500'], ['icon' => 'ti-building', 'value' => $stats['cinemas'], 'label' => 'Bioskop Partner', 'color' => 'text-blue-400'], ['icon' => 'ti-map-pin', 'value' => $stats['cities'], 'label' => 'Kota di Indonesia', 'color' => 'text-emerald-400']] as $stat)
                    <div class="flex items-center justify-center gap-4 py-5">
                        <div
                            class="w-10 h-10 bg-white/5 rounded-xl flex items-center
                    justify-center flex-shrink-0">
                            <i class="ti {{ $stat['icon'] }} {{ $stat['color'] }} text-xl"></i>
                        </div>
                        <div>
                            <p class="text-xl font-black text-white">{{ $stat['value'] }}+</p>
                            <p class="text-xs text-gray-500">{{ $stat['label'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Now Showing --}}
    <section class="py-14">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Section header --}}
            <div class="flex items-end justify-between mb-7">
                <div>
                    <p class="text-xs font-semibold text-red-500 uppercase tracking-widest mb-1">
                        Sedang Tayang
                    </p>
                    <h2 class="text-2xl font-black text-white">Film Terbaru di Bioskop</h2>
                </div>
                <a href="{{ route('cinema.movies') }}"
                    class="hidden sm:flex items-center gap-2 text-sm font-medium text-gray-500
                    hover:text-white transition-colors duration-200 group">
                    Lihat Semua
                    <i
                        class="ti ti-arrow-right text-base group-hover:translate-x-0.5
                    transition-transform duration-200"></i>
                </a>
            </div>

            {{-- Movies scroll --}}
            <div class="relative">
                <div class="flex gap-4 overflow-x-auto pb-3 scrollbar-hide
                snap-x snap-mandatory"
                    id="now-showing-scroll">
                    @foreach ($nowShowing as $movie)
                        <a href="{{ route('cinema.movies.show', $movie->slug) }}"
                            class="group flex-shrink-0 w-40 sm:w-44 snap-start">

                            {{-- Poster --}}
                            <div
                                class="relative aspect-[2/3] bg-gray-800 rounded-2xl
                        overflow-hidden mb-3 border border-white/5
                        group-hover:border-red-500/30 group-hover:shadow-xl
                        group-hover:shadow-red-500/10 transition-all duration-300">
                                <img src="{{ $movie->poster_url }}" alt="{{ $movie->title }}" loading="lazy"
                                    class="w-full h-full object-cover group-hover:scale-105
                                transition-transform duration-500">

                                {{-- Hover overlay --}}
                                <div
                                    class="absolute inset-0 bg-gradient-to-t from-black/90
                            via-black/0 to-black/0 opacity-0 group-hover:opacity-100
                            transition-opacity duration-300 flex items-end p-3">
                                    <div
                                        class="w-full h-9 bg-red-600 rounded-xl flex items-center
                                justify-center gap-1.5 text-white text-xs font-bold">
                                        <i class="ti ti-ticket text-sm"></i>
                                        Beli Tiket
                                    </div>
                                </div>

                                {{-- Rating --}}
                                @if ($movie->vote_average)
                                    <div
                                        class="absolute top-2 left-2 flex items-center gap-1
                            bg-black/70 backdrop-blur-sm text-amber-400 text-[10px]
                            font-bold px-2 py-0.5 rounded-lg">
                                        <i class="ti ti-star-filled text-[10px]"></i>
                                        {{ $movie->vote_average }}
                                    </div>
                                @endif

                                {{-- Age rating --}}
                                <div
                                    class="absolute top-2 right-2 text-[9px] font-bold
                            px-1.5 py-0.5 rounded border
                            {{ $movie->age_rating === 'SU' ? 'bg-green-900/80 border-green-500/50 text-green-400' : '' }}
                            {{ $movie->age_rating === '13+' ? 'bg-blue-900/80 border-blue-500/50 text-blue-400' : '' }}
                            {{ $movie->age_rating === '17+' ? 'bg-amber-900/80 border-amber-500/50 text-amber-400' : '' }}
                            {{ $movie->age_rating === '21+' ? 'bg-red-900/80 border-red-500/50 text-red-400' : '' }}">
                                    {{ $movie->age_rating }}
                                </div>
                            </div>

                            {{-- Info --}}
                            <h3
                                class="text-sm font-bold text-white line-clamp-2 leading-snug
                        mb-1 group-hover:text-red-400 transition-colors duration-200">
                                {{ $movie->title }}
                            </h3>
                            <p class="text-xs text-gray-600 truncate">
                                {{ $movie->genres_string }}
                            </p>

                        </a>
                    @endforeach

                    {{-- See all card --}}
                    <a href="{{ route('cinema.movies') }}" class="flex-shrink-0 w-40 sm:w-44 snap-start group">
                        <div
                            class="aspect-[2/3] bg-gray-900 border border-dashed border-white/10
                        rounded-2xl flex flex-col items-center justify-center gap-3
                        group-hover:border-red-500/30 group-hover:bg-red-600/5
                        transition-all duration-300">
                            <div
                                class="w-12 h-12 bg-white/5 group-hover:bg-red-600/20
                            rounded-xl flex items-center justify-center
                            transition-colors duration-300">
                                <i
                                    class="ti ti-arrow-right text-gray-600
                                group-hover:text-red-400 text-xl
                                transition-colors duration-300"></i>
                            </div>
                            <p
                                class="text-xs text-gray-600 group-hover:text-gray-400
                            font-medium text-center transition-colors duration-300">
                                Lihat Semua<br>Film
                            </p>
                        </div>
                    </a>
                </div>

                {{-- Scroll buttons --}}
                <button onclick="document.getElementById('now-showing-scroll').scrollBy({left:-220,behavior:'smooth'})"
                    class="absolute -left-4 top-1/2 -translate-y-8 w-9 h-9
                    bg-gray-900 border border-white/10 rounded-full
                    flex items-center justify-center text-white/50
                    hover:text-white hover:border-white/30 transition-all duration-200
                    shadow-xl hidden sm:flex">
                    <i class="ti ti-chevron-left text-sm"></i>
                </button>
                <button onclick="document.getElementById('now-showing-scroll').scrollBy({left:220,behavior:'smooth'})"
                    class="absolute -right-4 top-1/2 -translate-y-8 w-9 h-9
                    bg-gray-900 border border-white/10 rounded-full
                    flex items-center justify-center text-white/50
                    hover:text-white hover:border-white/30 transition-all duration-200
                    shadow-xl hidden sm:flex">
                    <i class="ti ti-chevron-right text-sm"></i>
                </button>
            </div>

        </div>
    </section>

    {{-- Coming Soon --}}
    @if ($comingSoon->count() > 0)
        <section class="py-14 bg-gray-900/50 border-y border-white/5">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                <div class="flex items-end justify-between mb-7">
                    <div>
                        <p class="text-xs font-semibold text-amber-500 uppercase tracking-widest mb-1">
                            Segera Hadir
                        </p>
                        <h2 class="text-2xl font-black text-white">Film yang Akan Datang</h2>
                    </div>
                    <a href="{{ route('cinema.movies', ['status' => 'coming_soon']) }}"
                        class="hidden sm:flex items-center gap-2 text-sm font-medium text-gray-500
                    hover:text-white transition-colors duration-200 group">
                        Lihat Semua
                        <i
                            class="ti ti-arrow-right text-base group-hover:translate-x-0.5
                    transition-transform duration-200"></i>
                    </a>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach ($comingSoon as $movie)
                        <a href="{{ route('cinema.movies.show', $movie->slug) }}"
                            class="group flex gap-4 bg-gray-900 border border-white/5 rounded-2xl p-4
                    hover:border-amber-500/30 hover:bg-amber-500/5
                    transition-all duration-300">

                            {{-- Poster --}}
                            <div
                                class="w-20 aspect-[2/3] bg-gray-800 rounded-xl overflow-hidden
                    flex-shrink-0 border border-white/5">
                                <img src="{{ $movie->poster_url }}" alt="{{ $movie->title }}" loading="lazy"
                                    class="w-full h-full object-cover group-hover:scale-105
                            transition-transform duration-500">
                            </div>

                            {{-- Info --}}
                            <div class="flex-1 min-w-0 flex flex-col justify-between py-0.5">
                                <div>
                                    <span
                                        class="text-[10px] font-bold bg-amber-500/10 text-amber-400
                            border border-amber-500/20 px-2 py-0.5 rounded-full
                            inline-block mb-2">
                                        Segera Hadir
                                    </span>
                                    <h3
                                        class="text-sm font-bold text-white line-clamp-2 leading-snug
                            mb-1.5 group-hover:text-amber-400 transition-colors duration-200">
                                        {{ $movie->title }}
                                    </h3>
                                    <p class="text-xs text-gray-600 truncate mb-2">
                                        {{ $movie->genres_string }}
                                    </p>
                                </div>

                                <div class="space-y-1.5">
                                    @if ($movie->release_date)
                                        <div class="flex items-center gap-2 text-xs text-amber-400">
                                            <i class="ti ti-calendar text-xs flex-shrink-0"></i>
                                            {{ $movie->release_date->translatedFormat('d F Y') }}
                                        </div>
                                    @endif
                                    @if ($movie->duration)
                                        <div class="flex items-center gap-2 text-xs text-gray-600">
                                            <i class="ti ti-clock text-xs flex-shrink-0"></i>
                                            {{ $movie->duration_formatted }}
                                        </div>
                                    @endif
                                </div>
                            </div>

                        </a>
                    @endforeach
                </div>

            </div>
        </section>
    @endif

    {{-- How It Works --}}
    <section class="py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="text-center mb-12">
                <p class="text-xs font-semibold text-red-500 uppercase tracking-widest mb-2">
                    Cara Kerja
                </p>
                <h2 class="text-2xl font-black text-white mb-3">
                    Pesan Tiket dalam 4 Langkah
                </h2>
                <p class="text-gray-500 text-sm max-w-md mx-auto">
                    Proses pemesanan yang mudah, cepat, dan aman.
                    Selesai dalam hitungan menit!
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                @foreach ([
        [
            'step' => '01',
            'icon' => 'ti-movie',
            'title' => 'Pilih Film',
            'desc' => 'Browse film yang sedang tayang atau segera hadir. Cari berdasarkan genre atau rating.',
            'color' => 'from-red-600/20 to-red-600/5',
            'icon_color' => 'text-red-400',
            'icon_bg' => 'bg-red-600/20',
        ],
        [
            'step' => '02',
            'icon' => 'ti-calendar',
            'title' => 'Pilih Jadwal',
            'desc' => 'Pilih tanggal, jam tayang, dan bioskop yang paling dekat dengan lokasi kamu.',
            'color' => 'from-blue-600/20 to-blue-600/5',
            'icon_color' => 'text-blue-400',
            'icon_bg' => 'bg-blue-600/20',
        ],
        [
            'step' => '03',
            'icon' => 'ti-armchair',
            'title' => 'Pilih Kursi',
            'desc' => 'Pilih kursi favorit dari denah interaktif. Lihat kursi mana yang tersedia secara real-time.',
            'color' => 'from-violet-600/20 to-violet-600/5',
            'icon_color' => 'text-violet-400',
            'icon_bg' => 'bg-violet-600/20',
        ],
        [
            'step' => '04',
            'icon' => 'ti-qrcode',
            'title' => 'Bayar & Masuk',
            'desc' => 'Bayar via berbagai metode. QR code tiket otomatis dikirim ke email dan tersedia di akun.',
            'color' => 'from-emerald-600/20 to-emerald-600/5',
            'icon_color' => 'text-emerald-400',
            'icon_bg' => 'bg-emerald-600/20',
        ],
    ] as $i => $step)
                    <div class="relative group">
                        <div
                            class="bg-gray-900 border border-white/5 rounded-2xl p-6
                    hover:border-white/10 transition-all duration-300
                    hover:-translate-y-1 hover:shadow-xl hover:shadow-black/40">

                            {{-- Step number --}}
                            <div class="flex items-center justify-between mb-5">
                                <div
                                    class="w-12 h-12 {{ $step['icon_bg'] }} rounded-2xl
                            flex items-center justify-center">
                                    <i class="ti {{ $step['icon'] }} {{ $step['icon_color'] }} text-xl"></i>
                                </div>
                                <span
                                    class="text-3xl font-black text-white/5
                            group-hover:text-white/10 transition-colors duration-300">
                                    {{ $step['step'] }}
                                </span>
                            </div>

                            <h3 class="text-base font-bold text-white mb-2">
                                {{ $step['title'] }}
                            </h3>
                            <p class="text-sm text-gray-500 leading-relaxed">
                                {{ $step['desc'] }}
                            </p>
                        </div>

                        {{-- Arrow connector (desktop) --}}
                        @if ($i < 3)
                            <div class="hidden lg:flex absolute -right-3 top-1/2 -translate-y-1/2 z-10">
                                <div
                                    class="w-6 h-6 bg-gray-950 border border-white/10 rounded-full
                        flex items-center justify-center">
                                    <i class="ti ti-chevron-right text-gray-700 text-xs"></i>
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="text-center mt-10">
                <a href="{{ route('cinema.movies') }}"
                    class="inline-flex items-center gap-2.5 h-13 px-8 bg-red-600
                    hover:bg-red-500 text-white font-bold text-sm rounded-2xl
                    transition-all duration-200 shadow-lg shadow-red-600/20
                    hover:-translate-y-0.5">
                    <i class="ti ti-ticket text-lg"></i>
                    Mulai Pesan Tiket
                </a>
            </div>

        </div>
    </section>

    {{-- Cinemas --}}
    @if ($cinemas->count() > 0)
        <section class="py-14 bg-gray-900/30 border-y border-white/5">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                <div class="flex items-end justify-between mb-7">
                    <div>
                        <p class="text-xs font-semibold text-blue-400 uppercase tracking-widest mb-1">
                            Jaringan Bioskop
                        </p>
                        <h2 class="text-2xl font-black text-white">Bioskop Partner Kami</h2>
                    </div>
                    <a href="{{ route('cinema.cinemas') }}"
                        class="hidden sm:flex items-center gap-2 text-sm font-medium text-gray-500
                    hover:text-white transition-colors duration-200 group">
                        Semua Bioskop
                        <i
                            class="ti ti-arrow-right text-base group-hover:translate-x-0.5
                    transition-transform duration-200"></i>
                    </a>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
                    @foreach ($cinemas as $cinema)
                        <a href="{{ route('cinema.cinemas', ['cinema' => $cinema->id]) }}"
                            class="group bg-gray-900 border border-white/5 rounded-2xl p-4
                    text-center hover:border-blue-500/30 hover:bg-blue-500/5
                    transition-all duration-300 hover:-translate-y-1">

                            {{-- Logo / Initial --}}
                            <div
                                class="w-12 h-12 bg-white/5 group-hover:bg-blue-500/10
                    rounded-2xl flex items-center justify-center mx-auto mb-3
                    transition-colors duration-300 border border-white/5
                    group-hover:border-blue-500/20">
                                @if ($cinema->logo)
                                    <img src="{{ Storage::url($cinema->logo) }}" class="w-7 h-7 object-contain"
                                        alt="{{ $cinema->name }}">
                                @else
                                    <i
                                        class="ti ti-building text-gray-600 group-hover:text-blue-400
                        text-xl transition-colors duration-300"></i>
                                @endif
                            </div>

                            <p
                                class="text-xs font-bold text-white line-clamp-2 leading-snug
                    mb-1 group-hover:text-blue-400 transition-colors duration-200">
                                {{ $cinema->name }}
                            </p>
                            <p class="text-[10px] text-gray-600">{{ $cinema->city }}</p>
                            <p class="text-[10px] text-gray-700 mt-1">
                                {{ $cinema->studios_count }} studio
                            </p>

                        </a>
                    @endforeach
                </div>

            </div>
        </section>
    @endif

    {{-- Features --}}
    <section class="py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 items-center">

                {{-- Left: Feature list --}}
                <div>
                    <p class="text-xs font-semibold text-red-500 uppercase tracking-widest mb-2">
                        Keunggulan Kami
                    </p>
                    <h2 class="text-3xl font-black text-white mb-3 leading-tight">
                        Pengalaman Menonton<br>
                        <span class="text-red-500">Level Berikutnya</span>
                    </h2>
                    <p class="text-gray-500 text-sm leading-relaxed mb-8 max-w-sm">
                        Dari pemilihan kursi hingga masuk studio — semua serba mudah,
                        cepat, dan bebas antri.
                    </p>

                    <div class="space-y-5">
                        @foreach ([
        [
            'icon' => 'ti-armchair',
            'title' => 'Pilih Kursi Interaktif',
            'desc' => 'Denah kursi real-time. Lihat mana yang kosong dan langsung klik untuk pilih. Kursi dikunci 10 menit selama checkout.',
            'color' => 'bg-red-600/20 text-red-400',
        ],
        [
            'icon' => 'ti-shield-check',
            'title' => 'Pembayaran 100% Aman',
            'desc' => 'Diproses oleh Midtrans dengan enkripsi SSL. Mendukung GoPay, OVO, Dana, QRIS, kartu kredit, dan transfer bank.',
            'color' => 'bg-emerald-600/20 text-emerald-400',
        ],
        [
            'icon' => 'ti-qrcode',
            'title' => 'E-Ticket Instan',
            'desc' => 'QR code tiket dikirim otomatis ke email dan tersimpan di akun. Cukup scan di pintu masuk studio.',
            'color' => 'bg-violet-600/20 text-violet-400',
        ],
        [
            'icon' => 'ti-bell',
            'title' => 'Notifikasi Real-time',
            'desc' => 'Dapat pengingat H-1 sebelum film tayang. Update status pesanan langsung di email.',
            'color' => 'bg-amber-600/20 text-amber-400',
        ],
    ] as $feature)
                            <div class="flex items-start gap-4 group">
                                <div
                                    class="w-11 h-11 {{ $feature['color'] }} rounded-2xl
                            flex items-center justify-center flex-shrink-0
                            group-hover:scale-110 transition-transform duration-200">
                                    <i class="ti {{ $feature['icon'] }} text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-sm font-bold text-white mb-1">
                                        {{ $feature['title'] }}
                                    </h3>
                                    <p class="text-xs text-gray-500 leading-relaxed">
                                        {{ $feature['desc'] }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Right: Mockup seat map preview --}}
                <div class="relative hidden lg:block">
                    <div
                        class="bg-gray-900 border border-white/10 rounded-3xl p-6
                    shadow-2xl shadow-black/50">

                        {{-- Header mockup --}}
                        <div class="flex items-center justify-between mb-5">
                            <div>
                                <p class="text-xs text-gray-500 mb-0.5">Studio IMAX</p>
                                <p class="text-sm font-bold text-white">Pilih Kursi Kamu</p>
                            </div>
                            <div class="flex items-center gap-2 text-xs text-gray-500">
                                <span class="flex items-center gap-1.5">
                                    <span class="w-3 h-3 bg-gray-700 rounded-sm"></span>
                                    Terisi
                                </span>
                                <span class="flex items-center gap-1.5">
                                    <span
                                        class="w-3 h-3 bg-emerald-500/30 border
                                    border-emerald-500/50 rounded-sm"></span>
                                    Tersedia
                                </span>
                                <span class="flex items-center gap-1.5">
                                    <span class="w-3 h-3 bg-red-500 rounded-sm"></span>
                                    Dipilih
                                </span>
                            </div>
                        </div>

                        {{-- Screen --}}
                        <div class="relative mb-6 mx-4">
                            <div
                                class="h-1.5 bg-gradient-to-r from-transparent via-white/20
                            to-transparent rounded-full">
                            </div>
                            <div class="text-center mt-1.5">
                                <span class="text-[9px] text-gray-600 tracking-widest uppercase">
                                    Layar
                                </span>
                            </div>
                            <div
                                class="absolute inset-x-0 -bottom-3 h-6
                            bg-gradient-to-b from-blue-500/10 to-transparent
                            blur-sm">
                            </div>
                        </div>

                        {{-- Seat grid mockup --}}
                        @php
                            $mockRows = ['A', 'B', 'C', 'D', 'E', 'F'];
                            $mockCols = 10;
                            $booked = ['A3', 'A7', 'B2', 'B5', 'B8', 'C4', 'C9', 'D1', 'D6', 'E3', 'E7'];
                            $selected = ['C5', 'C6'];
                            $vipRows = ['A', 'B'];
                        @endphp
                        <div class="space-y-1.5 mb-5">
                            @foreach ($mockRows as $row)
                                <div class="flex items-center gap-1.5">
                                    <span
                                        class="text-[9px] text-gray-700 w-3 text-right
                                flex-shrink-0">
                                        {{ $row }}
                                    </span>
                                    <div class="flex gap-1 mx-auto">
                                        @for ($c = 1; $c <= $mockCols; $c++)
                                            @php
                                                $seatId = $row . $c;
                                                $isBooked = in_array($seatId, $booked);
                                                $isSel = in_array($seatId, $selected);
                                                $isVip = in_array($row, $vipRows);
                                                $isAisle = $c === 5;
                                            @endphp
                                            @if ($isAisle)
                                                <div class="w-2"></div>
                                            @endif
                                            <div
                                                class="w-5 h-5 rounded-sm text-[8px] flex items-center
                                    justify-center font-medium cursor-pointer
                                    transition-all duration-150
                                    {{ $isSel ? 'bg-red-500 text-white scale-110 shadow-sm shadow-red-500/50' : '' }}
                                    {{ $isBooked ? 'bg-gray-700 text-gray-600' : '' }}
                                    {{ !$isSel && !$isBooked && $isVip ? 'bg-violet-500/20 border border-violet-500/30 text-violet-400' : '' }}
                                    {{ !$isSel && !$isBooked && !$isVip ? 'bg-emerald-500/20 border border-emerald-500/30 text-emerald-400' : '' }}
                                    {{ !$isBooked && !$isSel ? 'hover:bg-emerald-500/40' : '' }}">
                                                @if ($isSel)
                                                    ✓
                                                @endif
                                            </div>
                                        @endfor
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Selected seats info --}}
                        <div
                            class="bg-gray-950/80 rounded-2xl p-3 flex items-center
                        justify-between">
                            <div>
                                <p class="text-xs text-gray-500 mb-0.5">Kursi Dipilih</p>
                                <p class="text-sm font-black text-white">C5, C6</p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-gray-500 mb-0.5">Total</p>
                                <p class="text-sm font-black text-red-400">Rp 90.000</p>
                            </div>
                            <div
                                class="h-9 px-4 bg-red-600 rounded-xl flex items-center
                            text-white text-xs font-bold gap-1.5">
                                <i class="ti ti-check text-sm"></i>
                                Lanjut
                            </div>
                        </div>

                    </div>

                    {{-- Decorative glows --}}
                    <div
                        class="absolute -top-10 -right-10 w-40 h-40 bg-red-600/10
                    rounded-full blur-3xl">
                    </div>
                    <div
                        class="absolute -bottom-10 -left-10 w-40 h-40 bg-violet-600/10
                    rounded-full blur-3xl">
                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- CTA Section --}}
    <section class="py-20 relative overflow-hidden bg-gray-900/50 border-t border-white/5">
        <div class="absolute inset-0 pointer-events-none">
            <div
                class="absolute -top-32 left-1/2 -translate-x-1/2 w-[600px] h-[600px]
            bg-red-600/10 rounded-full blur-3xl">
            </div>
        </div>
        <div class="relative max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">

            <div
                class="w-16 h-16 bg-red-600/20 rounded-3xl flex items-center
            justify-center mx-auto mb-6 border border-red-500/20">
                <i class="ti ti-movie text-red-500 text-3xl"></i>
            </div>

            <h2 class="text-3xl sm:text-4xl font-black text-white mb-4 leading-tight">
                Siap Nikmati Film Favoritmu?
            </h2>
            <p class="text-gray-500 text-base leading-relaxed mb-8 max-w-md mx-auto">
                Pilih film, pilih kursi, bayar, dan duduk nyaman.
                Proses pemesanan selesai dalam kurang dari 2 menit!
            </p>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                <a href="{{ route('cinema.movies') }}"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2.5
                    h-13 px-8 bg-red-600 hover:bg-red-500 text-white font-bold
                    text-sm rounded-2xl transition-all duration-200
                    shadow-xl shadow-red-600/20 hover:-translate-y-0.5">
                    <i class="ti ti-ticket text-lg"></i>
                    Lihat Film Sekarang
                </a>
                <a href="{{ route('cinema.cinemas') }}"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2.5
                    h-13 px-7 border border-white/10 text-white font-medium text-sm
                    rounded-2xl hover:bg-white/5 hover:border-white/20
                    transition-all duration-200">
                    <i class="ti ti-building text-base"></i>
                    Cari Bioskop Terdekat
                </a>
            </div>

        </div>
    </section>

    @push('scripts')
        <script>
            function heroSlider(total) {
                return {
                    active: 0,
                    total: total,
                    timer: null,

                    init() {
                        if (this.total > 1) {
                            this.timer = setInterval(() => this.next(), 6000);
                        }
                    },

                    next() {
                        this.active = (this.active + 1) % this.total;
                    },

                    prev() {
                        this.active = (this.active - 1 + this.total) % this.total;
                    },

                    goTo(i) {
                        this.active = i;
                        clearInterval(this.timer);
                        this.timer = setInterval(() => this.next(), 6000);
                    }
                }
            }
        </script>
    @endpush

    @push('styles')
        <style>
            .scrollbar-hide::-webkit-scrollbar {
                display: none;
            }

            .scrollbar-hide {
                -ms-overflow-style: none;
                scrollbar-width: none;
            }

            h-13 {
                height: 3.25rem;
            }
        </style>
    @endpush

</x-cinema-layout>
