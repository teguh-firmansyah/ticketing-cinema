<x-cinema-layout>
    <x-slot name="title">
        {{ $status === 'now_showing' ? 'Sedang Tayang' : 'Segera Hadir' }}
        — {{ config('app.name') }} Cinema
    </x-slot>

    {{-- Hero Banner --}}
    @if ($status === 'now_showing' && $featured->count() > 0)
        <section class="relative bg-gray-900 overflow-hidden" x-data="heroBanner({{ $featured->count() }})" x-init="init()">

            {{-- Slides --}}
            @foreach ($featured as $i => $movie)
                <div class="absolute inset-0 transition-opacity duration-700"
                    :class="{{ $i }} === active ? 'opacity-100' : 'opacity-0'">

                    {{-- Backdrop --}}
                    <img src="{{ $movie->backdrop_url }}" class="w-full h-full object-cover" alt="{{ $movie->title }}">
                    <div class="absolute inset-0 bg-gradient-to-r from-gray-950 via-gray-950/70 to-transparent"></div>
                    <div class="absolute inset-0 bg-gradient-to-t from-gray-950 via-transparent to-transparent"></div>

                </div>
            @endforeach

            {{-- Content --}}
            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24">
                @foreach ($featured as $i => $movie)
                    <div class="max-w-lg transition-all duration-500"
                        :class="{{ $i }} === active ?
                            'opacity-100 translate-y-0' :
                            'opacity-0 translate-y-4 absolute'">

                        {{-- Badges --}}
                        <div class="flex items-center gap-2 mb-4">
                            <span
                                class="bg-red-600 text-white text-xs font-bold px-3 py-1
                    rounded-full flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 bg-white rounded-full animate-pulse"></span>
                                Sedang Tayang
                            </span>
                            <span
                                class="bg-white/10 text-white text-xs font-medium
                    px-3 py-1 rounded-full border border-white/10">
                                {{ $movie->format ?? '2D' }}
                            </span>
                            <span
                                class="bg-white/10 text-white text-xs font-medium
                    px-3 py-1 rounded-full border border-white/10">
                                {{ $movie->age_rating }}
                            </span>
                        </div>

                        <h1
                            class="text-3xl lg:text-5xl font-black text-white leading-tight mb-3
                tracking-tight">
                            {{ $movie->title }}
                        </h1>

                        <p class="text-sm text-gray-400 mb-2 flex items-center gap-3">
                            <span>{{ $movie->genres_string }}</span>
                            <span class="text-gray-600">·</span>
                            <span>{{ $movie->duration_formatted }}</span>
                            @if ($movie->vote_average)
                                <span class="text-gray-600">·</span>
                                <span class="flex items-center gap-1 text-amber-400">
                                    <i class="ti ti-star-filled text-xs"></i>
                                    {{ $movie->vote_average }}
                                </span>
                            @endif
                        </p>

                        <p class="text-sm text-gray-400 leading-relaxed mb-6 line-clamp-3">
                            {{ $movie->synopsis }}
                        </p>

                        <div class="flex items-center gap-3">
                            <a href="{{ route('cinema.movies.show', $movie->slug) }}"
                                class="inline-flex items-center gap-2 h-12 px-6 bg-red-600
                        hover:bg-red-500 text-white font-bold text-sm rounded-xl
                        transition-all duration-200">
                                <i class="ti ti-ticket text-base"></i>
                                Beli Tiket
                            </a>
                            @if ($movie->hasTrailer())
                                <a href="{{ $movie->trailer_url }}" target="_blank"
                                    class="inline-flex items-center gap-2 h-12 px-5 bg-white/10
                        hover:bg-white/20 border border-white/10 text-white font-medium
                        text-sm rounded-xl transition-all duration-200">
                                    <i class="ti ti-player-play text-base"></i>
                                    Trailer
                                </a>
                            @endif
                        </div>

                    </div>
                @endforeach
            </div>

            {{-- Slide indicators --}}
            @if ($featured->count() > 1)
                <div class="absolute bottom-6 left-1/2 -translate-x-1/2 flex items-center gap-2">
                    @foreach ($featured as $i => $movie)
                        <button @click="goTo({{ $i }})" class="h-1 rounded-full transition-all duration-300"
                            :class="{{ $i }} === active ?
                                'bg-red-500 w-8' :
                                'bg-white/30 w-2 hover:bg-white/50'">
                        </button>
                    @endforeach
                </div>
            @endif

            {{-- Poster thumbnails (desktop) --}}
            @if ($featured->count() > 1)
                <div class="absolute right-8 top-1/2 -translate-y-1/2 hidden xl:flex flex-col gap-3">
                    @foreach ($featured as $i => $movie)
                        <button @click="goTo({{ $i }})"
                            class="w-14 h-20 rounded-xl overflow-hidden border-2 transition-all duration-200"
                            :class="{{ $i }} === active ?
                                'border-red-500 opacity-100 scale-110' :
                                'border-transparent opacity-50 hover:opacity-75'">
                            <img src="{{ $movie->poster_url }}" class="w-full h-full object-cover"
                                alt="{{ $movie->title }}">
                        </button>
                    @endforeach
                </div>
            @endif

        </section>
    @endif

    {{-- Main Content --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        {{--  Tab Navigation --}}
        <div class="flex flex-col sm:flex-row items-start sm:items-center
        justify-between gap-4 mb-8">

            {{-- Tabs --}}
            <div class="flex items-center bg-gray-900 border border-white/5 rounded-2xl p-1">
                <a href="{{ route('cinema.movies', ['status' => 'now_showing'] + request()->except('status', 'page')) }}"
                    class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold
                    transition-all duration-200
                    {{ $status === 'now_showing' ? 'bg-red-600 text-white shadow-lg' : 'text-gray-500 hover:text-white' }}">
                    <i class="ti ti-movie text-sm"></i>
                    Sedang Tayang
                    <span class="text-xs font-normal opacity-70 ml-0.5">
                        ({{ $stats['now_showing'] }})
                    </span>
                </a>
                <a href="{{ route('cinema.movies', ['status' => 'coming_soon'] + request()->except('status', 'page')) }}"
                    class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold
                    transition-all duration-200
                    {{ $status === 'coming_soon' ? 'bg-red-600 text-white shadow-lg' : 'text-gray-500 hover:text-white' }}">
                    <i class="ti ti-calendar-event text-sm"></i>
                    Segera Hadir
                    <span class="text-xs font-normal opacity-70 ml-0.5">
                        ({{ $stats['coming_soon'] }})
                    </span>
                </a>
            </div>

            {{-- Sort --}}
            <div class="flex items-center gap-2">
                <span class="text-xs text-gray-600">Urutkan:</span>
                <select name="sort" onchange="window.location.href = updateUrl('sort', this.value)"
                    class="bg-gray-900 border border-white/10 text-gray-300 text-sm
                    rounded-xl px-3 h-9 outline-none cursor-pointer
                    focus:border-white/20">
                    <option value="latest" {{ $sort === 'latest' ? 'selected' : '' }}>Terbaru</option>
                    <option value="rating" {{ $sort === 'rating' ? 'selected' : '' }}>Rating Tertinggi</option>
                    <option value="release" {{ $sort === 'release' ? 'selected' : '' }}>Tanggal Rilis</option>
                    <option value="title" {{ $sort === 'title' ? 'selected' : '' }}>A-Z</option>
                </select>
            </div>
        </div>

        <div class="flex gap-6">

            {{-- Sidebar Filter --}}
            <aside class="hidden lg:block w-52 flex-shrink-0">
                <div class="bg-gray-900 border border-white/5 rounded-2xl p-5 sticky top-24">

                    {{-- Genre Filter --}}
                    <div class="mb-5">
                        <p
                            class="text-xs font-semibold text-gray-400 uppercase
                        tracking-wider mb-3">
                            Genre</p>
                        <div class="space-y-1">
                            <a href="{{ route('cinema.movies', array_merge(request()->except('genre', 'page'), ['status' => $status])) }}"
                                class="flex items-center justify-between px-3 py-2 rounded-xl
                                text-sm transition-all duration-150
                                {{ !$genre ? 'bg-red-600/20 text-red-400 font-medium' : 'text-gray-500 hover:text-white hover:bg-white/5' }}">
                                <span>Semua Genre</span>
                            </a>
                            @foreach ($genres as $g)
                                <a href="{{ route('cinema.movies', array_merge(request()->except('genre', 'page'), ['status' => $status, 'genre' => $g])) }}"
                                    class="flex items-center justify-between px-3 py-2 rounded-xl
                                text-sm transition-all duration-150
                                {{ $genre === $g ? 'bg-red-600/20 text-red-400 font-medium' : 'text-gray-500 hover:text-white hover:bg-white/5' }}">
                                    <span>{{ $g }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>

                    {{-- Rating Filter --}}
                    <div>
                        <p
                            class="text-xs font-semibold text-gray-400 uppercase
                        tracking-wider mb-3">
                            Usia</p>
                        <div class="space-y-1">
                            <a href="{{ route('cinema.movies', array_merge(request()->except('rating', 'page'), ['status' => $status])) }}"
                                class="flex items-center justify-between px-3 py-2 rounded-xl
                                text-sm transition-all duration-150
                                {{ !$rating ? 'bg-red-600/20 text-red-400 font-medium' : 'text-gray-500 hover:text-white hover:bg-white/5' }}">
                                <span>Semua Rating</span>
                            </a>
                            @foreach (['SU', '13+', '17+', '21+'] as $r)
                                <a href="{{ route('cinema.movies', array_merge(request()->except('rating', 'page'), ['status' => $status, 'rating' => $r])) }}"
                                    class="flex items-center gap-2 px-3 py-2 rounded-xl text-sm
                                transition-all duration-150
                                {{ $rating === $r
                                    ? 'bg-red-600/20 text-red-400 font-medium'
                                    : 'text-gray-500 hover:text-white hover:bg-white/5' }}">
                                    <span
                                        class="text-xs px-2 py-0.5 border rounded-md font-bold
                                {{ $r === 'SU' ? 'border-green-500/50 text-green-400' : '' }}
                                {{ $r === '13+' ? 'border-blue-500/50 text-blue-400' : '' }}
                                {{ $r === '17+' ? 'border-amber-500/50 text-amber-400' : '' }}
                                {{ $r === '21+' ? 'border-red-500/50 text-red-400' : '' }}">
                                        {{ $r }}
                                    </span>
                                    <span>{{ $r === 'SU' ? 'Semua Umur' : 'Usia ' . $r }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>

                    {{-- Reset --}}
                    @if ($genre || $rating)
                        <a href="{{ route('cinema.movies', ['status' => $status]) }}"
                            class="mt-4 flex items-center justify-center gap-2 h-9 w-full
                        border border-white/10 text-gray-500 text-xs rounded-xl
                        hover:text-white hover:border-white/20 transition-all duration-200">
                            <i class="ti ti-x text-sm"></i>
                            Reset Filter
                        </a>
                    @endif

                </div>
            </aside>

            {{-- Movie Grid --}}
            <div class="flex-1 min-w-0">

                {{-- Active Filters (mobile) --}}
                @if ($genre || $rating)
                    <div class="flex items-center gap-2 flex-wrap mb-5 lg:hidden">
                        <span class="text-xs text-gray-500">Filter aktif:</span>
                        @if ($genre)
                            <a href="{{ route('cinema.movies', array_merge(request()->except('genre'), ['status' => $status])) }}"
                                class="flex items-center gap-1.5 bg-red-600/20 text-red-400 text-xs
                        font-medium px-2.5 py-1 rounded-full border border-red-500/30">
                                {{ $genre }}
                                <i class="ti ti-x text-xs"></i>
                            </a>
                        @endif
                        @if ($rating)
                            <a href="{{ route('cinema.movies', array_merge(request()->except('rating'), ['status' => $status])) }}"
                                class="flex items-center gap-1.5 bg-red-600/20 text-red-400 text-xs
                        font-medium px-2.5 py-1 rounded-full border border-red-500/30">
                                {{ $rating }}
                                <i class="ti ti-x text-xs"></i>
                            </a>
                        @endif
                    </div>
                @endif

                @if ($movies->isEmpty())
                    {{-- Empty state --}}
                    <div class="text-center py-20 bg-gray-900/50 border border-white/5 rounded-2xl">
                        <i class="ti ti-movie-off text-5xl text-gray-700 block mb-4"></i>
                        <p class="text-base font-semibold text-gray-400 mb-2">
                            Tidak ada film ditemukan
                        </p>
                        <p class="text-sm text-gray-600 mb-5">
                            Coba ubah filter atau cari film lainnya
                        </p>
                        <a href="{{ route('cinema.movies', ['status' => $status]) }}"
                            class="inline-flex items-center gap-2 h-10 px-5 bg-red-600
                        hover:bg-red-500 text-white text-sm font-medium rounded-xl
                        transition-all duration-200">
                            <i class="ti ti-refresh text-base"></i>
                            Reset Filter
                        </a>
                    </div>
                @else
                    {{-- Movie Grid --}}
                    <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-4">
                        @foreach ($movies as $movie)
                            <a href="{{ route('cinema.movies.show', $movie->slug) }}"
                                class="group bg-gray-900 border border-white/5 rounded-2xl
                        overflow-hidden hover:border-red-500/30 hover:-translate-y-1
                        hover:shadow-xl hover:shadow-red-500/5
                        transition-all duration-300">

                                {{-- Poster --}}
                                <div class="relative aspect-[2/3] bg-gray-800 overflow-hidden">
                                    <img src="{{ $movie->poster_url }}" alt="{{ $movie->title }}" loading="lazy"
                                        class="w-full h-full object-cover group-hover:scale-105
                                transition-transform duration-500">

                                    {{-- Overlay on hover --}}
                                    <div
                                        class="absolute inset-0 bg-gradient-to-t from-black/80
                            via-black/0 to-black/0 opacity-0 group-hover:opacity-100
                            transition-opacity duration-300 flex items-end p-4">
                                        <div class="w-full">
                                            <div class="flex items-center gap-2 mb-2">
                                                @if ($movie->vote_average)
                                                    <span
                                                        class="flex items-center gap-1 text-xs
                                        text-amber-400 font-semibold">
                                                        <i class="ti ti-star-filled text-xs"></i>
                                                        {{ $movie->vote_average }}
                                                    </span>
                                                @endif
                                                <span class="text-xs text-gray-400">
                                                    {{ $movie->duration_formatted }}
                                                </span>
                                            </div>
                                            @if ($status === 'now_showing')
                                                <div
                                                    class="w-full h-9 bg-red-600 hover:bg-red-500
                                    rounded-xl flex items-center justify-center
                                    gap-2 text-white text-xs font-bold
                                    transition-colors duration-200">
                                                    <i class="ti ti-ticket text-sm"></i>
                                                    Beli Tiket
                                                </div>
                                            @else
                                                <div
                                                    class="w-full h-9 bg-white/10 border border-white/20
                                    rounded-xl flex items-center justify-center
                                    gap-2 text-white text-xs font-medium">
                                                    <i class="ti ti-bell text-sm"></i>
                                                    Ingatkan Saya
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Status badge --}}
                                    <div class="absolute top-2 left-2">
                                        @if ($status === 'now_showing')
                                            <span
                                                class="bg-red-600 text-white text-[10px] font-bold
                                px-2 py-0.5 rounded-lg">
                                                Tayang
                                            </span>
                                        @else
                                            <span
                                                class="bg-amber-500 text-white text-[10px] font-bold
                                px-2 py-0.5 rounded-lg">
                                                Segera
                                            </span>
                                        @endif
                                    </div>

                                    {{-- Age Rating --}}
                                    <div class="absolute top-2 right-2">
                                        <span
                                            class="text-[10px] font-bold px-2 py-0.5 rounded-lg border
                                {{ $movie->age_rating === 'SU' ? 'bg-green-900/80 border-green-500/50 text-green-400' : '' }}
                                {{ $movie->age_rating === '13+' ? 'bg-blue-900/80 border-blue-500/50 text-blue-400' : '' }}
                                {{ $movie->age_rating === '17+' ? 'bg-amber-900/80 border-amber-500/50 text-amber-400' : '' }}
                                {{ $movie->age_rating === '21+' ? 'bg-red-900/80 border-red-500/50 text-red-400' : '' }}">
                                            {{ $movie->age_rating }}
                                        </span>
                                    </div>
                                </div>

                                {{-- Info --}}
                                <div class="p-3">
                                    <h3
                                        class="text-sm font-bold text-white line-clamp-2 leading-snug
                            mb-1.5 group-hover:text-red-400 transition-colors duration-200">
                                        {{ $movie->title }}
                                    </h3>
                                    <p class="text-xs text-gray-500 truncate mb-2">
                                        {{ $movie->genres_string }}
                                    </p>
                                    @if ($status === 'coming_soon' && $movie->release_date)
                                        <div class="flex items-center gap-1.5 text-xs text-amber-400">
                                            <i class="ti ti-calendar text-xs"></i>
                                            {{ $movie->release_date->translatedFormat('d M Y') }}
                                        </div>
                                    @elseif($movie->vote_average)
                                        <div class="flex items-center gap-1.5 text-xs text-amber-400">
                                            <i class="ti ti-star-filled text-xs"></i>
                                            <span class="font-semibold">{{ $movie->vote_average }}</span>
                                            <span class="text-gray-600">/10</span>
                                        </div>
                                    @endif
                                </div>

                            </a>
                        @endforeach
                    </div>

                    {{-- Pagination --}}
                    @if ($movies->hasPages())
                        <div class="mt-8 flex justify-center">
                            <div class="flex items-center gap-2">
                                {{-- Previous --}}
                                @if ($movies->onFirstPage())
                                    <span
                                        class="w-10 h-10 flex items-center justify-center border
                        border-white/5 rounded-xl text-gray-700 cursor-not-allowed">
                                        <i class="ti ti-chevron-left text-sm"></i>
                                    </span>
                                @else
                                    <a href="{{ $movies->previousPageUrl() }}"
                                        class="w-10 h-10 flex items-center justify-center border
                            border-white/10 rounded-xl text-gray-400
                            hover:bg-white/5 hover:text-white transition-all duration-200">
                                        <i class="ti ti-chevron-left text-sm"></i>
                                    </a>
                                @endif

                                {{-- Pages --}}
                                @foreach ($movies->getUrlRange(max(1, $movies->currentPage() - 2), min($movies->lastPage(), $movies->currentPage() + 2)) as $page => $url)
                                    <a href="{{ $url }}"
                                        class="w-10 h-10 flex items-center justify-center border
                            rounded-xl text-sm font-medium transition-all duration-200
                            {{ $page === $movies->currentPage()
                                ? 'bg-red-600 border-red-600 text-white'
                                : 'border-white/10 text-gray-400 hover:bg-white/5 hover:text-white' }}">
                                        {{ $page }}
                                    </a>
                                @endforeach

                                {{-- Next --}}
                                @if ($movies->hasMorePages())
                                    <a href="{{ $movies->nextPageUrl() }}"
                                        class="w-10 h-10 flex items-center justify-center border
                            border-white/10 rounded-xl text-gray-400
                            hover:bg-white/5 hover:text-white transition-all duration-200">
                                        <i class="ti ti-chevron-right text-sm"></i>
                                    </a>
                                @else
                                    <span
                                        class="w-10 h-10 flex items-center justify-center border
                        border-white/5 rounded-xl text-gray-700 cursor-not-allowed">
                                        <i class="ti ti-chevron-right text-sm"></i>
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endif

                @endif
            </div>

        </div>
    </div>

    @push('scripts')
        <script>
            function heroBanner(total) {
                return {
                    active: 0,
                    timer: null,
                    total: total,

                    init() {
                        if (this.total > 1) {
                            this.timer = setInterval(() => {
                                this.active = (this.active + 1) % this.total;
                            }, 5000);
                        }
                    },

                    goTo(index) {
                        this.active = index;
                        clearInterval(this.timer);
                        this.timer = setInterval(() => {
                            this.active = (this.active + 1) % this.total;
                        }, 5000);
                    }
                }
            }

            function updateUrl(key, value) {
                const url = new URL(window.location.href);
                url.searchParams.set(key, value);
                url.searchParams.delete('page');
                return url.toString();
            }
        </script>
    @endpush

</x-cinema-layout>
