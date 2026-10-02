<x-user-layout>
    {{-- Hero section --}}
    <section class="relative overflow-hidden bg-gray-950 pt-8 pb-20 lg:pt-16 lg:pb-28">

        {{-- Background decoration --}}
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-40 -right-40 w-96 h-96 bg-white/3 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-white/2 rounded-full blur-3xl"></div>
            <div
                class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-white/1 rounded-full blur-3xl">
            </div>
            {{-- Grid pattern --}}
            <div class="absolute inset-0 opacity-[0.03]"
                style="background-image: linear-gradient(rgba(255,255,255,.3) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.3) 1px, transparent 1px); background-size: 40px 40px;">
            </div>
        </div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mx-auto text-center">

                {{-- Headline --}}
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-white leading-tight tracking-tight mb-6">
                    Temukan Event
                    <span class="relative inline-block">
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-gray-200 to-gray-400">
                            Impianmu
                        </span>
                        <svg class="absolute -bottom-2 left-0 w-full" height="6" viewBox="0 0 300 6">
                            <path d="M0 3 Q75 0 150 3 Q225 6 300 3" stroke="rgba(255,255,255,0.3)" stroke-width="2"
                                fill="none" stroke-linecap="round" />
                        </svg>
                    </span>
                    <br>Sekarang
                </h1>

                <p class="text-base sm:text-lg text-gray-400 mb-8 leading-relaxed max-w-xl mx-auto">
                    Dari konser musik, seminar, hingga festival budaya.
                    Semua tiket event tersedia di satu tempat dengan harga terbaik.
                </p>

                {{-- Search Bar --}}
                <form action="{{ route('events.index') }}" method="GET" class="relative max-w-xl mx-auto mb-8">
                    <div
                        class="flex items-center bg-white rounded-2xl p-1.5 shadow-2xl shadow-black/50 focus-within:shadow-black/60 transition-shadow duration-300">
                        <div class="flex items-center gap-3 flex-1 px-4">
                            <i class="ti ti-search text-gray-400 text-lg flex-shrink-0"></i>
                            <input type="text" name="search" placeholder="Cari event, konser, seminar..."
                                class="w-full bg-transparent border-none outline-none focus:outline-none focus:ring-0 text-sm text-gray-700
                                placeholder-gray-400 py-2.5">
                        </div>
                        <button type="submit"
                            class="h-11 px-6 bg-gray-900 text-white text-sm font-semibold rounded-xl
                            hover:bg-gray-700 transition-all duration-200 flex-shrink-0 flex items-center gap-2">
                            <span class="hidden sm:block">Cari Event</span>
                            <i class="ti ti-arrow-right text-base"></i>
                        </button>
                    </div>
                </form>

                {{-- Quick Stats --}}
                <div class="flex items-center justify-center gap-6 sm:gap-10">
                    @foreach ([['value' => number_format($stats['events']), 'label' => 'Event Aktif'], ['value' => number_format($stats['tickets']), 'label' => 'Tiket Terjual'], ['value' => number_format($stats['users']), 'label' => 'Pengguna']] as $stat)
                        <div class="text-center">
                            <p class="text-2xl sm:text-3xl font-bold text-white">{{ $stat['value'] }}+</p>
                            <p class="text-xs text-gray-500 mt-0.5">{{ $stat['label'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- Kategori --}}
    <section class="py-14 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Section Header --}}
            <div class="flex items-end justify-between mb-8">
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-2">Kategori</p>
                    <h2 class="text-2xl sm:text-3xl font-bold text-gray-900">Temukan Event Favoritmu</h2>
                </div>
                <a href="{{ route('events.index') }}"
                    class="hidden sm:flex items-center gap-1.5 text-sm font-medium text-gray-500
                    hover:text-gray-900 transition-colors duration-200">
                    Lihat semua <i class="ti ti-arrow-right text-base"></i>
                </a>
            </div>

            {{-- Category Grid --}}
            @if ($categories->isEmpty())
                <div class="text-center py-12 text-gray-400">
                    <i class="ti ti-tag-off text-4xl block mb-3"></i>
                    <p class="text-sm">Belum ada kategori tersedia</p>
                </div>
            @else
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-4 gap-3">
                    @foreach ($categories as $index => $category)
                        @php
                            $colors = [
                                'bg-rose-50 hover:bg-rose-100 border-rose-100',
                                'bg-blue-50 hover:bg-blue-100 border-blue-100',
                                'bg-violet-50 hover:bg-violet-100 border-violet-100',
                                'bg-amber-50 hover:bg-amber-100 border-amber-100',
                                'bg-emerald-50 hover:bg-emerald-100 border-emerald-100',
                                'bg-cyan-50 hover:bg-cyan-100 border-cyan-100',
                                'bg-orange-50 hover:bg-orange-100 border-orange-100',
                                'bg-pink-50 hover:bg-pink-100 border-pink-100',
                            ];
                            $iconColors = [
                                'text-rose-500',
                                'text-blue-500',
                                'text-violet-500',
                                'text-amber-500',
                                'text-emerald-500',
                                'text-cyan-500',
                                'text-orange-500',
                                'text-pink-500',
                            ];
                            $colorClass = $colors[$index % count($colors)];
                            $iconColorClass = $iconColors[$index % count($iconColors)];
                        @endphp
                        <a href="{{ route('events.index', ['category' => $category->id]) }}"
                            class="group flex items-center gap-3 p-4 border rounded-2xl transition-all duration-200 hover:shadow-sm hover:-translate-y-0.5 {{ $colorClass }}">
                            {{-- Icon / Image --}}
                            <div
                                class="w-12 h-12 rounded-xl overflow-hidden flex-shrink-0 bg-white/60 flex items-center justify-center">
                                @if ($category->image)
                                    <img src="{{ Storage::url($category->image) }}" class="w-full h-full object-cover"
                                        alt="{{ $category->name }}">
                                @else
                                    <i class="ti ti-tag text-xl {{ $iconColorClass }}"></i>
                                @endif
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-gray-900 truncate">{{ $category->name }}</p>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    {{ $category->events_count }} event
                                </p>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    {{-- Featured event --}}
    @if ($featuredEvents->count() > 0)
        <section class="py-14 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                {{-- Section Header --}}
                <div class="flex items-end justify-between mb-8">
                    <div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-2">
                            Unggulan
                        </p>
                        <h2 class="text-2xl sm:text-3xl font-bold text-gray-900">Event Pilihan</h2>
                    </div>
                    <a href="{{ route('events.index', ['featured' => 1]) }}"
                        class="hidden sm:flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-gray-900 transition-colors duration-200">
                        Lihat semua <i class="ti ti-arrow-right text-base"></i>
                    </a>
                </div>

                {{-- Featured Grid --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach ($featuredEvents as $index => $event)
                        @php
                            $minPrice = $event->ticketTypes->min('price');
                            $isFree = $minPrice == 0;
                        @endphp

                        {{-- First card: Large --}}
                        @if ($index === 0)
                            <div class="md:col-span-2 lg:col-span-2">
                            @else
                                <div>
                        @endif

                        <a href="{{ route('events.show', $event->slug) }}"
                            class="group block bg-white border border-gray-100 rounded-2xl overflow-hidden hover:shadow-lg hover:shadow-gray-200/60 hover:-translate-y-1 transition-all duration-300">

                            {{-- Thumbnail --}}
                            <div
                                class="relative {{ $index === 0 ? 'h-56 sm:h-72' : 'h-44' }} bg-gray-100 overflow-hidden">
                                @if ($event->thumbnail)
                                    <img src="{{ Storage::url($event->thumbnail) }}"
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                        alt="{{ $event->title }}">
                                @else
                                    <div
                                        class="w-full h-full bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center">
                                        <i class="ti ti-calendar-event text-5xl text-gray-300"></i>
                                    </div>
                                @endif

                                {{-- Overlay gradient --}}
                                <div
                                    class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent">
                                </div>

                                {{-- Badges --}}
                                <div class="absolute top-3 left-3 flex gap-2">
                                    <span
                                        class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1
                                bg-amber-400 text-amber-900 rounded-full">
                                        <i class="ti ti-star text-xs"></i> Featured
                                    </span>
                                    @if ($event->category)
                                        <span
                                            class="text-xs font-medium px-2.5 py-1 bg-white/90 text-gray-700 rounded-full backdrop-blur-sm">
                                            {{ $event->category->name }}
                                        </span>
                                    @endif
                                </div>

                                {{-- Price badge --}}
                                <div class="absolute bottom-3 right-3">
                                    <span
                                        class="text-xs font-bold px-3 py-1.5 rounded-xl backdrop-blur-sm
                                {{ $isFree ? 'bg-emerald-500 text-white' : 'bg-white/95 text-gray-900' }}">
                                        {{ $isFree ? 'GRATIS' : 'Mulai Rp ' . number_format($minPrice, 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>

                            {{-- Content --}}
                            <div class="p-4">
                                <h3
                                    class="text-base font-bold text-gray-900 mb-2 line-clamp-2
                            group-hover:text-gray-700 transition-colors duration-200 leading-snug">
                                    {{ $event->title }}
                                </h3>

                                <div class="space-y-1.5 mb-3">
                                    <div class="flex items-center gap-2 text-xs text-gray-500">
                                        <i class="ti ti-calendar text-gray-300 flex-shrink-0"></i>
                                        <span>{{ $event->start_date->translatedFormat('d M Y, H:i') }}</span>
                                    </div>
                                    <div class="flex items-center gap-2 text-xs text-gray-500">
                                        <i class="ti ti-map-pin text-gray-300 flex-shrink-0"></i>
                                        <span class="truncate">{{ $event->location }}</span>
                                    </div>
                                </div>

                                {{-- Ticket quota progress --}}
                                @php
                                    $totalQuota = $event->ticketTypes->sum('quota');
                                    $totalSold = $event->ticketTypes->sum('sold');
                                    $percentage = $totalQuota > 0 ? ($totalSold / $totalQuota) * 100 : 0;
                                @endphp
                                @if ($totalQuota > 0)
                                    <div class="mt-3">
                                        <div class="flex justify-between text-xs text-gray-400 mb-1.5">
                                            <span>{{ number_format($totalSold) }} terjual</span>
                                            <span>{{ number_format($totalQuota - $totalSold) }} sisa</span>
                                        </div>
                                        <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                            <div class="h-full rounded-full transition-all duration-500
                                    {{ $percentage >= 80 ? 'bg-red-400' : ($percentage >= 50 ? 'bg-amber-400' : 'bg-emerald-400') }}"
                                                style="width: {{ min(100, $percentage) }}%"></div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </a>
                </div>
    @endforeach
    </div>
    </div>
    </section>
    @endif

    {{-- Latest event --}}
    <section class="py-14 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Section Header --}}
            <div class="flex items-end justify-between mb-8">
                <div>
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-2">Event Terbaru</p>
                    <h2 class="text-2xl sm:text-3xl font-bold text-gray-900">Event yang Akan Datang</h2>
                </div>
                <a href="{{ route('events.index') }}"
                    class="hidden sm:flex items-center gap-1.5 text-sm font-medium text-gray-500
                    hover:text-gray-900 transition-colors duration-200">
                    Lihat semua <i class="ti ti-arrow-right text-base"></i>
                </a>
            </div>

            @if ($latestEvents->isEmpty())
                <div class="text-center py-16 bg-gray-50 rounded-2xl border border-gray-100">
                    <i class="ti ti-calendar-off text-5xl text-gray-200 block mb-3"></i>
                    <p class="text-sm text-gray-400 mb-4">Belum ada event tersedia</p>
                    @auth
                        <p class="text-xs text-gray-400">Pantau terus untuk event terbaru!</p>
                    @endauth
                </div>
            @else
                {{-- Event Grid --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    @foreach ($latestEvents as $event)
                        @php
                            $minPrice = $event->ticketTypes->min('price');
                            $isFree = $minPrice == 0;
                            $totalQuota = $event->ticketTypes->sum('quota');
                            $totalSold = $event->ticketTypes->sum('sold');
                            $remaining = $totalQuota - $totalSold;
                            $isAlmostFull = $totalQuota > 0 && $totalSold / $totalQuota >= 0.8;
                        @endphp
                        <a href="{{ route('events.show', $event->slug) }}"
                            class="group flex flex-col bg-white border border-gray-100 rounded-2xl overflow-hidden
                    hover:shadow-md hover:shadow-gray-200/50 hover:-translate-y-0.5
                    transition-all duration-300">

                            {{-- Thumbnail --}}
                            <div class="relative h-44 bg-gray-100 overflow-hidden flex-shrink-0">
                                @if ($event->thumbnail)
                                    <img src="{{ Storage::url($event->thumbnail) }}"
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                        alt="{{ $event->title }}">
                                @else
                                    <div
                                        class="w-full h-full bg-gradient-to-br from-gray-100 to-gray-200
                        flex items-center justify-center">
                                        <i class="ti ti-calendar-event text-4xl text-gray-300"></i>
                                    </div>
                                @endif

                                {{-- Overlay --}}
                                <div
                                    class="absolute inset-0 bg-gradient-to-t from-black/30 via-transparent to-transparent">
                                </div>

                                {{-- Category Badge --}}
                                @if ($event->category)
                                    <div class="absolute top-3 left-3">
                                        <span
                                            class="text-xs font-medium px-2.5 py-1 bg-white/90 text-gray-700
                            rounded-full backdrop-blur-sm">
                                            {{ $event->category->name }}
                                        </span>
                                    </div>
                                @endif

                                {{-- Almost Full Badge --}}
                                @if ($isAlmostFull)
                                    <div class="absolute top-3 right-3">
                                        <span
                                            class="text-xs font-semibold px-2.5 py-1 bg-red-500 text-white rounded-full animate-pulse">
                                            Hampir Habis!
                                        </span>
                                    </div>
                                @endif

                                {{-- Date Badge --}}
                                <div class="absolute bottom-3 left-3">
                                    <div
                                        class="bg-white/95 backdrop-blur-sm rounded-xl px-2.5 py-1.5 text-center min-w-[48px]">
                                        <p class="text-[10px] font-semibold text-gray-500 uppercase leading-none">
                                            {{ $event->start_date->format('M') }}
                                        </p>
                                        <p class="text-base font-black text-gray-900 leading-none mt-0.5">
                                            {{ $event->start_date->format('d') }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {{-- Content --}}
                            <div class="p-4 flex flex-col flex-1">
                                <h3
                                    class="text-sm font-bold text-gray-900 mb-2 line-clamp-2 leading-snug
                        group-hover:text-gray-700 transition-colors duration-200 flex-1">
                                    {{ $event->title }}
                                </h3>

                                <div class="space-y-1 mb-3">
                                    <div class="flex items-center gap-2 text-xs text-gray-500">
                                        <i class="ti ti-map-pin text-gray-300 flex-shrink-0 text-sm"></i>
                                        <span class="truncate">{{ $event->location }}</span>
                                    </div>
                                    <div class="flex items-center gap-2 text-xs text-gray-500">
                                        <i class="ti ti-clock text-gray-300 flex-shrink-0 text-sm"></i>
                                        <span>{{ $event->start_date->format('H:i') }} WIB</span>
                                    </div>
                                </div>

                                {{-- Footer --}}
                                <div class="flex items-center justify-between pt-3 border-t border-gray-50 mt-auto">
                                    <div>
                                        @if ($isFree)
                                            <span class="text-sm font-bold text-emerald-600">GRATIS</span>
                                        @else
                                            <p class="text-xs text-gray-400 leading-none">Mulai dari</p>
                                            <p class="text-sm font-bold text-gray-900 leading-tight mt-0.5">
                                                Rp {{ number_format($minPrice, 0, ',', '.') }}
                                            </p>
                                        @endif
                                    </div>
                                    <div
                                        class="w-8 h-8 bg-gray-900 rounded-xl flex items-center justify-center
                            group-hover:bg-gray-700 transition-colors duration-200">
                                        <i class="ti ti-arrow-right text-white text-sm"></i>
                                    </div>
                                </div>
                            </div>

                        </a>
                    @endforeach
                </div>

                {{-- Mobile: Lihat semua --}}
                <div class="sm:hidden mt-5 text-center">
                    <a href="{{ route('events.index') }}"
                        class="inline-flex items-center gap-2 h-10 px-6 border border-gray-200 text-gray-700
                    text-sm font-medium rounded-xl hover:bg-gray-50 transition-all duration-200">
                        Lihat semua event <i class="ti ti-arrow-right text-base"></i>
                    </a>
                </div>
            @endif
        </div>
    </section>

    {{-- Cinema Banner --}}
    <section class="py-14 bg-gray-950 relative overflow-hidden">
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute -top-20 -right-20 w-80 h-80 bg-red-600/5 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-20 -left-20 w-80 h-80 bg-red-600/3 rounded-full blur-3xl"></div>
            <div class="absolute inset-0 opacity-[0.02]"
                style="background-image: linear-gradient(rgba(255,255,255,.3) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,.3) 1px, transparent 1px);
                background-size: 40px 40px;">
            </div>
        </div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row items-center gap-10">

                {{-- Left: Text --}}
                <div class="flex-1 text-center lg:text-left">
                    <div
                        class="inline-flex items-center gap-2 bg-red-600/10 border
                    border-red-500/20 text-red-400 text-xs font-semibold px-4 py-2
                    rounded-full mb-5">
                        <i class="ti ti-movie text-sm"></i>
                        Ada yang baru di {{ setting('app_name') }} — Bioskop Online
                    </div>

                    <h2 class="text-3xl lg:text-4xl font-black text-white leading-tight mb-4">
                        Booking Kursi Bioskop<br>
                        <span class="text-red-500">Langsung Online!</span>
                    </h2>

                    <p class="text-base text-gray-400 leading-relaxed mb-6 max-w-lg mx-auto lg:mx-0">
                        Pilih film favorit, tentukan kursi, bayar, dan dapatkan
                        e-ticket langsung di smartphone. Tidak perlu antri lagi!
                    </p>

                    <div
                        class="flex flex-col sm:flex-row items-center gap-3
                    justify-center lg:justify-start">
                        <a href="{{ route('cinema.movies') }}"
                            class="inline-flex items-center gap-2 h-12 px-7 bg-red-600
                            hover:bg-red-500 text-white font-bold text-sm rounded-xl
                            transition-all duration-200">
                            <i class="ti ti-ticket text-base"></i>
                            Lihat Film Sekarang
                        </a>
                        <a href="{{ route('cinema.index') }}"
                            class="inline-flex items-center gap-2 h-12 px-6
                            border border-white/10 text-white font-medium text-sm
                            rounded-xl hover:bg-white/5 transition-all duration-200">
                            <i class="ti ti-movie text-base"></i>
                            Jelajahi Bioskop
                        </a>
                    </div>

                    {{-- Feature badges --}}
                    <div
                        class="flex flex-wrap items-center gap-2 mt-6
                    justify-center lg:justify-start">
                        @foreach ([['icon' => 'ti-armchair', 'label' => 'Pilih Kursi'], ['icon' => 'ti-qrcode', 'label' => 'E-Ticket QR'], ['icon' => 'ti-device-mobile', 'label' => 'Bayar Digital'], ['icon' => 'ti-shield-check', 'label' => 'Aman & Terjamin']] as $feature)
                            <div
                                class="flex items-center gap-1.5 bg-white/5 border border-white/10
                        text-gray-400 text-xs font-medium px-3 py-1.5 rounded-full">
                                <i class="ti {{ $feature['icon'] }} text-red-500 text-sm"></i>
                                {{ $feature['label'] }}
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Right: Film Posters Preview --}}
                <div class="flex-shrink-0">
                    <div class="relative flex items-end gap-3">
                        @php
                            $previewMovies = \App\Models\Movie::where('status', 'now_showing')
                                ->whereNotNull('poster')
                                ->take(3)
                                ->get();
                        @endphp

                        @foreach ($previewMovies as $i => $previewMovie)
                            <div
                                class="relative transition-transform duration-300
                        hover:-translate-y-2
                        {{ $i === 1 ? '-translate-y-4' : '' }}">
                                <div
                                    class="w-32 rounded-2xl overflow-hidden shadow-2xl
                            border-2 border-white/10">
                                    <img src="{{ $previewMovie->poster_url }}"
                                        class="w-full aspect-[2/3] object-cover" alt="{{ $previewMovie->title }}">
                                </div>
                                @if ($i === 1)
                                    <div
                                        class="absolute -top-3 -right-3 bg-red-600 text-white
                            text-[10px] font-black px-2 py-0.5 rounded-full
                            shadow-lg shadow-red-500/30">
                                        HOT
                                    </div>
                                @endif
                            </div>
                        @endforeach

                        {{-- Glow effect --}}
                        <div
                            class="absolute inset-0 bg-red-600/10 blur-3xl rounded-full
                        -z-10 scale-150">
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- Blog terbaru --}}
    @if ($latestBlogs->count() > 0)
        <section class="py-14 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                {{-- Section Header --}}
                <div class="flex items-end justify-between mb-8">
                    <div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-2">Blog</p>
                        <h2 class="text-2xl sm:text-3xl font-bold text-gray-900">Artikel Terbaru</h2>
                    </div>
                    <a href="{{ route('blogs.index') }}"
                        class="hidden sm:flex items-center gap-1.5 text-sm font-medium text-gray-500
                    hover:text-gray-900 transition-colors duration-200">
                        Lihat semua <i class="ti ti-arrow-right text-base"></i>
                    </a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    @foreach ($latestBlogs as $index => $blog)
                        <a href="{{ route('blogs.show', $blog->slug) }}"
                            class="group bg-white border border-gray-100 rounded-2xl overflow-hidden
                    hover:shadow-md hover:shadow-gray-200/50 hover:-translate-y-0.5
                    transition-all duration-300">

                            {{-- Thumbnail --}}
                            <div class="relative h-48 bg-gray-100 overflow-hidden">
                                @if ($blog->thumbnail)
                                    <img src="{{ Storage::url($blog->thumbnail) }}"
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                        alt="{{ $blog->title }}">
                                @else
                                    <div
                                        class="w-full h-full bg-gradient-to-br from-gray-100 to-gray-200
                        flex items-center justify-center">
                                        <i class="ti ti-news text-5xl text-gray-300"></i>
                                    </div>
                                @endif
                                @if ($blog->is_featured)
                                    <div class="absolute top-3 left-3">
                                        <span
                                            class="text-xs font-semibold px-2.5 py-1 bg-gray-900 text-white rounded-full">
                                            <i class="ti ti-star-filled text-xs mr-1"></i> Featured
                                        </span>
                                    </div>
                                @endif
                            </div>

                            {{-- Content --}}
                            <div class="p-5">
                                <div class="flex items-center gap-2 mb-3">
                                    <div
                                        class="w-6 h-6 rounded-full bg-gray-900 flex items-center justify-center
                            text-white text-[10px] font-semibold flex-shrink-0">
                                        @if (setting('app_logo'))
                                            <img src="{{ Storage::url(setting('app_logo')) }}"
                                                alt="{{ setting('app_name', 'TicketIn') }}"
                                                class="w-7 h-7 rounded-full object-cover flex-shrink-0">
                                        @else
                                            <svg class="w-5 h-5 fill-white" viewBox="0 0 24 24">
                                                <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" />
                                            </svg>
                                        @endif
                                    </div>
                                    <span class="text-xs text-gray-400">{{ $blog->author->name ?? '-' }}</span>
                                    <span class="text-gray-200">·</span>
                                    <span class="text-xs text-gray-400">
                                        {{ $blog->published_at?->diffForHumans() ?? $blog->created_at->diffForHumans() }}
                                    </span>
                                </div>

                                <h3
                                    class="text-base font-bold text-gray-900 mb-2 line-clamp-2 leading-snug
                        group-hover:text-gray-700 transition-colors duration-200">
                                    {{ $blog->title }}
                                </h3>

                                @if ($blog->excerpt)
                                    <p class="text-xs text-gray-500 line-clamp-2 leading-relaxed">
                                        {{ $blog->excerpt }}
                                    </p>
                                @endif

                                <div
                                    class="flex items-center gap-1.5 mt-4 text-xs font-medium text-gray-900
                        group-hover:gap-2.5 transition-all duration-200">
                                    Baca selengkapnya
                                    <i class="ti ti-arrow-right text-base"></i>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- CTA --}}
    <section class="py-20 bg-gray-950 relative overflow-hidden">

        {{-- Background decoration --}}
        <div class="absolute inset-0 pointer-events-none overflow-hidden">
            <div class="absolute -top-32 -right-32 w-80 h-80 bg-white/3 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-32 -left-32 w-80 h-80 bg-white/2 rounded-full blur-3xl"></div>
            <div class="absolute inset-0 opacity-[0.02]"
                style="background-image: linear-gradient(rgba(255,255,255,.3) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,.3) 1px, transparent 1px);
                background-size: 40px 40px;">
            </div>
        </div>

        <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">

            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-white mb-4 leading-tight">
                Siap Menemukan<br>
                <span class="text-gray-400">Event Selanjutnya?</span>
            </h2>

            <p class="text-base text-gray-500 mb-8 max-w-xl mx-auto leading-relaxed">
                Daftar sekarang dan nikmati kemudahan membeli tiket event favoritmu.
                Aman, mudah, dan terpercaya.
            </p>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                @guest
                    <a href="{{ route('register') }}"
                        class="w-full sm:w-auto h-12 px-8 bg-white text-gray-900 font-semibold text-sm
                    rounded-xl hover:bg-gray-100 transition-all duration-200
                    flex items-center justify-center gap-2">
                        <i class="ti ti-user-plus text-base"></i>
                        Daftar Gratis Sekarang
                    </a>
                    <a href="{{ route('events.index') }}"
                        class="w-full sm:w-auto h-12 px-8 border border-white/20 text-white font-medium text-sm
                    rounded-xl hover:bg-white/10 transition-all duration-200
                    flex items-center justify-center gap-2">
                        <i class="ti ti-calendar-event text-base"></i>
                        Jelajahi Event
                    </a>
                @else
                    <a href="{{ route('events.index') }}"
                        class="w-full sm:w-auto h-12 px-8 bg-white text-gray-900 font-semibold text-sm
                    rounded-xl hover:bg-gray-100 transition-all duration-200
                    flex items-center justify-center gap-2">
                        <i class="ti ti-calendar-event text-base"></i>
                        Jelajahi Event Sekarang
                    </a>
                    <a href="{{ route('user.tickets.index') }}"
                        class="w-full sm:w-auto h-12 px-8 border border-white/20 text-white font-medium text-sm
                    rounded-xl hover:bg-white/10 transition-all duration-200
                    flex items-center justify-center gap-2">
                        <i class="ti ti-ticket text-base"></i>
                        Tiket Saya
                    </a>
                @endauth
            </div>

            {{-- Trust Badges --}}
            <div class="flex items-center justify-center gap-6 mt-10 pt-10 border-t border-white/5">
                @foreach ([['icon' => 'ti-shield-check', 'text' => 'Pembayaran Aman'], ['icon' => 'ti-clock', 'text' => 'Tiket Instan'], ['icon' => 'ti-headset', 'text' => 'Support 24/7'], ['icon' => 'ti-refresh', 'text' => 'Refund Mudah']] as $badge)
                    <div class="flex flex-col items-center gap-1.5">
                        <i class="ti {{ $badge['icon'] }} text-xl text-gray-600"></i>
                        <span class="text-xs text-gray-600 hidden sm:block">{{ $badge['text'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

</x-user-layout>
