<x-app-layout>
    <x-slot name="header">Tambah Film</x-slot>

    <div class="p-6 max-w-5xl" x-data="movieCreate()" x-init="init()">

        {{-- Back --}}
        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('admin.movies.index') }}"
                class="w-9 h-9 bg-white border border-gray-200 rounded-xl flex items-center
                justify-center text-gray-400 hover:text-gray-600 hover:border-gray-300
                transition-all duration-200 shadow-sm">
                <i class="ti ti-arrow-left text-base"></i>
            </a>
            <div>
                <h1 class="text-lg font-bold text-gray-900">Tambah Film Baru</h1>
                <p class="text-sm text-gray-500 mt-0.5">Import dari TMDb atau isi manual</p>
            </div>
        </div>

        {{-- Mode tabs --}}
        <div class="flex items-center bg-gray-100 rounded-2xl p-1 mb-6 w-fit">
            <button @click="mode = 'tmdb'"
                :class="mode === 'tmdb'
                    ?
                    'bg-white text-gray-900 shadow-sm' :
                    'text-gray-500 hover:text-gray-700'"
                class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm
                font-semibold transition-all duration-200">
                <i class="ti ti-database-import text-base"></i>
                Import dari TMDb
            </button>
            <button @click="mode = 'manual'"
                :class="mode === 'manual'
                    ?
                    'bg-white text-gray-900 shadow-sm' :
                    'text-gray-500 hover:text-gray-700'"
                class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm
                font-semibold transition-all duration-200">
                <i class="ti ti-edit text-base"></i>
                Input Manual
            </button>
        </div>

        {{-- TMDb Mode --}}
        <div x-show="mode === 'tmdb'" class="space-y-5">

            {{-- TMDb Search --}}
            <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
                <h3 class="text-sm font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <i class="ti ti-search text-gray-400"></i>
                    Cari Film di TMDb
                </h3>

                {{-- Search input --}}
                <div class="flex gap-2 mb-4">
                    <div
                        class="flex items-center gap-2 bg-gray-50 border border-gray-200
                    rounded-xl px-3 h-11 flex-1 focus-within:border-gray-400
                    focus-within:bg-white transition-all duration-200">
                        <i class="ti ti-search text-gray-400 text-sm flex-shrink-0"></i>
                        <input type="text" x-model="searchQuery" @input.debounce.500ms="searchTmdb()"
                            @keydown.enter.prevent="searchTmdb()"
                            placeholder="Ketik judul film... (cth. Inside Out, Avengers)"
                            class="bg-transparent border-none outline-none text-sm
                            text-gray-700 placeholder-gray-400 w-full">
                        <button x-show="searchQuery" @click="clearSearch()"
                            class="text-gray-400 hover:text-gray-600 transition">
                            <i class="ti ti-x text-xs"></i>
                        </button>
                    </div>
                    <button @click="searchTmdb()" :disabled="!searchQuery || searching"
                        class="h-11 px-5 bg-gray-900 hover:bg-gray-800 text-white text-sm
                        font-semibold rounded-xl transition-all duration-200
                        disabled:opacity-50 disabled:cursor-not-allowed
                        flex items-center gap-2">
                        <span x-show="!searching">Cari</span>
                        <span x-show="searching" class="flex items-center gap-2">
                            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            Mencari...
                        </span>
                    </button>
                </div>

                {{-- Browse tabs --}}
                <div x-show="!searchQuery" class="mb-4">
                    <div class="flex gap-2 mb-4">
                        @foreach (['now_playing' => 'Now Playing', 'upcoming' => 'Upcoming', 'popular' => 'Popular'] as $t => $label)
                            <button @click="browseTab = '{{ $t }}'; browseTmdb()"
                                :class="browseTab === '{{ $t }}'
                                    ?
                                    'bg-gray-900 text-white' :
                                    'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                                class="px-3 h-8 text-xs font-semibold rounded-xl
                            transition-all duration-150">
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Results grid --}}
                <div x-show="!searching && results.length > 0">
                    <p class="text-xs text-gray-500 mb-3"
                        x-text="searchQuery
                        ? totalResults + ' film ditemukan'
                        : 'Film ' + browseTab.replace('_', ' ')">
                    </p>
                    <div class="grid grid-cols-3 sm:grid-cols-4 lg:grid-cols-6 gap-3">
                        <template x-for="movie in results" :key="movie.tmdb_id">
                            <div class="group relative">
                                {{-- Card --}}
                                <div @click="selectMovie(movie)"
                                    class="cursor-pointer rounded-xl overflow-hidden border
                                    transition-all duration-200"
                                    :class="selectedTmdbId === movie.tmdb_id ?
                                        'border-gray-900 ring-2 ring-gray-900 ring-offset-1' :
                                        'border-gray-200 hover:border-gray-400'">

                                    <div class="aspect-[2/3] bg-gray-100 overflow-hidden">
                                        <img :src="movie.poster" :alt="movie.title" loading="lazy"
                                            class="w-full h-full object-cover
                                            group-hover:scale-105 transition-transform
                                            duration-300">
                                    </div>
                                    <div class="p-2 bg-white">
                                        <p class="text-xs font-semibold text-gray-900
                                        line-clamp-2 leading-snug"
                                            x-text="movie.title">
                                        </p>
                                        <p class="text-[10px] text-gray-400 mt-0.5"
                                            x-text="movie.release_date
                                            ? movie.release_date.substring(0,4) : '-'">
                                        </p>
                                    </div>
                                </div>

                                {{-- Already in DB badge --}}
                                <div x-show="movie.in_db" class="absolute top-1 right-1">
                                    <span
                                        class="text-[9px] font-bold px-1.5 py-0.5
                                    rounded-full bg-blue-500 text-white">
                                        Ada
                                    </span>
                                </div>

                                {{-- Selected check --}}
                                <div x-show="selectedTmdbId === movie.tmdb_id" class="absolute top-1 left-1">
                                    <div
                                        class="w-6 h-6 bg-gray-900 rounded-full flex
                                    items-center justify-center">
                                        <i class="ti ti-check text-white text-xs"></i>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- Browse pagination --}}
                    <div x-show="!searchQuery && browsePages > 1" class="flex justify-center gap-2 mt-4">
                        <button @click="browsePage > 1 && (browsePage--, browseTmdb())" :disabled="browsePage <= 1"
                            class="w-8 h-8 border border-gray-200 rounded-lg text-gray-500
                            hover:bg-gray-50 disabled:opacity-30 text-sm
                            transition flex items-center justify-center">
                            <i class="ti ti-chevron-left"></i>
                        </button>
                        <span class="text-xs text-gray-500 flex items-center px-2">
                            Hal <span class="font-bold mx-1" x-text="browsePage"></span>
                            dari <span class="ml-1" x-text="browsePages"></span>
                        </span>
                        <button @click="browsePage < browsePages && (browsePage++, browseTmdb())"
                            :disabled="browsePage >= browsePages"
                            class="w-8 h-8 border border-gray-200 rounded-lg text-gray-500
                            hover:bg-gray-50 disabled:opacity-30 text-sm
                            transition flex items-center justify-center">
                            <i class="ti ti-chevron-right"></i>
                        </button>
                    </div>
                </div>

                {{-- No results --}}
                <div x-show="!searching && results.length === 0 && searchQuery" class="text-center py-10">
                    <i class="ti ti-movie-off text-4xl text-gray-300 block mb-2"></i>
                    <p class="text-sm text-gray-400">
                        Tidak ada film ditemukan untuk "<span x-text="searchQuery"></span>"
                    </p>
                </div>

                {{-- Loading --}}
                <div x-show="searching" class="text-center py-10">
                    <svg class="animate-spin w-8 h-8 text-gray-400 mx-auto" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z">
                        </path>
                    </svg>
                </div>
            </div>

            {{-- Selected movie detail --}}
            <div x-show="selectedMovie" x-transition
                class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
                <h3 class="text-sm font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <i class="ti ti-film text-gray-400"></i>
                    Film Dipilih
                    <span x-show="loadingDetail" class="ml-2">
                        <svg class="animate-spin w-4 h-4 text-gray-400 inline" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                    </span>
                </h3>

                <div class="flex gap-5">
                    {{-- Poster --}}
                    <div class="w-24 flex-shrink-0">
                        <div class="aspect-[2/3] rounded-xl overflow-hidden border border-gray-200">
                            <img :src="selectedMovie?.poster" :alt="selectedMovie?.title"
                                class="w-full h-full object-cover">
                        </div>
                    </div>

                    {{-- Detail --}}
                    <div class="flex-1 min-w-0">
                        <h4 class="text-lg font-black text-gray-900 mb-0.5" x-text="selectedMovie?.title"></h4>
                        <p class="text-sm text-gray-500 italic mb-3" x-text="selectedMovie?.original_title"></p>

                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-4 text-xs">
                            <div class="bg-gray-50 rounded-xl p-2.5">
                                <p class="text-gray-400 mb-0.5">Sutradara</p>
                                <p class="font-semibold text-gray-800" x-text="selectedMovie?.director || '-'"></p>
                            </div>
                            <div class="bg-gray-50 rounded-xl p-2.5">
                                <p class="text-gray-400 mb-0.5">Durasi</p>
                                <p class="font-semibold text-gray-800"
                                    x-text="selectedMovie?.duration
                                    ? selectedMovie.duration + ' menit' : '-'">
                                </p>
                            </div>
                            <div class="bg-gray-50 rounded-xl p-2.5">
                                <p class="text-gray-400 mb-0.5">Rating TMDb</p>
                                <p class="font-semibold text-amber-600"
                                    x-text="selectedMovie?.vote_average
                                    ? '⭐ ' + selectedMovie.vote_average : '-'">
                                </p>
                            </div>
                            <div class="bg-gray-50 rounded-xl p-2.5">
                                <p class="text-gray-400 mb-0.5">Rilis</p>
                                <p class="font-semibold text-gray-800" x-text="selectedMovie?.release_date || '-'">
                                </p>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-1.5 mb-4">
                            <template x-for="genre in (selectedMovie?.genres || [])" :key="genre">
                                <span
                                    class="text-xs bg-gray-100 text-gray-600 px-2.5 py-1
                                rounded-full border border-gray-200"
                                    x-text="genre">
                                </span>
                            </template>
                        </div>

                        {{-- Import settings --}}
                        <div class="flex flex-wrap items-end gap-4 pt-4 border-t border-gray-100">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                                    Status di Bioskop <span class="text-red-500">*</span>
                                </label>
                                <select x-model="importStatus"
                                    class="bg-gray-50 border border-gray-200 text-gray-900
                                    text-sm rounded-xl px-3 h-10 outline-none
                                    focus:border-gray-400 transition-all duration-200">
                                    <option value="now_showing">Sedang Tayang</option>
                                    <option value="coming_soon">Segera Hadir</option>
                                    <option value="ended">Selesai</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                                    Rating Usia <span class="text-red-500">*</span>
                                </label>
                                <select x-model="importAgeRating"
                                    class="bg-gray-50 border border-gray-200 text-gray-900
                                    text-sm rounded-xl px-3 h-10 outline-none
                                    focus:border-gray-400 transition-all duration-200">
                                    <option value="SU">SU (Semua Umur)</option>
                                    <option value="13+">13+</option>
                                    <option value="17+">17+</option>
                                    <option value="21+">21+</option>
                                </select>
                            </div>
                            <button @click="importMovie()" :disabled="importing || !selectedTmdbId"
                                class="h-10 px-6 bg-gray-900 hover:bg-gray-800 text-white
                                text-sm font-semibold rounded-xl transition-all duration-200
                                disabled:opacity-50 flex items-center gap-2 shadow-sm">
                                <span x-show="!importing" class="flex items-center gap-2">
                                    <i class="ti ti-download text-base"></i>
                                    Import Film
                                </span>
                                <span x-show="importing" class="flex items-center gap-2">
                                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                    </svg>
                                    Mengimpor...
                                </span>
                            </button>
                        </div>

                        {{-- Import error --}}
                        <div x-show="importError" x-transition
                            class="mt-3 p-3 bg-red-50 border border-red-200 rounded-xl">
                            <p class="text-xs text-red-600 flex items-center gap-2">
                                <i class="ti ti-alert-circle text-sm"></i>
                                <span x-text="importError"></span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Manual Mode --}}
        <div x-show="mode === 'manual'">
            <form method="POST" action="{{ route('admin.movies.store') }}" enctype="multipart/form-data"
                class="space-y-5">
                @csrf
                @include('admin.movies._form')
                <div class="flex items-center gap-3 pt-2">
                    <button type="submit"
                        class="inline-flex items-center gap-2 h-11 px-6 bg-gray-900
                        hover:bg-gray-800 text-white text-sm font-semibold rounded-xl
                        transition-all duration-200 shadow-sm">
                        <i class="ti ti-check text-base"></i>Simpan Film
                    </button>
                    <a href="{{ route('admin.movies.index') }}"
                        class="h-11 px-5 border border-gray-200 text-gray-600 text-sm
                        font-medium rounded-xl hover:bg-gray-50 transition-all
                        duration-200 flex items-center">
                        Batal
                    </a>
                </div>
            </form>
        </div>

    </div>

    @push('scripts')
        <script>
            function movieCreate() {
                return {
                    mode: 'tmdb',
                    searchQuery: '',
                    searching: false,
                    results: [],
                    totalResults: 0,
                    selectedTmdbId: null,
                    selectedMovie: null,
                    loadingDetail: false,
                    importStatus: 'now_showing',
                    importAgeRating: 'SU',
                    importing: false,
                    importError: '',
                    browseTab: 'now_playing',
                    browsePage: 1,
                    browsePages: 1,

                    init() {
                        this.browseTmdb();
                    },

                    async searchTmdb() {
                        if (!this.searchQuery || this.searchQuery.length < 2) return;
                        this.searching = true;
                        this.selectedTmdbId = null;
                        this.selectedMovie = null;

                        try {
                            const res = await fetch(
                                `{{ route('admin.movies.tmdb.search') }}?q=${encodeURIComponent(this.searchQuery)}`, {
                                    headers: {
                                        'Accept': 'application/json'
                                    }
                                }
                            );
                            const data = await res.json();
                            this.results = data.results || [];
                            this.totalResults = data.total_results || 0;
                        } catch (e) {
                            console.error(e);
                        } finally {
                            this.searching = false;
                        }
                    },

                    async browseTmdb() {
                        this.searching = true;
                        try {
                            const res = await fetch(
                                `{{ route('admin.movies.tmdb.browse') }}?tab=${this.browseTab}&page=${this.browsePage}`, {
                                    headers: {
                                        'Accept': 'application/json'
                                    }
                                }
                            );
                            const data = await res.json();
                            this.results = data.results || [];
                            this.browsePages = data.total_pages || 1;
                        } catch (e) {
                            console.error(e);
                        } finally {
                            this.searching = false;
                        }
                    },

                    async selectMovie(movie) {
                        this.selectedTmdbId = movie.tmdb_id;
                        this.selectedMovie = movie;
                        this.loadingDetail = true;

                        try {
                            const res = await fetch(
                                `{{ url('admin/movies/tmdb') }}/${movie.tmdb_id}`, {
                                    headers: {
                                        'Accept': 'application/json'
                                    }
                                }
                            );
                            const data = await res.json();
                            if (!data.error) {
                                this.selectedMovie = data;
                            }
                        } catch (e) {
                            console.error(e);
                        } finally {
                            this.loadingDetail = false;
                        }
                    },

                    async importMovie() {
                        if (!this.selectedTmdbId) return;
                        this.importing = true;
                        this.importError = '';

                        try {
                            const res = await fetch(
                                '{{ route('admin.movies.tmdb.import') }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector(
                                            'meta[name="csrf-token"]'
                                        ).content,
                                        'Accept': 'application/json',
                                    },
                                    body: JSON.stringify({
                                        tmdb_id: this.selectedTmdbId,
                                        status: this.importStatus,
                                        age_rating: this.importAgeRating,
                                    }),
                                }
                            );

                            const data = await res.json();

                            if (data.success) {
                                window.location.href = data.redirect;
                            } else {
                                this.importError = data.message || 'Import gagal.';
                            }
                        } catch (e) {
                            this.importError = 'Terjadi kesalahan server.';
                        } finally {
                            this.importing = false;
                        }
                    },

                    clearSearch() {
                        this.searchQuery = '';
                        this.results = [];
                        this.selectedTmdbId = null;
                        this.selectedMovie = null;
                        this.browseTmdb();
                    }
                }
            }
        </script>
    @endpush

</x-app-layout>
